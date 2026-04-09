<?php

namespace App\Livewire\Drills;

use App\Models\DrillRecord;
use App\Models\DrillStatus;
use App\Models\DrillType;
use App\Models\EventType;
use App\Models\Rig;
use App\Services\DrillReportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';
    public ?int $statusId = null;
    public ?int $rigId = null;
    public ?int $drillTypeId = null;
    public ?int $eventTypeId = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function updating($property): void
    {
        if (in_array($property, ['search', 'statusId', 'rigId', 'drillTypeId', 'eventTypeId', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function render(DrillReportService $reportService)
    {
        $user = auth()->user();
        $drills = $reportService->queryForUser($user, [
            'search' => $this->search,
            'status_id' => $this->statusId,
            'rig_id' => $this->rigId,
            'drill_type_id' => $this->drillTypeId,
            'event_type_id' => $this->eventTypeId,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ])->paginate(10);

        return view('livewire.drills.index-page', [
            'drills' => $drills,
            'statuses' => DrillStatus::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'rigs' => $user->isManagement() || $user->isAdministrator()
                ? Rig::query()->where('is_active', true)->orderBy('name')->get()
                : Rig::query()->whereKey($user->rig_id)->get(),
            'drillTypes' => DrillType::query()->where('is_active', true)->orderBy('name')->get(),
            'eventTypes' => EventType::query()->where('is_active', true)->orderBy('name')->get(),
            'canCreate' => Gate::allows('create', DrillRecord::class),
        ])->layout('layouts.app');
    }
}
