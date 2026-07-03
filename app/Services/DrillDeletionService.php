<?php

namespace App\Services;

use App\Models\ArchivedDrillRecord;
use App\Models\DrillAction;
use App\Models\DrillRecord;
use App\Models\DrillWorkflowHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DrillDeletionService
{
    /**
     * Permanently delete a drill record, retaining a full audit archive.
     *
     * A complete JSON snapshot of the drill and everything attached to it is
     * written to `archived_drill_records` first, so the record survives for
     * audit even though the live rows are removed. Child rows (workflow
     * history, events, actions, attachment rows and the drill-type / event-type
     * / DSHA pivots) are then removed by the database via `cascadeOnDelete`.
     * In-app notifications reference the drill by id only (no foreign key), so
     * they are cleared here too.
     *
     * Uploaded attachment files are intentionally left on disk — the archive
     * snapshot records their paths, and keeping them preserves the evidence for
     * an audit. The download route no longer serves them once the drill is gone.
     */
    public function delete(DrillRecord $drillRecord, User $actor): void
    {
        $drillRecord->load([
            'rig',
            'status',
            'drillTypes',
            'eventTypes',
            'dshas',
            'events',
            'actions.status',
            'attachments',
            'workflowHistory.actor',
            'workflowHistory.fromStatus',
            'workflowHistory.toStatus',
            'creator',
            'stoUser',
            'beUser',
            'oimUser',
        ]);

        $drillId = $drillRecord->id;
        $reference = $drillRecord->reference_no;
        $snapshot = $this->buildSnapshot($drillRecord);

        DB::transaction(function () use ($drillRecord, $drillId, $reference, $actor, $snapshot): void {
            ArchivedDrillRecord::create([
                'original_drill_id' => $drillId,
                'reference_no' => $reference,
                'rig_name' => $drillRecord->rig?->name,
                'status_name' => $drillRecord->status?->name,
                'drill_date' => $drillRecord->drill_date,
                'snapshot' => $snapshot,
                'deleted_by_user_id' => $actor->id,
                'deleted_by_name' => $actor->full_name ?? $actor->email,
                'archived_at' => now(),
            ]);

            // In-app notifications point at the drill via data->drill_id only.
            DB::table('notifications')->where('data->drill_id', $drillId)->delete();

            // Cascade removes history, events, actions, attachment rows and pivots.
            $drillRecord->delete();
        });

        Log::warning('Drill record permanently deleted by administrator (archived for audit).', [
            'drill_id' => $drillId,
            'reference_no' => $reference,
            'actor_user_id' => $actor->id,
            'actor' => $actor->full_name ?? $actor->email,
        ]);
    }

    /**
     * Build a complete, self-contained snapshot of the drill for the archive.
     */
    private function buildSnapshot(DrillRecord $drill): array
    {
        return [
            'drill' => $drill->attributesToArray(),
            'rig' => $drill->rig?->only(['id', 'name', 'code']),
            'status' => $drill->status?->only(['id', 'code', 'name']),
            'drill_types' => $drill->drillTypes->map->only(['id', 'name'])->values()->all(),
            'event_types' => $drill->eventTypes->map->only(['id', 'name'])->values()->all(),
            'dshas' => $drill->dshas->map->only(['id', 'code', 'name'])->values()->all(),
            'events' => $drill->events->map->attributesToArray()->values()->all(),
            'actions' => $drill->actions->map(fn (DrillAction $action) => $action->attributesToArray() + [
                'status' => $action->status?->only(['code', 'name']),
            ])->values()->all(),
            'attachments' => $drill->attachments->map->attributesToArray()->values()->all(),
            'workflow_history' => $drill->workflowHistory->map(fn (DrillWorkflowHistory $history) => $history->attributesToArray() + [
                'actor' => $history->actor?->only(['id', 'full_name', 'email']),
                'from_status' => $history->fromStatus?->only(['code', 'name']),
                'to_status' => $history->toStatus?->only(['code', 'name']),
            ])->values()->all(),
            'people' => [
                'creator' => $drill->creator?->only(['id', 'full_name', 'email']),
                'sto' => $drill->stoUser?->only(['id', 'full_name', 'email']),
                'be' => $drill->beUser?->only(['id', 'full_name', 'email']),
                'oim' => $drill->oimUser?->only(['id', 'full_name', 'email']),
            ],
        ];
    }
}
