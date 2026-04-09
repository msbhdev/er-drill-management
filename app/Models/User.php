<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'role',
        'rig_id',
        'active_status',
        'description',
        'password',
        'last_login_at',
        'failed_login_attempts',
        'locked_until',
        'must_change_password',
        'name_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'active_status' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'name_confirmed_at' => 'datetime',
        ];
    }

    public function rig(): BelongsTo
    {
        return $this->belongsTo(Rig::class);
    }

    public function roleHistory(): HasMany
    {
        return $this->hasMany(RoleHistory::class);
    }

    public function createdDrills(): HasMany
    {
        return $this->hasMany(DrillRecord::class, 'created_by_user_id');
    }

    public function isRole(UserRole $role): bool
    {
        return $this->role === $role->value;
    }

    public function isAdministrator(): bool
    {
        return $this->isRole(UserRole::Administrator);
    }

    public function isManagement(): bool
    {
        return $this->isRole(UserRole::Management);
    }

    public function canAccessRig(?int $rigId): bool
    {
        if ($this->isAdministrator() || $this->isManagement()) {
            return true;
        }

        return $rigId !== null && $this->rig_id === $rigId;
    }
}
