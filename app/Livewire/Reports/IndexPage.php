<?php

namespace App\Livewire\Reports;

use App\Models\DrillRecord;
use App\Models\DrillStatus;
use App\Models\DrillType;
use App\Models\EventType;
use App\Models\Rig;
use App\Services\DrillReportService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $statusId = null;
    public ?int $rigId = null;
    public ?int $drillTypeId = null;
    public ?int $eventTypeId = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function mount(): void
    {
        Gate::authorize('export', DrillRecord::class);
    }

    public function render(DrillReportService $reportService)
    {
        $filters = [
            'search' => $this->search,
            'status_id' => $this->statusId,
            'rig_id' => $this->rigId,
            'drill_type_id' => $this->drillTypeId,
            'event_type_id' => $this->eventTypeId,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ];

        $query = $reportService->queryForUser(auth()->user(), $filters);
        $records = (clone $query)->paginate(10);
        $recordsCollection = (clone $query)->get();

        return view('livewire.reports.index-page', [
            'records' => $records,
            'summary' => [
                'total' => $recordsCollection->count(),
                'approved' => $recordsCollection->where('status.code', 'approved')->count(),
                'returned' => $recordsCollection->filter(fn ($record) => in_array($record->status->code, ['returned_by_be', 'returned_by_oim'], true))->count(),
                'pending' => $recordsCollection->filter(fn ($record) => in_array($record->status->code, ['submitted', 'verified'], true))->count(),
            ],
            'statuses' => DrillStatus::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'rigs' => auth()->user()->isManagement() || auth()->user()->isAdministrator()
                ? Rig::query()->where('is_active', true)->orderBy('name')->get()
                : Rig::query()->whereKey(auth()->user()->rig_id)->get(),
            'drillTypes' => DrillType::query()->where('is_active', true)->orderBy('name')->get(),
            'eventTypes' => EventType::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
