<?php

namespace App\Services;

use App\Models\DrillRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DrillReportService
{
    public function queryForUser(User $user, array $filters = []): Builder
    {
        return DrillRecord::query()
            ->with(['rig', 'drillType', 'eventType', 'status'])
            ->visibleTo($user)
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('reference_no', 'like', "%{$search}%")
                        ->orWhere('event_location', 'like', "%{$search}%")
                        ->orWhere('scenario', 'like', "%{$search}%");
                });
            })
            ->when($filters['rig_id'] ?? null, fn (Builder $query, $rigId) => $query->where('rig_id', $rigId))
            ->when($filters['drill_type_id'] ?? null, fn (Builder $query, $drillTypeId) => $query->where('drill_type_id', $drillTypeId))
            ->when($filters['event_type_id'] ?? null, fn (Builder $query, $eventTypeId) => $query->where('event_type_id', $eventTypeId))
            ->when($filters['status_id'] ?? null, fn (Builder $query, $statusId) => $query->where('status_id', $statusId))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $dateFrom) => $query->whereDate('drill_date', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $dateTo) => $query->whereDate('drill_date', '<=', $dateTo))
            ->orderByDesc('drill_date')
            ->orderByDesc('created_at');
    }
}
