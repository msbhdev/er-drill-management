<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Drills\EditorPage;
use App\Models\DrillAttachment;
use App\Models\DrillRecord;
use App\Models\DrillStatus;
use App\Models\DrillType;
use App\Models\EventType;
use App\Models\Rig;
use App\Models\User;
use App\Services\DrillWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DrillWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sto_can_create_and_submit_a_drill(): void
    {
        $rig = Rig::factory()->create();
        $sto = User::factory()->create(['role' => UserRole::STO->value, 'rig_id' => $rig->id]);
        User::factory()->create(['role' => UserRole::BE->value, 'rig_id' => $rig->id]);
        User::factory()->create(['role' => UserRole::OIM->value, 'rig_id' => $rig->id]);

        $drillType = DrillType::query()->create(['name' => 'Fire Drill', 'is_active' => true]);
        $eventType = EventType::query()->create(['name' => 'Fire', 'is_active' => true]);

        $service = app(DrillWorkflowService::class);

        $record = $service->saveDraft(new DrillRecord(), [
            'rig_id' => $rig->id,
            'drill_type_id' => $drillType->id,
            'event_type_id' => $eventType->id,
            'drill_date' => now()->toDateString(),
        ], $sto);

        $this->assertSame('draft', $record->status->code);

        $record = $service->submit($record->fresh('status'), $sto);

        $this->assertSame('submitted', $record->status->code);
        $this->assertNotNull($record->submitted_at);
        $this->assertSame($sto->id, $record->sto_user_id);
    }

    public function test_be_access_is_restricted_to_their_own_rig(): void
    {
        $rigA = Rig::factory()->create();
        $rigB = Rig::factory()->create();

        $be = User::factory()->create(['role' => UserRole::BE->value, 'rig_id' => $rigA->id]);

        $this->assertTrue($be->canAccessRig($rigA->id));
        $this->assertFalse($be->canAccessRig($rigB->id));
    }

    public function test_sto_can_save_a_captioned_image_attachment(): void
    {
        Storage::fake('public');

        [$rig, $sto, $drillType, $eventType] = $this->drillFormContext();

        $this->actingAs($sto);

        Livewire::test(EditorPage::class)
            ->set('rigId', $rig->id)
            ->set('drillTypeId', $drillType->id)
            ->set('eventTypeId', $eventType->id)
            ->call('addNewAttachment')
            ->set('newAttachments.0', UploadedFile::fake()->image('vantris-logo.png')->size(512))
            ->set('newAttachmentCaptions.0', 'Updated Vantris logo')
            ->call('saveDraft')
            ->assertHasNoErrors();

        $attachment = DrillAttachment::query()->firstOrFail();

        $this->assertSame('Updated Vantris logo', $attachment->caption);
        $this->assertSame('vantris-logo.png', $attachment->file_name);
        $this->assertNotNull($attachment->mime_type);
        Storage::disk('public')->assertExists($attachment->file_path);
    }

    public function test_non_image_attachments_are_rejected(): void
    {
        Storage::fake('public');

        [$rig, $sto, $drillType, $eventType] = $this->drillFormContext();

        $this->actingAs($sto);

        Livewire::test(EditorPage::class)
            ->set('rigId', $rig->id)
            ->set('drillTypeId', $drillType->id)
            ->set('eventTypeId', $eventType->id)
            ->call('addNewAttachment')
            ->set('newAttachments.0', UploadedFile::fake()->create('checklist.pdf', 200, 'application/pdf'))
            ->set('newAttachmentCaptions.0', 'Checklist')
            ->call('saveDraft')
            ->assertHasErrors(['newAttachments.0' => 'image']);
    }

    public function test_oversized_image_attachments_are_rejected(): void
    {
        Storage::fake('public');

        [$rig, $sto, $drillType, $eventType] = $this->drillFormContext();

        $this->actingAs($sto);

        Livewire::test(EditorPage::class)
            ->set('rigId', $rig->id)
            ->set('drillTypeId', $drillType->id)
            ->set('eventTypeId', $eventType->id)
            ->call('addNewAttachment')
            ->set('newAttachments.0', UploadedFile::fake()->image('oversized.png')->size(1600))
            ->set('newAttachmentCaptions.0', 'Oversized image')
            ->call('saveDraft')
            ->assertHasErrors(['newAttachments.0' => 'max']);
    }

    private function drillFormContext(): array
    {
        $rig = Rig::factory()->create();
        $sto = User::factory()->create(['role' => UserRole::STO->value, 'rig_id' => $rig->id]);
        $drillType = DrillType::query()->create(['name' => 'Fire Drill', 'is_active' => true]);
        $eventType = EventType::query()->create(['name' => 'Fire', 'is_active' => true]);

        return [$rig, $sto, $drillType, $eventType];
    }
}
