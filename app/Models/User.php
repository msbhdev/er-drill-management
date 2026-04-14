<?php

namespace App\Models;

use App\Enums\UserRole;
use Carbon\CarbonInterface;
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

    public function needsNameConfirmation(int $days = 14): bool
    {
        if (blank($this->full_name) || ! $this->name_confirmed_at) {
            return true;
        }

        return $this->name_confirmed_at->lte(now()->subDays($days));
    }

    public function confirmFullName(string $fullName): void
    {
        $this->forceFill([
            'full_name' => trim($fullName),
            'name_confirmed_at' => now(),
        ])->save();
    }

    public function preferredTimezone(): string
    {
        return $this->rig?->timezoneName() ?? config('er_drill.default_timezone', 'Asia/Kuala_Lumpur');
    }

    public function formatDateTime(?CarbonInterface $value, string $format = 'd M Y H:i'): ?string
    {
        return $value?->copy()->timezone($this->preferredTimezone())->format($format);
    }
}
