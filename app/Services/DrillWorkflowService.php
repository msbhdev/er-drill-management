<?php

namespace App\Services;

use App\Enums\DrillWorkflowAction;
use App\Enums\UserRole;
use App\Models\DrillRecord;
use App\Models\DrillStatus;
use App\Models\DrillWorkflowHistory;
use App\Models\Rig;
use App\Models\User;
use App\Notifications\DrillApprovedNotification;
use App\Notifications\DrillSubmittedNotification;
use App\Notifications\DrillVerifiedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DrillWorkflowService
{
    public function saveDraft(DrillRecord $drillRecord, array $payload, User $actor): DrillRecord
    {
        return DB::transaction(function () use ($drillRecord, $payload, $actor) {
            $fromStatus = $drillRecord->exists ? $drillRecord->status()->first() : null;

            if ($fromStatus && ! in_array($fromStatus->code, ['draft', 'returned_by_be', 'returned_by_oim'], true)) {
                throw new AuthorizationException('This drill can no longer be edited as a draft.');
            }

            $status = $fromStatus ?? DrillStatus::query()->where('code', 'draft')->firstOrFail();

            $drillRecord->fill(Arr::except($payload, ['drill_type_ids', 'event_type_ids']));
            $drillRecord->status()->associate($status);
            $drillRecord->reference_no ??= $this->generateReference($payload['rig_id'] ?? $actor->rig_id);
            $drillRecord->created_by_user_id ??= $actor->id;

            $this->assignRigRoleAccounts($drillRecord, $actor);

            $drillRecord->save();
            $this->syncTypes($drillRecord, $payload);

            $this->recordHistory($drillRecord, DrillWorkflowAction::SaveDraft, $actor, $fromStatus, $status, 'Draft saved.');

            return $drillRecord->fresh(['status', 'rig', 'drillType', 'eventType', 'drillTypes', 'eventTypes']);
        });
    }

    public function submit(DrillRecord $drillRecord, User $actor): DrillRecord
    {
        return $this->transition($drillRecord, DrillWorkflowAction::Submit, 'submitted', $actor, null, 'Drill submitted for BE verification.');
    }

    public function returnByBe(DrillRecord $drillRecord, User $actor, ?string $comments): DrillRecord
    {
        return $this->transition($drillRecord, DrillWorkflowAction::ReturnByBe, 'returned_by_be', $actor, $comments, $comments);
    }

    public function verify(DrillRecord $drillRecord, User $actor, ?string $comments): DrillRecord
    {
        return $this->transition($drillRecord, DrillWorkflowAction::Verify, 'verified', $actor, $comments, $comments);
    }

    public function returnByOim(DrillRecord $drillRecord, User $actor, ?string $comments): DrillRecord
    {
        return $this->transition($drillRecord, DrillWorkflowAction::ReturnByOim, 'returned_by_oim', $actor, $comments, $comments);
    }

    public function approve(DrillRecord $drillRecord, User $actor, ?string $comments): DrillRecord
    {
        return $this->transition($drillRecord, DrillWorkflowAction::Approve, 'approved', $actor, $comments, $comments);
    }

    public function close(DrillRecord $drillRecord, User $actor, ?string $comments): DrillRecord
    {
        return $this->transition($drillRecord, DrillWorkflowAction::Close, 'closed', $actor, $comments, $comments);
    }

    private function transition(
        DrillRecord $drillRecord,
        DrillWorkflowAction $action,
        string $targetCode,
        User $actor,
        ?string $comments,
        ?string $historyComment
    ): DrillRecord {
        return DB::transaction(function () use ($drillRecord, $action, $targetCode, $actor, $comments, $historyComment) {
            $fromStatus = $drillRecord->status()->first();
            $toStatus = DrillStatus::query()->where('code', $targetCode)->firstOrFail();

            $drillRecord->status()->associate($toStatus);

            if ($targetCode === 'submitted') {
                $drillRecord->submitted_at = now();
            }

            if ($targetCode === 'verified') {
                $drillRecord->verified_at = now();
            }

            if ($targetCode === 'approved') {
                $drillRecord->approved_at = now();
            }

            if ($targetCode === 'closed') {
                $drillRecord->closed_at = now();
            }

            $drillRecord->save();

            $this->recordHistory($drillRecord, $action, $actor, $fromStatus, $toStatus, $historyComment ?: $comments);
            $this->sendNotifications($drillRecord->fresh(['rig', 'drillType', 'eventType', 'drillTypes', 'eventTypes', 'stoUser', 'beUser', 'oimUser']), $targetCode);

            return $drillRecord->fresh(['status', 'rig', 'drillType', 'eventType', 'drillTypes', 'eventTypes', 'workflowHistory']);
        });
    }

    private function syncTypes(DrillRecord $drillRecord, array $payload): void
    {
        $drillTypeIds = array_values(array_filter($payload['drill_type_ids'] ?? [$drillRecord->drill_type_id]));
        $eventTypeIds = array_values(array_filter($payload['event_type_ids'] ?? [$drillRecord->event_type_id]));

        $drillRecord->drillTypes()->sync($drillTypeIds);
        $drillRecord->eventTypes()->sync($eventTypeIds);
    }

    private function recordHistory(
        DrillRecord $drillRecord,
        DrillWorkflowAction $action,
        User $actor,
        ?DrillStatus $fromStatus,
        ?DrillStatus $toStatus,
        ?string $comments
    ): DrillWorkflowHistory {
        return DrillWorkflowHistory::query()->create([
            'drill_record_id' => $drillRecord->id,
            'actor_user_id' => $actor->id,
            'actor_account_name' => $actor->full_name,
            'actor_person_name' => $actor->currentAssigneeName(),
            'actor_role_code' => $actor->role,
            'actor_rig_code' => $actor->rig_code,
            'from_status_id' => $fromStatus?->id,
            'to_status_id' => $toStatus?->id,
            'action' => $action->value,
            'comments' => $comments,
        ]);
    }

    private function assignRigRoleAccounts(DrillRecord $drillRecord, User $actor): void
    {
        $rigCode = Rig::query()->whereKey($drillRecord->rig_id)->value('code');

        $rigUsers = User::query()
            ->withAppAccess(config('er_drill.auth_app_code'))
            ->where('rig_code', $rigCode)
            ->where('active_status', true)
            ->whereIn('role_code', [UserRole::STO->value, UserRole::BE->value, UserRole::OIM->value])
            ->get()
            ->keyBy('role_code');

        $stoUser = $actor->role === UserRole::STO->value ? $actor : $rigUsers->get(UserRole::STO->value);
        $beUser = $rigUsers->get(UserRole::BE->value);
        $oimUser = $rigUsers->get(UserRole::OIM->value);

        $drillRecord->sto_user_id = $stoUser?->id;
        $drillRecord->be_user_id = $beUser?->id;
        $drillRecord->oim_user_id = $oimUser?->id;
        $drillRecord->sto_name = $stoUser?->currentAssigneeName();
        $drillRecord->be_name = $beUser?->currentAssigneeName();
        $drillRecord->oim_name = $oimUser?->currentAssigneeName();
    }

    private function generateReference(?int $rigId): string
    {
        $prefix = 'DRILL';

        if ($rigId) {
            $rigCode = optional(\App\Models\Rig::find($rigId))->code;
            $prefix = $rigCode ?: $prefix;
        }

        return Str::upper(sprintf('%s-%s-%s', $prefix, now()->format('Ymd-His'), Str::padLeft((string) random_int(0, 999), 3, '0')));
    }

    private function sendNotifications(DrillRecord $drillRecord, string $statusCode): void
    {
        if ($statusCode === 'submitted' && $drillRecord->beUser) {
            $drillRecord->beUser->notify(new DrillSubmittedNotification($drillRecord));
        }

        if ($statusCode === 'verified' && $drillRecord->oimUser) {
            $drillRecord->oimUser->notify(new DrillVerifiedNotification($drillRecord));
        }

        if ($statusCode === 'approved') {
            $recipients = User::query()
                ->withAppAccess(config('er_drill.auth_app_code'))
                ->where('active_status', true)
                ->where(function ($query) use ($drillRecord) {
                    $query
                        ->whereIn('id', array_filter([$drillRecord->sto_user_id, $drillRecord->be_user_id]))
                        ->orWhere(function ($subQuery) use ($drillRecord) {
                            $subQuery->where('rig_code', optional($drillRecord->rig)->code)
                                ->where('role_code', UserRole::RM->value);
                        });
                })
                ->get();

            foreach ($recipients as $recipient) {
                $recipient->notify(new DrillApprovedNotification($drillRecord));
            }
        }
    }
}
