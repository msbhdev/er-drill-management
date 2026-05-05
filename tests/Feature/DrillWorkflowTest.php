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
use Illuminate\Auth\Access\AuthorizationException;
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
        $sto = User::factory()->create(['full_name' => 'BER STO', 'role' => UserRole::STO->value, 'rig_id' => $rig->id]);
        User::factory()->create(['full_name' => 'BER BE', 'role' => UserRole::BE->value, 'rig_id' => $rig->id]);
        User::factory()->create(['full_name' => 'BER OIM', 'role' => UserRole::OIM->value, 'rig_id' => $rig->id]);

        $drillType = DrillType::query()->create(['name' => 'Fire Drill', 'is_active' => true]);
        $eventType = EventType::query()->create(['name' => 'Fire', 'is_active' => true]);

        $service = app(DrillWorkflowService::class);

        $record = $service->saveDraft(new DrillRecord(), [
            'rig_id' => $rig->id,
            'drill_type_id' => $drillType->id,
            'event_type_id' => $eventType->id,
            'drill_type_ids' => [$drillType->id],
            'event_type_ids' => [$eventType->id],
            'drill_date' => now()->toDateString(),
        ], $sto);

        $this->assertSame('draft', $record->status->code);

        $record = $service->submit($record->fresh('status'), $sto);

        $this->assertSame('submitted', $record->status->code);
        $this->assertNotNull($record->submitted_at);
        $this->assertSame($sto->id, $record->sto_user_id);
        $this->assertSame($sto->currentAssigneeName(), $record->sto_name);
        $this->assertSame($sto->currentAssigneeName(), $record->workflowHistory()->latest('id')->first()->actor_person_name);
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
            ->set('drillTypeIds', [$drillType->id])
            ->set('eventTypeIds', [$eventType->id])
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
            ->set('drillTypeIds', [$drillType->id])
            ->set('eventTypeIds', [$eventType->id])
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
            ->set('drillTypeIds', [$drillType->id])
            ->set('eventTypeIds', [$eventType->id])
            ->call('addNewAttachment')
            ->set('newAttachments.0', UploadedFile::fake()->image('oversized.png')->size(1600))
            ->set('newAttachmentCaptions.0', 'Oversized image')
            ->call('saveDraft')
            ->assertHasErrors(['newAttachments.0' => 'max']);
    }

    public function test_sto_can_assign_multiple_drill_and_event_types(): void
    {
        $rig = Rig::factory()->create();
        $sto = User::factory()->create(['role' => UserRole::STO->value, 'rig_id' => $rig->id]);
        $drillTypes = collect([
            DrillType::query()->create(['name' => 'Fire Drill', 'is_active' => true]),
            DrillType::query()->create(['name' => 'Abandon Rig', 'is_active' => true]),
        ]);
        $eventTypes = collect([
            EventType::query()->create(['name' => 'Fire', 'is_active' => true]),
            EventType::query()->create(['name' => 'Explosion', 'is_active' => true]),
        ]);

        $this->actingAs($sto);

        Livewire::test(EditorPage::class)
            ->set('rigId', $rig->id)
            ->set('drillTypeIds', $drillTypes->pluck('id')->all())
            ->set('eventTypeIds', $eventTypes->pluck('id')->all())
            ->call('saveDraft')
            ->assertHasNoErrors();

        $record = DrillRecord::query()->with(['drillTypes', 'eventTypes'])->firstOrFail();

        $this->assertSame($drillTypes->first()->id, $record->drill_type_id);
        $this->assertSame($eventTypes->first()->id, $record->event_type_id);
        $this->assertEqualsCanonicalizing($drillTypes->pluck('id')->all(), $record->drillTypes->pluck('id')->all());
        $this->assertEqualsCanonicalizing($eventTypes->pluck('id')->all(), $record->eventTypes->pluck('id')->all());
    }

    public function test_approved_drill_cannot_be_saved_back_to_draft(): void
    {
        $record = $this->approvedDrill();
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin);

        Livewire::test(EditorPage::class, ['drillRecord' => $record->fresh()])
            ->call('saveDraft')
            ->assertForbidden();

        $this->assertSame('approved', $record->fresh('status')->status->code);
    }

    public function test_approved_drill_hides_draft_edit_actions(): void
    {
        $record = $this->approvedDrill();
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin);

        Livewire::test(EditorPage::class, ['drillRecord' => $record->fresh()])
            ->assertDontSeeHtml('wire:click="resetForm"')
            ->assertDontSeeHtml('wire:click="saveDraft"')
            ->assertDontSeeHtml('wire:click="submit"')
            ->assertSeeHtml('wire:click="closeRecord"');
    }

    public function test_workflow_service_rejects_draft_save_after_approval(): void
    {
        $record = $this->approvedDrill();
        $admin = User::factory()->administrator()->create();

        $this->expectException(AuthorizationException::class);

        app(DrillWorkflowService::class)->saveDraft($record->fresh('status'), [
            'rig_id' => $record->rig_id,
            'drill_type_id' => $record->drill_type_id,
            'event_type_id' => $record->event_type_id,
            'drill_type_ids' => $record->drillTypes()->pluck('drill_types.id')->all(),
            'event_type_ids' => $record->eventTypes()->pluck('event_types.id')->all(),
            'drill_date' => $record->drill_date->toDateString(),
        ], $admin);
    }

    public function test_drill_pdf_download_works_for_visible_record(): void
    {
        Storage::fake('public');

        $record = $this->approvedDrill();
        $file = UploadedFile::fake()->image('evidence.png', 320, 180);
        $path = $file->storeAs('drills/'.$record->id, 'evidence.png', 'public');

        DrillAttachment::query()->create([
            'drill_record_id' => $record->id,
            'caption' => 'Muster point evidence',
            'file_path' => $path,
            'file_name' => 'evidence.png',
            'file_size_kb' => 1,
            'mime_type' => 'image/png',
            'created_by_user_id' => $record->created_by_user_id,
        ]);

        $this->actingAs($record->stoUser);

        $this->get(route('drills.print', $record))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_drill_print_view_renders_attachments_and_signoff_labels(): void
    {
        $record = $this->approvedDrill();
        $record->forceFill([
            'applicable_dsha' => 'DSHA-ER-01',
            'performance_standard' => 'All muster teams respond within the expected timeframe.',
            'performance_standards_met' => 'Partial',
            'debrief_attendees' => 'STO, BE, OIM, Fire Team Lead',
            'other_comments' => 'Review communications during the next drill.',
        ])->save();

        $record = $record->fresh([
            'rig',
            'drillType',
            'eventType',
            'drillTypes',
            'eventTypes',
            'status',
            'events',
            'actions.status',
            'attachments',
            'workflowHistory.actor',
            'workflowHistory.fromStatus',
            'workflowHistory.toStatus',
        ]);

        $html = view('reports.drill-print', [
            'record' => $record,
            'logoPath' => public_path('images/vantris-energy-berhad-logo.png'),
            'attachmentsForPdf' => collect([
                [
                    'caption' => 'Muster point evidence',
                    'file_name' => 'evidence.png',
                    'preview_path' => null,
                ],
            ]),
        ])->render();

        $this->assertStringContainsString('Attachments', $html);
        $this->assertStringContainsString('Applicable DSHA', $html);
        $this->assertStringContainsString('DSHA-ER-01', $html);
        $this->assertStringContainsString('Performance Standard', $html);
        $this->assertStringContainsString('All muster teams respond within the expected timeframe.', $html);
        $this->assertStringContainsString('Performance Result', $html);
        $this->assertStringContainsString('Partial', $html);
        $this->assertStringContainsString('Debrief Attendees', $html);
        $this->assertStringContainsString('STO, BE, OIM, Fire Team Lead', $html);
        $this->assertStringContainsString('Other Comments', $html);
        $this->assertStringContainsString('Review communications during the next drill.', $html);
        $this->assertStringContainsString('Attachment 1', $html);
        $this->assertStringContainsString('Muster point evidence', $html);
        $this->assertStringNotContainsString('<strong>Caption:</strong>', $html);
        $this->assertStringContainsString('Prepared By', $html);
        $this->assertStringContainsString('Verified By', $html);
        $this->assertStringContainsString('Approved By', $html);
        $this->assertStringContainsString('This is a system generated document. No signature is required.', $html);
        $this->assertStringNotContainsString('<th>STO</th>', $html);
        $this->assertStringNotContainsString('border-top: 1px solid #111827', $html);
    }

    public function test_attachment_download_works_for_visible_record(): void
    {
        Storage::fake('public');

        $record = $this->approvedDrill();
        Storage::disk('public')->put('drills/'.$record->id.'/evidence.png', 'fake image contents');

        $attachment = DrillAttachment::query()->create([
            'drill_record_id' => $record->id,
            'caption' => 'Evidence',
            'file_path' => 'drills/'.$record->id.'/evidence.png',
            'file_name' => 'evidence.png',
            'file_size_kb' => 1,
            'mime_type' => 'image/png',
            'created_by_user_id' => $record->created_by_user_id,
        ]);

        $this->actingAs($record->stoUser);

        $this->get(route('attachments.show', $attachment))
            ->assertOk()
            ->assertDownload('evidence.png');
    }

    private function drillFormContext(): array
    {
        $rig = Rig::factory()->create();
        $sto = User::factory()->create(['role' => UserRole::STO->value, 'rig_id' => $rig->id]);
        $drillType = DrillType::query()->create(['name' => 'Fire Drill', 'is_active' => true]);
        $eventType = EventType::query()->create(['name' => 'Fire', 'is_active' => true]);

        return [$rig, $sto, $drillType, $eventType];
    }

    private function approvedDrill(): DrillRecord
    {
        $rig = Rig::factory()->create();
        $sto = User::factory()->create(['full_name' => 'ESP STO', 'role' => UserRole::STO->value, 'rig_id' => $rig->id]);
        $be = User::factory()->create(['full_name' => 'ESP BE', 'role' => UserRole::BE->value, 'rig_id' => $rig->id]);
        $oim = User::factory()->create(['full_name' => 'ESP OIM', 'role' => UserRole::OIM->value, 'rig_id' => $rig->id]);
        $drillType = DrillType::query()->create(['name' => 'Fire Drill '.uniqid(), 'is_active' => true]);
        $eventType = EventType::query()->create(['name' => 'Fire '.uniqid(), 'is_active' => true]);
        $service = app(DrillWorkflowService::class);

        $record = $service->saveDraft(new DrillRecord(), [
            'rig_id' => $rig->id,
            'drill_type_id' => $drillType->id,
            'event_type_id' => $eventType->id,
            'drill_type_ids' => [$drillType->id],
            'event_type_ids' => [$eventType->id],
            'drill_date' => now()->toDateString(),
            'scenario' => 'Approved drill workflow test.',
        ], $sto);

        $record = $service->submit($record->fresh('status'), $sto);
        $record = $service->verify($record->fresh('status'), $be, 'Verified.');

        return $service->approve($record->fresh('status'), $oim, 'Approved.');
    }
}
