<?php

namespace App\Notifications;

use App\Models\DrillRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DrillSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject("Drill Submitted: {$this->drillRecord->reference_no}")
            ->greeting("Hello {$notifiable->full_name},")
            ->line("A drill for rig {$this->drillRecord->rig->name} has been submitted and is waiting for your verification.")
            ->line("Drill Type: {$this->drillRecord->drillType->name}")
            ->line("Event Type: {$this->drillRecord->eventType->name}")
            ->action('Review Drill', route('drills.show', $this->drillRecord))
            ->line('Please review and either verify or return it for correction.');
    }
}
