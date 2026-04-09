<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DrillRecord;
use App\Models\User;

class DrillRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user;
    }

    public function view(User $user, DrillRecord $drillRecord): bool
    {
        return $user->canAccessRig($drillRecord->rig_id);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::STO->value, UserRole::Administrator->value], true);
    }

    public function update(User $user, DrillRecord $drillRecord): bool
    {
        return $drillRecord->isEditableBy($user);
    }

    public function submit(User $user, DrillRecord $drillRecord): bool
    {
        return $this->update($user, $drillRecord);
    }

    public function verify(User $user, DrillRecord $drillRecord): bool
    {
        return $user->canAccessRig($drillRecord->rig_id)
            && in_array($user->role, [UserRole::BE->value, UserRole::Administrator->value], true)
            && $drillRecord->status?->code === 'submitted';
    }

    public function approve(User $user, DrillRecord $drillRecord): bool
    {
        return $user->canAccessRig($drillRecord->rig_id)
            && in_array($user->role, [UserRole::OIM->value, UserRole::Administrator->value], true)
            && $drillRecord->status?->code === 'verified';
    }

    public function close(User $user, DrillRecord $drillRecord): bool
    {
        return $user->canAccessRig($drillRecord->rig_id)
            && in_array($user->role, [UserRole::OIM->value, UserRole::Administrator->value], true)
            && $drillRecord->status?->code === 'approved';
    }

    public function export(User $user): bool
    {
        return in_array($user->role, [
            UserRole::RM->value,
            UserRole::Management->value,
            UserRole::Administrator->value,
        ], true);
    }
}
