<?php

namespace App\Livewire\Admin;

use App\Models\AccountAppAccess;
use App\Models\ActionStatus;
use App\Models\DrillStatus;
use App\Models\DrillType;
use App\Models\EventType;
use App\Models\Rig;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SettingsPage extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $userSearch = '';

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
    public bool $userHasAppAccess = true;
    public string $userCurrentAssigneeName = '';
    public string $userAssigneeEffectiveFrom = '';
    public string $userAssigneeEffectiveTo = '';
    public string $userAssigneeRemarks = '';
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

    public function mount(): void
    {
        $this->userAssigneeEffectiveFrom = now()->toDateString();
    }

    public function saveUser(): void
    {
        $requiresRigAssignment = ! in_array($this->userRole, ['Management', 'Administrator'], true);

        $rules = [
            'userFullName' => ['required', 'string', 'max:255'],
            'userEmail' => ['required', 'email', 'max:255', Rule::unique('auth.accounts', 'email')->ignore($this->editingUserId)],
            'userRole' => ['required', Rule::in(array_values(config('er_drill.roles')))],
            'userRigId' => [$requiresRigAssignment ? 'required' : 'nullable', 'exists:rigs,id'],
            'userDescription' => ['nullable', 'string'],
            'userCurrentAssigneeName' => [$requiresRigAssignment ? 'required' : 'nullable', 'string', 'max:255'],
            'userAssigneeEffectiveFrom' => [$requiresRigAssignment ? 'required' : 'nullable', 'date'],
            'userAssigneeEffectiveTo' => ['nullable', 'date', 'after_or_equal:userAssigneeEffectiveFrom'],
            'userAssigneeRemarks' => ['nullable', 'string'],
            'userPassword' => [$this->editingUserId ? 'nullable' : 'required', 'string', 'min:8'],
        ];

        $validated = $this->validate($rules);

        $user = User::query()->find($this->editingUserId);
        $previous = $user?->only(['full_name', 'role_code', 'rig_code']);

        $payload = [
            'full_name' => $validated['userFullName'],
            'email' => strtolower($validated['userEmail']),
            'role' => $validated['userRole'],
            'account_type' => $requiresRigAssignment ? 'shared_role' : 'admin',
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
        } else {
            $user->update($payload);
        }

        if (! $previous || $previous['full_name'] !== $user->full_name || $previous['role_code'] !== $user->role_code || $previous['rig_code'] !== $user->rig_code) {
            $this->syncCurrentAssignee($user);
        }

        if ($this->userCurrentAssigneeName !== '' || $this->userAssigneeEffectiveFrom !== '' || $this->userAssigneeRemarks !== '') {
            $this->syncCurrentAssignee($user);
        }

        $this->syncAppAccess($user);

        $this->resetUserForm();
        $this->resetPage('usersPage');
        session()->flash('status', 'User saved successfully.');
    }

    public function editUser(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        $currentAssignee = $user->currentAssignee();

        $this->editingUserId = $user->id;
        $this->userFullName = $user->full_name;
        $this->userEmail = $user->email;
        $this->userRole = $user->role;
        $this->userRigId = $user->rig_id;
        $this->userDescription = $user->description ?? '';
        $this->userActiveStatus = $user->active_status;
        $this->userHasAppAccess = $user->hasAppAccess(config('er_drill.auth_app_code'));
        $this->userCurrentAssigneeName = $currentAssignee?->person_name ?? '';
        $this->userAssigneeEffectiveFrom = optional($currentAssignee?->effective_from)->format('Y-m-d') ?? now()->toDateString();
        $this->userAssigneeEffectiveTo = optional($currentAssignee?->effective_to)->format('Y-m-d') ?? '';
        $this->userAssigneeRemarks = $currentAssignee?->remarks ?? '';
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

    public function updatedUserSearch(): void
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
            ->orderBy('role_code')
            ->orderBy('full_name');

        $search = trim($this->userSearch);
        if ($search !== '') {
            $like = '%'.$search.'%';
            $userQuery->where(function ($query) use ($like) {
                $query->where('full_name', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

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
        $this->reset(
            'editingUserId',
            'userFullName',
            'userEmail',
            'userRigId',
            'userDescription',
            'userPassword',
            'userCurrentAssigneeName',
            'userAssigneeEffectiveFrom',
            'userAssigneeEffectiveTo',
            'userAssigneeRemarks'
        );
        $this->userRole = 'STO';
        $this->userActiveStatus = true;
        $this->userHasAppAccess = true;
        $this->userAssigneeEffectiveFrom = now()->toDateString();
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

    private function syncCurrentAssignee(User $user): void
    {
        if (in_array($user->role, ['Management', 'Administrator'], true) || ! $user->rig_code) {
            return;
        }

        $user->syncAssigneeSchedule(
            $this->userCurrentAssigneeName,
            $this->userAssigneeEffectiveFrom ?: now()->toDateString(),
            $this->userAssigneeEffectiveTo ?: null,
            $this->userAssigneeRemarks ?: null,
        );
    }

    private function syncAppAccess(User $user): void
    {
        AccountAppAccess::query()->updateOrCreate(
            [
                'account_id' => $user->id,
                'app_code' => config('er_drill.auth_app_code'),
            ],
            ['is_active' => $this->userHasAppAccess]
        );
    }

    private function resolvePerPage(int $total, string $setting): int
    {
        if ($setting === 'all') {
            return max($total, 1);
        }

        return (int) $setting;
    }
}
