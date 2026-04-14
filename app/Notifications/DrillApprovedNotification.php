<?php

namespace App\Notifications;

use App\Models\DrillRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DrillApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly DrillRecord $drillRecord)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Drill Approved: {$this->drillRecord->reference_no}")
            ->greeting("Hello {$notifiable->full_name},")
            ->line("The drill for rig {$this->drillRecord->rig->name} has been approved.")
            ->line("Drill Type: {$this->drillRecord->drillTypeNames()}")
            ->line("Event Type: {$this->drillRecord->eventTypeNames()}")
            ->action('View Drill', route('drills.show', $this->drillRecord))
            ->line('You can now review the final record and any follow-up actions.');
    }
}
