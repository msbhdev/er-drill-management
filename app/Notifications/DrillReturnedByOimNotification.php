<?php

namespace App\Notifications;

use App\Models\DrillRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DrillReturnedByOimNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DrillRecord $drillRecord,
        private readonly ?string $comments = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Drill Returned: {$this->drillRecord->reference_no}")
            ->greeting("Hello {$notifiable->full_name},")
            ->line("The drill for rig {$this->drillRecord->rig->name} has been returned by OIM for correction.");

        if ($this->comments) {
            $mail->line("Reviewer comments: {$this->comments}");
        }

        return $mail
            ->action('Review Drill', route('drills.show', $this->drillRecord))
            ->line('Please update the drill and resubmit it through the workflow.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'returned_by_oim',
            'title' => 'Drill returned by OIM',
            'message' => "Drill {$this->drillRecord->reference_no} for {$this->drillRecord->rig->name} was returned by OIM for correction.",
            'reference_no' => $this->drillRecord->reference_no,
            'drill_id' => $this->drillRecord->id,
            'rig' => $this->drillRecord->rig->name,
            'comments' => $this->comments,
            'url' => route('drills.show', $this->drillRecord),
        ];
    }
}
