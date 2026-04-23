<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleAssigneeSchedule extends Model
{
    protected $connection = 'auth';

    protected $table = 'role_assignee_schedules';

    protected $fillable = [
        'account_id',
        'person_name',
        'role_code',
        'rig_code',
        'effective_from',
        'effective_to',
        'remarks',
        'active_status',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'active_status' => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_id');
    }

    public function scopeActiveOn(Builder $query, CarbonInterface|string $date): Builder
    {
        $resolvedDate = $date instanceof CarbonInterface
            ? $date->toDateString()
            : $date;

        return $query
            ->where('active_status', true)
            ->whereDate('effective_from', '<=', $resolvedDate)
            ->where(function (Builder $builder) use ($resolvedDate) {
                $builder
                    ->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $resolvedDate);
            });
    }
}
