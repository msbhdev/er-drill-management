<?php

namespace App\Models;

use App\Enums\UserRole;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $connection = 'auth';

    protected $table = 'accounts';

    protected $fillable = [
        'full_name',
        'email',
        'account_type',
        'role',
        'role_code',
        'rig_id',
        'rig_code',
        'active_status',
        'description',
        'password',
        'last_login_at',
        'failed_login_attempts',
        'locked_until',
        'must_change_password',
        'name_confirmed_at',
        'remember_token',
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
        return $this->belongsTo(Rig::class, 'rig_code', 'code');
    }

    public function roleHistory(): HasMany
    {
        return $this->hasMany(RoleAssigneeSchedule::class, 'account_id');
    }

    public function appAccesses(): HasMany
    {
        return $this->hasMany(AccountAppAccess::class, 'account_id');
    }

    public function createdDrills(): HasMany
    {
        return $this->hasMany(DrillRecord::class, 'created_by_user_id');
    }

    public function scopeWithAppAccess(Builder $query, ?string $appCode = null): Builder
    {
        $resolvedAppCode = $appCode ?? config('er_drill.auth_app_code');

        return $query->whereHas('appAccesses', function (Builder $builder) use ($resolvedAppCode) {
            $builder
                ->where('app_code', $resolvedAppCode)
                ->where('is_active', true);
        });
    }

    public function isRole(UserRole $role): bool
    {
        return $this->role_code === $role->value;
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

    public function hasAppAccess(string $appCode): bool
    {
        if ($this->relationLoaded('appAccesses')) {
            return $this->appAccesses
                ->contains(fn (AccountAppAccess $access) => $access->app_code === $appCode && $access->is_active);
        }

        return $this->appAccesses()
            ->where('app_code', $appCode)
            ->where('is_active', true)
            ->exists();
    }

    public function currentAssignee(?CarbonInterface $date = null): ?RoleAssigneeSchedule
    {
        $resolvedDate = $date ?? now();

        return $this->roleHistory()
            ->activeOn($resolvedDate)
            ->latest('effective_from')
            ->first();
    }

    public function currentAssigneeName(?CarbonInterface $date = null): string
    {
        return $this->currentAssignee($date)?->person_name ?? $this->full_name;
    }

    public function syncAssigneeSchedule(
        string $personName,
        string $effectiveFrom,
        ?string $effectiveTo = null,
        ?string $remarks = null
    ): RoleAssigneeSchedule {
        $start = Carbon::parse($effectiveFrom)->toDateString();
        $end = blank($effectiveTo) ? null : Carbon::parse($effectiveTo)->toDateString();

        $existing = $this->roleHistory()
            ->whereDate('effective_from', $start)
            ->first();

        if ($existing) {
            $existing->forceFill([
                'person_name' => trim($personName),
                'role_code' => $this->role,
                'rig_code' => $this->rig_code,
                'effective_to' => $end,
                'remarks' => $remarks,
                'active_status' => true,
            ])->save();

            return $existing;
        }

        $openSchedule = $this->roleHistory()
            ->where('active_status', true)
            ->whereDate('effective_from', '<', $start)
            ->where(function (Builder $builder) use ($start) {
                $builder
                    ->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $start);
            })
            ->latest('effective_from')
            ->first();

        if ($openSchedule) {
            $openSchedule->forceFill([
                'effective_to' => Carbon::parse($start)->subDay()->toDateString(),
            ])->save();
        }

        return $this->roleHistory()->create([
            'person_name' => trim($personName),
            'role_code' => $this->role,
            'rig_code' => $this->rig_code,
            'effective_from' => $start,
            'effective_to' => $end,
            'remarks' => $remarks,
            'active_status' => true,
        ]);
    }

    public function formatDateTime(?CarbonInterface $value, string $format = 'd M Y H:i'): ?string
    {
        return $value?->copy()->timezone($this->preferredTimezone())->format($format);
    }

    public function getRoleAttribute(): ?string
    {
        return $this->role_code;
    }

    public function setRoleAttribute(?string $value): void
    {
        $this->attributes['role_code'] = $value;
    }

    public function getRigIdAttribute(): ?int
    {
        if (! $this->rig_code) {
            return null;
        }

        if ($this->relationLoaded('rig')) {
            return $this->rig?->id;
        }

        return Rig::query()
            ->where('code', $this->rig_code)
            ->value('id');
    }

    public function setRigIdAttribute(mixed $value): void
    {
        if (blank($value)) {
            $this->attributes['rig_code'] = null;

            return;
        }

        $this->attributes['rig_code'] = $value instanceof Rig
            ? $value->code
            : Rig::query()->whereKey($value)->value('code');
    }
}
