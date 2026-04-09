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

class SettingsPage extends Component
{
    public ?int $editingUserId = null;
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

    public string $drillTypeName = '';
    public string $drillTypeDescription = '';
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
            'rigName' => ['required', 'string', 'max:255', 'unique:rigs,name'],
            'rigCode' => ['required', 'string', 'max:50', 'unique:rigs,code'],
            'rigLocation' => ['nullable', 'string', 'max:255'],
        ]);

        Rig::query()->create([
            'name' => $validated['rigName'],
            'code' => strtoupper($validated['rigCode']),
            'location' => $validated['rigLocation'],
            'is_active' => true,
        ]);

        $this->reset('rigName', 'rigCode', 'rigLocation');
    }

    public function saveDrillType(): void
    {
        $validated = $this->validate([
            'drillTypeName' => ['required', 'string', 'max:255', 'unique:drill_types,name'],
            'drillTypeDescription' => ['nullable', 'string'],
        ]);

        DrillType::query()->create([
            'name' => $validated['drillTypeName'],
            'description' => $validated['drillTypeDescription'],
            'is_active' => true,
        ]);

        $this->reset('drillTypeName', 'drillTypeDescription');
    }

    public function saveEventType(): void
    {
        $validated = $this->validate([
            'eventTypeName' => ['required', 'string', 'max:255', 'unique:event_types,name'],
            'eventTypeDescription' => ['nullable', 'string'],
        ]);

        EventType::query()->create([
            'name' => $validated['eventTypeName'],
            'description' => $validated['eventTypeDescription'],
            'is_active' => true,
        ]);

        $this->reset('eventTypeName', 'eventTypeDescription');
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

    public function render()
    {
        return view('livewire.admin.settings-page', [
            'users' => User::query()->with('rig')->orderBy('role')->orderBy('full_name')->get(),
            'rigs' => Rig::query()->orderBy('name')->get(),
            'drillTypes' => DrillType::query()->orderBy('name')->get(),
            'eventTypes' => EventType::query()->orderBy('name')->get(),
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
}
