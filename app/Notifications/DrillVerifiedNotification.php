<?php

namespace App\Notifications;

use App\Models\DrillRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DrillVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly DrillRecord $drillRecord) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Drill Verified: {$this->drillRecord->reference_no}")
            ->greeting("Hello {$notifiable->full_name},")
            ->line("A drill for rig {$this->drillRecord->rig->name} has been verified by BE and is ready for your approval.")
            ->line("Drill Type: {$this->drillRecord->drillTypeNames()}")
            ->line("Event Type: {$this->drillRecord->eventTypeNames()}")
            ->action('Approve Drill', route('drills.show', $this->drillRecord))
            ->line('Please approve or return it with comments.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'verified',
            'title' => 'Drill awaiting approval',
            'message' => "Drill {$this->drillRecord->reference_no} for {$this->drillRecord->rig->name} has been verified and needs your approval.",
            'reference_no' => $this->drillRecord->reference_no,
            'drill_id' => $this->drillRecord->id,
            'rig' => $this->drillRecord->rig->name,
            'url' => route('drills.show', $this->drillRecord),
        ];
    }
}
