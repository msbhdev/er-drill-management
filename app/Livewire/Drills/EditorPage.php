<?php

namespace App\Livewire\Drills;

use App\Models\ActionStatus;
use App\Models\DrillAction;
use App\Models\DrillAttachment;
use App\Models\DrillEvent;
use App\Models\DrillRecord;
use App\Models\DrillType;
use App\Models\Dsha;
use App\Models\EventType;
use App\Models\Rig;
use App\Models\User;
use App\Services\DrillWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditorPage extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public ?DrillRecord $drillRecord = null;

    public ?int $rigId = null;

    public array $drillTypeIds = [];

    public ?int $eventTypeId = null;

    public ?int $statusId = null;

    public ?string $drillDate = null;

    public ?string $drillTime = null;

    public ?string $onDutyCrews = null;

    public ?string $eventLocation = null;

    public ?string $scenario = null;

    public array $applicableDshaIds = [];

    public ?string $performanceStandard = null;

    public ?string $performanceStandardsMet = null;

    public ?string $objectives = null;

    public ?string $debriefAttendees = null;

    public ?string $positiveObservations = null;

    public ?string $improvementOpportunities = null;

    public ?string $otherComments = null;

    public array $events = [];

    public array $actions = [];

    public array $newAttachments = [];

    public array $newAttachmentCaptions = [];

    public ?string $reviewComments = null;

    public function mount(?DrillRecord $drillRecord = null): void
    {
        $user = auth()->user();

        if ($drillRecord?->exists) {
            $drillRecord->load(['events', 'actions.status', 'attachments', 'status', 'drillTypes', 'eventTypes', 'dshas', 'workflowHistory.actor', 'workflowHistory.fromStatus', 'workflowHistory.toStatus']);
            $this->authorize('view', $drillRecord);
            $this->drillRecord = $drillRecord;
            $this->fillFromModel($drillRecord);

            return;
        }

        Gate::authorize('create', DrillRecord::class);

        $this->rigId = $user->rig_id;
        $this->drillDate = now()->toDateString();
        $this->addEvent();
        $this->addAction();
    }

    public function addEvent(): void
    {
        $this->events[] = [
            'event_time' => '',
            'event_description' => '',
        ];
    }

    public function removeEvent(int $index): void
    {
        unset($this->events[$index]);
        $this->events = array_values($this->events);
    }

    public function addAction(): void
    {
        $openStatusId = ActionStatus::query()->where('code', 'open')->value('id');

        $this->actions[] = [
            'action_description' => '',
            'action_owner' => '',
            'action_status_id' => $openStatusId,
            'due_date' => '',
        ];
    }

    public function removeAction(int $index): void
    {
        unset($this->actions[$index]);
        $this->actions = array_values($this->actions);
    }

    public function removeAttachment(int $attachmentId): void
    {
        abort_unless($this->drillRecord && $this->drillRecord->isEditableBy(auth()->user()), 403);

        $attachment = DrillAttachment::query()->where('drill_record_id', $this->drillRecord->id)->findOrFail($attachmentId);
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        $this->drillRecord->refresh()->load('attachments');
    }

    public function addNewAttachment(): void
    {
        $this->newAttachments[] = null;
        $this->newAttachmentCaptions[] = '';
    }

    public function removeNewAttachment(int $index): void
    {
        unset($this->newAttachments[$index], $this->newAttachmentCaptions[$index]);

        $this->newAttachments = array_values($this->newAttachments);
        $this->newAttachmentCaptions = array_values($this->newAttachmentCaptions);
    }

    public function resetForm(): void
    {
        abort_unless($this->drillRecord ? $this->drillRecord->isEditableBy(auth()->user()) : Gate::allows('create', DrillRecord::class), 403);

        $this->drillTypeIds = [];
        $this->eventTypeId = null;
        $this->drillDate = null;
        $this->drillTime = null;
        $this->onDutyCrews = null;
        $this->eventLocation = null;
        $this->scenario = null;
        $this->applicableDshaIds = [];
        $this->performanceStandard = null;
        $this->performanceStandardsMet = null;
        $this->objectives = null;
        $this->debriefAttendees = null;
        $this->positiveObservations = null;
        $this->improvementOpportunities = null;
        $this->otherComments = null;
        $this->reviewComments = null;
        $this->events = [];
        $this->actions = [];
        $this->newAttachments = [];
        $this->newAttachmentCaptions = [];

        $this->addEvent();
        $this->addAction();
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function saveDraft(DrillWorkflowService $workflowService): void
    {
        $record = $this->persistDraft($workflowService);

        session()->flash('status', 'Draft saved successfully.');
        $this->redirectRoute('drills.show', $record, navigate: true);
    }

    public function submit(DrillWorkflowService $workflowService): void
    {
        $record = $this->persistDraft($workflowService);
        $this->authorize('submit', $record);
        $workflowService->submit($record, auth()->user());

        session()->flash('status', 'Drill submitted to BE for verification.');
        $this->redirectRoute('drills.show', $record, navigate: true);
    }

    public function verify(DrillWorkflowService $workflowService): void
    {
        abort_unless($this->drillRecord, 404);
        $this->authorize('verify', $this->drillRecord);
        $workflowService->verify($this->drillRecord, auth()->user(), $this->reviewComments);

        session()->flash('status', 'Drill verified and routed to OIM.');
        $this->redirectRoute('drills.show', $this->drillRecord, navigate: true);
    }

    public function returnByBe(DrillWorkflowService $workflowService): void
    {
        abort_unless($this->drillRecord, 404);
        $this->authorize('verify', $this->drillRecord);
        $this->validate(['reviewComments' => ['required', 'string', 'min:5']]);
        $workflowService->returnByBe($this->drillRecord, auth()->user(), $this->reviewComments);

        session()->flash('status', 'Drill returned to STO.');
        $this->redirectRoute('drills.show', $this->drillRecord, navigate: true);
    }

    public function approve(DrillWorkflowService $workflowService): void
    {
        abort_unless($this->drillRecord, 404);
        $this->authorize('approve', $this->drillRecord);
        $workflowService->approve($this->drillRecord, auth()->user(), $this->reviewComments);

        session()->flash('status', 'Drill approved successfully.');
        $this->redirectRoute('drills.show', $this->drillRecord, navigate: true);
    }

    public function returnByOim(DrillWorkflowService $workflowService): void
    {
        abort_unless($this->drillRecord, 404);
        $this->authorize('approve', $this->drillRecord);
        $this->validate(['reviewComments' => ['required', 'string', 'min:5']]);
        $workflowService->returnByOim($this->drillRecord, auth()->user(), $this->reviewComments);

        session()->flash('status', 'Drill returned to STO by OIM.');
        $this->redirectRoute('drills.show', $this->drillRecord, navigate: true);
    }

    public function closeRecord(DrillWorkflowService $workflowService): void
    {
        abort_unless($this->drillRecord, 404);
        $this->authorize('close', $this->drillRecord);

        if ($this->drillRecord->hasOpenActions()) {
            session()->flash('status', 'All follow-up actions must be marked Closed before this drill can be closed.');

            return;
        }

        $workflowService->close($this->drillRecord, auth()->user(), $this->reviewComments);

        session()->flash('status', 'Drill has been closed.');
        $this->redirectRoute('drills.show', $this->drillRecord, navigate: true);
    }

    public function render()
    {
        $user = auth()->user();
        $editable = $this->drillRecord ? $this->drillRecord->fresh('status')?->isEditableBy($user) : Gate::allows('create', DrillRecord::class);

        $rigCode = $this->rigId ? Rig::query()->whereKey($this->rigId)->value('code') : null;
        $actionOwners = $rigCode
            ? User::query()
                ->withAppAccess(config('er_drill.auth_app_code'))
                ->where('rig_code', $rigCode)
                ->where('active_status', true)
                ->orderBy('full_name')
                ->pluck('full_name')
                ->all()
            : [];

        return view('livewire.drills.editor-page', [
            'record' => $this->drillRecord?->fresh([
                'rig',
                'drillType',
                'eventType',
                'drillTypes',
                'eventTypes',
                'dshas',
                'status',
                'attachments',
                'workflowHistory.actor',
                'workflowHistory.fromStatus',
                'workflowHistory.toStatus',
            ]),
            'rigs' => $user->isManagement() || $user->isAdministrator()
                ? Rig::query()->where('is_active', true)->orderBy('name')->get()
                : Rig::query()->whereKey($user->rig_id)->get(),
            'drillTypes' => DrillType::query()->where('is_active', true)->orderBy('name')->get(),
            'eventTypes' => EventType::query()->where('is_active', true)->orderBy('name')->get(),
            'dshas' => Dsha::query()->where('is_active', true)->orderBy('code')->get(),
            'actionStatuses' => ActionStatus::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'actionOwners' => $actionOwners,
            'editable' => $editable,
            'canVerify' => $this->drillRecord ? Gate::allows('verify', $this->drillRecord) : false,
            'canApprove' => $this->drillRecord ? Gate::allows('approve', $this->drillRecord) : false,
            'canClose' => $this->drillRecord ? Gate::allows('close', $this->drillRecord) : false,
            'hasOpenActions' => $this->drillRecord ? $this->drillRecord->hasOpenActions() : false,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'rigId' => ['required', 'exists:rigs,id'],
            'drillTypeIds' => ['required', 'array', 'min:1'],
            'drillTypeIds.*' => ['integer', 'exists:drill_types,id'],
            'eventTypeId' => ['required', 'integer', 'exists:event_types,id'],
            'drillDate' => ['required', 'date'],
            'drillTime' => ['nullable', 'date_format:H:i'],
            'onDutyCrews' => ['nullable', 'string'],
            'eventLocation' => ['nullable', 'string', 'max:255'],
            'scenario' => ['nullable', 'string'],
            'applicableDshaIds' => ['array'],
            'applicableDshaIds.*' => ['integer', 'exists:dshas,id'],
            'performanceStandard' => ['nullable', 'string'],
            'performanceStandardsMet' => ['nullable', 'string', 'max:50'],
            'objectives' => ['nullable', 'string'],
            'debriefAttendees' => ['nullable', 'string'],
            'positiveObservations' => ['nullable', 'string'],
            'improvementOpportunities' => ['nullable', 'string'],
            'otherComments' => ['nullable', 'string'],
            'events' => ['array'],
            'events.*.event_time' => ['nullable', 'date_format:H:i'],
            'events.*.event_description' => ['nullable', 'string'],
            'actions' => ['array'],
            'actions.*.action_description' => ['nullable', 'string'],
            'actions.*.action_owner' => ['nullable', 'string', 'max:255'],
            'actions.*.action_status_id' => ['nullable', 'exists:action_statuses,id'],
            'actions.*.due_date' => ['nullable', 'date'],
            'newAttachments' => ['array'],
            'newAttachments.*' => ['nullable', 'image', 'max:1536'],
            'newAttachmentCaptions' => ['array'],
            'newAttachmentCaptions.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function persistDraft(DrillWorkflowService $workflowService): DrillRecord
    {
        $record = $this->drillRecord ?? new DrillRecord;
        $this->authorize($record->exists ? 'update' : 'create', $record->exists ? $record : DrillRecord::class);

        $validated = $this->validate();
        $drillTypeIds = $this->normalizeIds($validated['drillTypeIds']);
        $payload = [
            'rig_id' => $validated['rigId'],
            'drill_type_id' => $drillTypeIds[0],
            'event_type_id' => $validated['eventTypeId'],
            'drill_type_ids' => $drillTypeIds,
            'event_type_ids' => [$validated['eventTypeId']],
            'drill_date' => $validated['drillDate'],
            'drill_time' => $validated['drillTime'],
            'on_duty_crews' => $validated['onDutyCrews'],
            'event_location' => $validated['eventLocation'],
            'scenario' => $validated['scenario'],
            'dsha_ids' => $this->normalizeIds($this->applicableDshaIds),
            'performance_standard' => $validated['performanceStandard'],
            'performance_standards_met' => $validated['performanceStandardsMet'],
            'objectives' => $validated['objectives'],
            'debrief_attendees' => $validated['debriefAttendees'],
            'positive_observations' => $validated['positiveObservations'],
            'improvement_opportunities' => $validated['improvementOpportunities'],
            'other_comments' => $validated['otherComments'],
        ];

        $record = $workflowService->saveDraft($record, $payload, auth()->user());
        $this->syncChildRecords($record);
        $this->drillRecord = $record->fresh(['events', 'actions.status', 'attachments', 'status', 'drillTypes', 'eventTypes', 'dshas', 'workflowHistory.actor', 'workflowHistory.fromStatus', 'workflowHistory.toStatus']);

        return $this->drillRecord;
    }

    private function syncChildRecords(DrillRecord $record): void
    {
        $record->events()->delete();
        collect($this->events)
            ->filter(fn (array $event) => filled($event['event_time'] ?? null) || filled($event['event_description'] ?? null))
            ->each(function (array $event) use ($record) {
                DrillEvent::query()->create([
                    'drill_record_id' => $record->id,
                    'event_time' => $event['event_time'] ?: null,
                    'event_description' => $event['event_description'],
                    'created_by_user_id' => auth()->id(),
                ]);
            });

        $record->actions()->delete();
        $defaultActionStatusId = ActionStatus::query()->where('code', 'open')->value('id');

        collect($this->actions)
            ->filter(fn (array $action) => filled($action['action_description'] ?? null))
            ->each(function (array $action) use ($record, $defaultActionStatusId) {
                DrillAction::query()->create([
                    'drill_record_id' => $record->id,
                    'action_description' => $action['action_description'],
                    'action_owner' => $action['action_owner'] ?: null,
                    'action_status_id' => $action['action_status_id'] ?: $defaultActionStatusId,
                    'due_date' => $action['due_date'] ?: null,
                    'created_by_user_id' => auth()->id(),
                ]);
            });

        foreach ($this->newAttachments as $index => $upload) {
            if (! $upload) {
                continue;
            }

            $path = $upload->store("drills/{$record->id}", 'public');

            DrillAttachment::query()->create([
                'drill_record_id' => $record->id,
                'caption' => blank($this->newAttachmentCaptions[$index] ?? null)
                    ? pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME)
                    : trim($this->newAttachmentCaptions[$index]),
                'file_path' => $path,
                'file_name' => $upload->getClientOriginalName(),
                'file_size_kb' => (int) ceil($upload->getSize() / 1024),
                'mime_type' => $upload->getClientMimeType(),
                'created_by_user_id' => auth()->id(),
            ]);
        }

        $this->newAttachments = [];
        $this->newAttachmentCaptions = [];
    }

    private function fillFromModel(DrillRecord $record): void
    {
        $this->rigId = $record->rig_id;
        $this->drillTypeIds = $record->drillTypes->pluck('id')->all();
        $this->eventTypeId = $record->event_type_id ?? $record->eventTypes->first()?->id;
        $this->statusId = $record->status_id;
        $this->drillDate = optional($record->drill_date)->format('Y-m-d');
        $this->drillTime = $record->drill_time ? $record->drill_time->format('H:i') : null;
        $this->onDutyCrews = $record->on_duty_crews;
        $this->eventLocation = $record->event_location;
        $this->scenario = $record->scenario;
        $this->applicableDshaIds = $record->dshas->pluck('id')->all();
        $this->performanceStandard = $record->performance_standard;
        $this->performanceStandardsMet = $record->performance_standards_met;
        $this->objectives = $record->objectives;
        $this->debriefAttendees = $record->debrief_attendees;
        $this->positiveObservations = $record->positive_observations;
        $this->improvementOpportunities = $record->improvement_opportunities;
        $this->otherComments = $record->other_comments;
        $this->events = $record->events->map(fn (DrillEvent $event) => [
            'event_time' => $event->event_time,
            'event_description' => $event->event_description,
        ])->all();
        $this->actions = $record->actions->map(fn (DrillAction $action) => [
            'action_description' => $action->action_description,
            'action_owner' => $action->action_owner,
            'action_status_id' => $action->action_status_id,
            'due_date' => optional($action->due_date)->format('Y-m-d'),
        ])->all();

        if ($this->events === []) {
            $this->addEvent();
        }

        if ($this->actions === []) {
            $this->addAction();
        }

        if ($this->drillTypeIds === [] && $record->drill_type_id) {
            $this->drillTypeIds = [$record->drill_type_id];
        }
    }

    private function normalizeIds(array $ids): array
    {
        return collect($ids)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
