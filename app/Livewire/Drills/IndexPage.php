<?php

namespace App\Livewire\Drills;

use App\Models\DrillRecord;
use App\Models\DrillStatus;
use App\Models\DrillType;
use App\Models\EventType;
use App\Models\Rig;
use App\Services\DrillDeletionService;
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

    public bool $confirmingDeletion = false;
    public ?int $deletingDrillId = null;
    public string $deletingDrillReference = '';
    public string $deleteConfirmationReference = '';

    public function updating($property): void
    {
        if (in_array($property, ['search', 'statusId', 'rigId', 'drillTypeId', 'eventTypeId', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function confirmDeletion(int $drillId): void
    {
        $drill = DrillRecord::query()->findOrFail($drillId);
        $this->authorize('delete', $drill);

        $this->deletingDrillId = $drill->id;
        $this->deletingDrillReference = $drill->reference_no;
        $this->deleteConfirmationReference = '';
        $this->resetErrorBag('deleteConfirmationReference');
        $this->confirmingDeletion = true;
    }

    public function cancelDeletion(): void
    {
        $this->confirmingDeletion = false;
        $this->deletingDrillId = null;
        $this->deletingDrillReference = '';
        $this->deleteConfirmationReference = '';
        $this->resetErrorBag('deleteConfirmationReference');
    }

    public function deleteDrill(DrillDeletionService $service): void
    {
        $drill = DrillRecord::query()->findOrFail($this->deletingDrillId);
        $this->authorize('delete', $drill);

        if (trim($this->deleteConfirmationReference) !== $drill->reference_no) {
            $this->addError('deleteConfirmationReference', 'The reference number does not match. Type it exactly to confirm.');

            return;
        }

        $reference = $drill->reference_no;
        $service->delete($drill, auth()->user());

        $this->cancelDeletion();
        $this->resetPage();

        session()->flash('status', "Drill {$reference} has been permanently deleted.");
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
            'canDelete' => $user->isAdministrator(),
        ])->layout('layouts.app');
    }
}
