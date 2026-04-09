<?php

namespace App\Services;

use App\Enums\DrillWorkflowAction;
use App\Enums\UserRole;
use App\Models\DrillRecord;
use App\Models\DrillStatus;
use App\Models\DrillWorkflowHistory;
use App\Models\User;
use App\Notifications\DrillApprovedNotification;
use App\Notifications\DrillSubmittedNotification;
use App\Notifications\DrillVerifiedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DrillWorkflowService
{
    public function saveDraft(DrillRecord $drillRecord, array $payload, User $actor): DrillRecord
    {
        return DB::transaction(function () use ($drillRecord, $payload, $actor) {
            $status = DrillStatus::query()->where('code', 'draft')->firstOrFail();

            $drillRecord->fill($payload);
            $drillRecord->status()->associate($status);
            $drillRecord->reference_no ??= $this->generateReference($payload['rig_id'] ?? $actor->rig_id);
            $drillRecord->created_by_user_id ??= $actor->id;

            $this->assignRigRoleAccounts($drillRecord, $actor);

            $drillRecord->save();

            $this->recordHistory($drillRecord, DrillWorkflowAction::SaveDraft, $actor, null, $status, 'Draft saved.');

            return $drillRecord->fresh(['status', 'rig', 'drillType', 'eventType']);
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
            $this->sendNotifications($drillRecord->fresh(['rig', 'drillType', 'eventType', 'stoUser', 'beUser', 'oimUser']), $targetCode);

            return $drillRecord->fresh(['status', 'rig', 'drillType', 'eventType', 'workflowHistory']);
        });
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
            'from_status_id' => $fromStatus?->id,
            'to_status_id' => $toStatus?->id,
            'action' => $action->value,
            'comments' => $comments,
        ]);
    }

    private function assignRigRoleAccounts(DrillRecord $drillRecord, User $actor): void
    {
        $rigUsers = User::query()
            ->where('rig_id', $drillRecord->rig_id)
            ->where('active_status', true)
            ->whereIn('role', [UserRole::STO->value, UserRole::BE->value, UserRole::OIM->value])
            ->get()
            ->keyBy('role');

        $stoUser = $actor->role === UserRole::STO->value ? $actor : $rigUsers->get(UserRole::STO->value);
        $beUser = $rigUsers->get(UserRole::BE->value);
        $oimUser = $rigUsers->get(UserRole::OIM->value);

        $drillRecord->sto_user_id = $stoUser?->id;
        $drillRecord->be_user_id = $beUser?->id;
        $drillRecord->oim_user_id = $oimUser?->id;
        $drillRecord->sto_name = $stoUser?->full_name;
        $drillRecord->be_name = $beUser?->full_name;
        $drillRecord->oim_name = $oimUser?->full_name;
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
                ->where('active_status', true)
                ->where(function ($query) use ($drillRecord) {
                    $query
                        ->whereIn('id', array_filter([$drillRecord->sto_user_id, $drillRecord->be_user_id]))
                        ->orWhere(function ($subQuery) use ($drillRecord) {
                            $subQuery->where('rig_id', $drillRecord->rig_id)
                                ->where('role', UserRole::RM->value);
                        });
                })
                ->get();

            foreach ($recipients as $recipient) {
                $recipient->notify(new DrillApprovedNotification($drillRecord));
            }
        }
    }
}
