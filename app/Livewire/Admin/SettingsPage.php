<?php

namespace App\Livewire\Admin;

use App\Models\ActionStatus;
use App\Models\DrillStatus;
use App\Models\DrillType;
use App\Models\EventType;
use App\Models\Rig;
use App\Models\RoleHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SettingsPage extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $userRecordsPerPage = '5';
    public string $rigRecordsPerPage = '5';
    public string $drillTypeRecordsPerPage = '5';
    public string $eventTypeRecordsPerPage = '5';

    public ?int $editingUserId = null;
    public ?int $editingRigId = null;
    public string $userFullName = '';
    public string $userEmail = '';
    public string $userRole = 'STO';
    public ?int $userRigId = null;
    public string $userDescription = '';
    public bool $userActiveStatus = true;
    public string $userPassword = '';

    public string $rigName = '';
    public string $rigCode = '';
    public string $rigLocation = '';
    public string $rigTimezone = 'Asia/Kuala_Lumpur';

    public ?int $editingDrillTypeId = null;
    public string $drillTypeName = '';
    public string $drillTypeDescription = '';
    public ?int $editingEventTypeId = null;
    public string $eventTypeName = '';
    public string $eventTypeDescription = '';

    public string $drillStatusName = '';
    public string $drillStatusCode = '';
    public string $actionStatusName = '';
    public string $actionStatusCode = '';

    public function saveUser(): void
    {
        $rules = [
            'userFullName' => ['required', 'string', 'max:255'],
            'userEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'userRole' => ['required', Rule::in(array_values(config('er_drill.roles')))],
            'userRigId' => ['nullable', 'exists:rigs,id'],
            'userDescription' => ['nullable', 'string'],
            'userPassword' => [$this->editingUserId ? 'nullable' : 'required', 'string', 'min:8'],
        ];

        $validated = $this->validate($rules);

        $user = User::query()->find($this->editingUserId);
        $previous = $user?->only(['full_name', 'role', 'rig_id']);

        $payload = [
            'full_name' => $validated['userFullName'],
            'email' => strtolower($validated['userEmail']),
            'role' => $validated['userRole'],
            'rig_id' => in_array($validated['userRole'], ['Management', 'Administrator'], true) ? null : $validated['userRigId'],
            'description' => $validated['userDescription'],
            'active_status' => $this->userActiveStatus,
        ];

        if ($validated['userPassword'] !== '') {
            $payload['password'] = Hash::make($validated['userPassword']);
            $payload['must_change_password'] = true;
        }

        if (! $user) {
            $user = User::query()->create($payload);
            $this->writeRoleHistory($user);
        } else {
            $user->update($payload);

            if (! $previous || $previous['full_name'] !== $user->full_name || $previous['role'] !== $user->role || (int) $previous['rig_id'] !== (int) $user->rig_id) {
                RoleHistory::query()
                    ->where('user_id', $user->id)
                    ->whereNull('effective_to')
                    ->update(['effective_to' => now()->toDateString()]);

                $this->writeRoleHistory($user);
            }
        }

        $this->resetUserForm();
        $this->resetPage('usersPage');
        session()->flash('status', 'User saved successfully.');
    }

    public function editUser(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->userFullName = $user->full_name;
        $this->userEmail = $user->email;
        $this->userRole = $user->role;
        $this->userRigId = $user->rig_id;
        $this->userDescription = $user->description ?? '';
        $this->userActiveStatus = $user->active_status;
        $this->userPassword = '';
    }

    public function toggleUserActive(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        $user->update(['active_status' => ! $user->active_status]);
    }

    public function saveRig(): void
    {
        $validated = $this->validate([
            'rigName' => ['required', 'string', 'max:255', Rule::unique('rigs', 'name')->ignore($this->editingRigId)],
            'rigCode' => ['required', 'string', 'max:50', Rule::unique('rigs', 'code')->ignore($this->editingRigId)],
            'rigLocation' => ['nullable', 'string', 'max:255'],
            'rigTimezone' => ['required', Rule::in(array_keys(config('er_drill.timezones')))],
        ]);

        $rig = Rig::query()->find($this->editingRigId);
        $payload = [
            'name' => $validated['rigName'],
            'code' => strtoupper($validated['rigCode']),
            'location' => $validated['rigLocation'],
            'timezone' => $validated['rigTimezone'],
        ];

        if ($rig) {
            $rig->update($payload);
        } else {
            Rig::query()->create([
                ...$payload,
                'is_active' => true,
            ]);
        }

        $this->resetRigForm();
        $this->resetPage('rigsPage');
    }

    public function editRig(int $rigId): void
    {
        $rig = Rig::query()->findOrFail($rigId);

        $this->editingRigId = $rig->id;
        $this->rigName = $rig->name;
        $this->rigCode = $rig->code;
        $this->rigLocation = $rig->location ?? '';
        $this->rigTimezone = $rig->timezoneName();
    }

    public function cancelRigEdit(): void
    {
        $this->resetRigForm();
    }

    public function saveDrillType(): void
    {
        $validated = $this->validate([
            'drillTypeName' => ['required', 'string', 'max:255', Rule::unique('drill_types', 'name')->ignore($this->editingDrillTypeId)],
            'drillTypeDescription' => ['nullable', 'string'],
        ]);

        if ($this->editingDrillTypeId) {
            DrillType::query()->findOrFail($this->editingDrillTypeId)->update([
                'name' => $validated['drillTypeName'],
                'description' => $validated['drillTypeDescription'],
            ]);
        } else {
            DrillType::query()->create([
                'name' => $validated['drillTypeName'],
                'description' => $validated['drillTypeDescription'],
                'is_active' => true,
            ]);
        }

        $this->resetDrillTypeForm();
        $this->resetPage('drillTypesPage');
        session()->flash('status', 'Drill type saved successfully.');
    }

    public function editDrillType(int $drillTypeId): void
    {
        $drillType = DrillType::query()->findOrFail($drillTypeId);

        $this->editingDrillTypeId = $drillType->id;
        $this->drillTypeName = $drillType->name;
        $this->drillTypeDescription = $drillType->description ?? '';
    }

    public function cancelDrillTypeEdit(): void
    {
        $this->resetDrillTypeForm();
    }

    public function deleteDrillType(int $drillTypeId): void
    {
        $drillType = DrillType::query()->findOrFail($drillTypeId);

        if ($drillType->primaryDrillRecords()->exists() || $drillType->drillRecords()->exists()) {
            session()->flash('status', 'This drill type is already used by drill records and cannot be deleted.');

            return;
        }

        $drillType->delete();

        if ($this->editingDrillTypeId === $drillTypeId) {
            $this->resetDrillTypeForm();
        }

        $this->resetPage('drillTypesPage');
        session()->flash('status', 'Drill type deleted successfully.');
    }

    public function saveEventType(): void
    {
        $validated = $this->validate([
            'eventTypeName' => ['required', 'string', 'max:255', Rule::unique('event_types', 'name')->ignore($this->editingEventTypeId)],
            'eventTypeDescription' => ['nullable', 'string'],
        ]);

        if ($this->editingEventTypeId) {
            EventType::query()->findOrFail($this->editingEventTypeId)->update([
                'name' => $validated['eventTypeName'],
                'description' => $validated['eventTypeDescription'],
            ]);
        } else {
            EventType::query()->create([
                'name' => $validated['eventTypeName'],
                'description' => $validated['eventTypeDescription'],
                'is_active' => true,
            ]);
        }

        $this->resetEventTypeForm();
        $this->resetPage('eventTypesPage');
        session()->flash('status', 'Event type saved successfully.');
    }

    public function editEventType(int $eventTypeId): void
    {
        $eventType = EventType::query()->findOrFail($eventTypeId);

        $this->editingEventTypeId = $eventType->id;
        $this->eventTypeName = $eventType->name;
        $this->eventTypeDescription = $eventType->description ?? '';
    }

    public function cancelEventTypeEdit(): void
    {
        $this->resetEventTypeForm();
    }

    public function deleteEventType(int $eventTypeId): void
    {
        $eventType = EventType::query()->findOrFail($eventTypeId);

        if ($eventType->primaryDrillRecords()->exists() || $eventType->drillRecords()->exists()) {
            session()->flash('status', 'This event type is already used by drill records and cannot be deleted.');

            return;
        }

        $eventType->delete();

        if ($this->editingEventTypeId === $eventTypeId) {
            $this->resetEventTypeForm();
        }

        $this->resetPage('eventTypesPage');
        session()->flash('status', 'Event type deleted successfully.');
    }

    public function saveDrillStatus(): void
    {
        $validated = $this->validate([
            'drillStatusName' => ['required', 'string', 'max:255'],
            'drillStatusCode' => ['required', 'string', 'max:50', 'unique:drill_statuses,code'],
        ]);

        DrillStatus::query()->create([
            'name' => $validated['drillStatusName'],
            'code' => strtolower($validated['drillStatusCode']),
            'sort_order' => DrillStatus::max('sort_order') + 10,
            'is_active' => true,
        ]);

        $this->reset('drillStatusName', 'drillStatusCode');
    }

    public function saveActionStatus(): void
    {
        $validated = $this->validate([
            'actionStatusName' => ['required', 'string', 'max:255'],
            'actionStatusCode' => ['required', 'string', 'max:50', 'unique:action_statuses,code'],
        ]);

        ActionStatus::query()->create([
            'name' => $validated['actionStatusName'],
            'code' => strtolower($validated['actionStatusCode']),
            'sort_order' => ActionStatus::max('sort_order') + 10,
            'is_active' => true,
        ]);

        $this->reset('actionStatusName', 'actionStatusCode');
    }

    public function toggleRig(int $rigId): void
    {
        $rig = Rig::query()->findOrFail($rigId);
        $rig->update(['is_active' => ! $rig->is_active]);
    }

    public function toggleDrillType(int $drillTypeId): void
    {
        $drillType = DrillType::query()->findOrFail($drillTypeId);
        $drillType->update(['is_active' => ! $drillType->is_active]);
    }

    public function toggleEventType(int $eventTypeId): void
    {
        $eventType = EventType::query()->findOrFail($eventTypeId);
        $eventType->update(['is_active' => ! $eventType->is_active]);
    }

    public function toggleDrillStatus(int $drillStatusId): void
    {
        $status = DrillStatus::query()->findOrFail($drillStatusId);
        $status->update(['is_active' => ! $status->is_active]);
    }

    public function toggleActionStatus(int $actionStatusId): void
    {
        $status = ActionStatus::query()->findOrFail($actionStatusId);
        $status->update(['is_active' => ! $status->is_active]);
    }

    public function updatedUserRecordsPerPage(): void
    {
        $this->resetPage('usersPage');
    }

    public function updatedRigRecordsPerPage(): void
    {
        $this->resetPage('rigsPage');
    }

    public function updatedDrillTypeRecordsPerPage(): void
    {
        $this->resetPage('drillTypesPage');
    }

    public function updatedEventTypeRecordsPerPage(): void
    {
        $this->resetPage('eventTypesPage');
    }

    public function render()
    {
        $userQuery = User::query()
            ->with('rig')
            ->orderBy('role')
            ->orderBy('full_name');

        $rigQuery = Rig::query()->orderBy('name');
        $drillTypeQuery = DrillType::query()->orderBy('name');
        $eventTypeQuery = EventType::query()->orderBy('name');

        return view('livewire.admin.settings-page', [
            'users' => $userQuery->paginate(
                $this->resolvePerPage($userQuery->toBase()->getCountForPagination(), $this->userRecordsPerPage),
                pageName: 'usersPage'
            ),
            'rigs' => $rigQuery->paginate(
                $this->resolvePerPage($rigQuery->toBase()->getCountForPagination(), $this->rigRecordsPerPage),
                pageName: 'rigsPage'
            ),
            'drillTypes' => $drillTypeQuery->paginate(
                $this->resolvePerPage($drillTypeQuery->toBase()->getCountForPagination(), $this->drillTypeRecordsPerPage),
                pageName: 'drillTypesPage'
            ),
            'eventTypes' => $eventTypeQuery->paginate(
                $this->resolvePerPage($eventTypeQuery->toBase()->getCountForPagination(), $this->eventTypeRecordsPerPage),
                pageName: 'eventTypesPage'
            ),
            'drillStatuses' => DrillStatus::query()->orderBy('sort_order')->get(),
            'actionStatuses' => ActionStatus::query()->orderBy('sort_order')->get(),
        ])->layout('layouts.app');
    }

    private function resetUserForm(): void
    {
        $this->reset('editingUserId', 'userFullName', 'userEmail', 'userRigId', 'userDescription', 'userPassword');
        $this->userRole = 'STO';
        $this->userActiveStatus = true;
    }

    private function resetRigForm(): void
    {
        $this->reset('editingRigId', 'rigName', 'rigCode', 'rigLocation');
        $this->rigTimezone = 'Asia/Kuala_Lumpur';
    }

    private function resetDrillTypeForm(): void
    {
        $this->reset('editingDrillTypeId', 'drillTypeName', 'drillTypeDescription');
    }

    private function resetEventTypeForm(): void
    {
        $this->reset('editingEventTypeId', 'eventTypeName', 'eventTypeDescription');
    }

    private function writeRoleHistory(User $user): void
    {
        RoleHistory::query()->create([
            'user_id' => $user->id,
            'rig_id' => $user->rig_id,
            'person_name' => $user->full_name,
            'role' => $user->role,
            'effective_from' => now()->toDateString(),
        ]);
    }

    private function resolvePerPage(int $total, string $setting): int
    {
        if ($setting === 'all') {
            return max($total, 1);
        }

        return (int) $setting;
    }
}
