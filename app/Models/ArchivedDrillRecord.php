<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ArchivedDrillRecord extends Model
{
    protected $fillable = [
        'original_drill_id',
        'reference_no',
        'rig_name',
        'status_name',
        'drill_date',
        'snapshot',
        'deleted_by_user_id',
        'deleted_by_name',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'drill_date' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Rehydrate a transient (unsaved) DrillRecord with its relations from the
     * stored snapshot, so the existing drill-print view can render an archived
     * drill exactly as it looked when live. The models are never persisted.
     */
    public function toDrillRecord(): DrillRecord
    {
        $snapshot = $this->snapshot ?? [];

        $record = (new DrillRecord)->forceFill($snapshot['drill'] ?? []);

        $record->setRelation('rig', (new Rig)->forceFill($snapshot['rig'] ?? []));
        $record->setRelation('status', (new DrillStatus)->forceFill($snapshot['status'] ?? []));
        $record->setRelation('drillTypes', $this->hydrateMany($snapshot['drill_types'] ?? [], DrillType::class));
        $record->setRelation('eventTypes', $this->hydrateMany($snapshot['event_types'] ?? [], EventType::class));
        $record->setRelation('dshas', $this->hydrateMany($snapshot['dshas'] ?? [], Dsha::class));
        $record->setRelation('events', $this->hydrateMany($snapshot['events'] ?? [], DrillEvent::class));

        $record->setRelation('actions', new Collection(array_map(function (array $action) {
            $status = $action['status'] ?? [];
            unset($action['status']);

            return (new DrillAction)
                ->forceFill($action)
                ->setRelation('status', (new ActionStatus)->forceFill($status ?? []));
        }, $snapshot['actions'] ?? [])));

        $record->setRelation('workflowHistory', new Collection(array_map(function (array $history) {
            unset($history['actor'], $history['from_status'], $history['to_status']);

            // The print view reads the denormalised actor_* columns directly.
            return (new DrillWorkflowHistory)->forceFill($history)->setRelation('actor', null);
        }, $snapshot['workflow_history'] ?? [])));

        return $record;
    }

    private function hydrateMany(array $rows, string $model): Collection
    {
        return new Collection(array_map(fn (array $row) => (new $model)->forceFill($row), $rows));
    }
}
