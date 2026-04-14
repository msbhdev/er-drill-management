<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Livewire\Admin\SettingsPage;
use App\Models\DrillRecord;
use App\Models\DrillType;
use App\Models\EventType;
use App\Models\Rig;
use App\Models\User;
use App\Services\DrillWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_a_rig_with_a_timezone(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Administrator->value,
            'rig_id' => null,
        ]);

        $this->actingAs($admin);

        Livewire::test(SettingsPage::class)
            ->set('rigName', 'Thailand Rig')
            ->set('rigCode', 'THR')
            ->set('rigLocation', 'Gulf of Thailand')
            ->set('rigTimezone', 'Asia/Bangkok')
            ->call('saveRig')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('rigs', [
            'name' => 'Thailand Rig',
            'code' => 'THR',
            'timezone' => 'Asia/Bangkok',
        ]);
    }

    public function test_administrator_can_update_a_rig_timezone(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Administrator->value,
            'rig_id' => null,
        ]);

        $rig = Rig::factory()->create([
            'timezone' => 'Asia/Kuala_Lumpur',
            'is_active' => false,
        ]);

        $this->actingAs($admin);

        Livewire::test(SettingsPage::class)
            ->call('editRig', $rig->id)
            ->set('rigTimezone', 'Asia/Bangkok')
            ->call('saveRig')
            ->assertHasNoErrors();

        $rig->refresh();

        $this->assertSame('Asia/Bangkok', $rig->timezone);
        $this->assertFalse($rig->is_active);
    }

    public function test_user_accounts_list_is_paginated_to_five_records(): void
    {
        $admin = User::factory()->create([
            'full_name' => 'Admin User',
            'role' => UserRole::Administrator->value,
            'rig_id' => null,
        ]);

        foreach (range(1, 6) as $index) {
            User::factory()->create([
                'full_name' => sprintf('User %02d', $index),
                'email' => sprintf('user%02d@example.com', $index),
                'role' => UserRole::STO->value,
            ]);
        }

        $this->actingAs($admin);

        Livewire::test(SettingsPage::class)
            ->assertSee('Admin User')
            ->assertSee('User 01')
            ->assertSee('User 04')
            ->assertDontSee('User 05')
            ->assertDontSee('User 06');
    }

    public function test_administrator_can_edit_and_delete_a_drill_type(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Administrator->value,
            'rig_id' => null,
        ]);

        $drillType = DrillType::query()->create([
            'name' => 'Man Overboard',
            'description' => 'Original description',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(SettingsPage::class)
            ->call('editDrillType', $drillType->id)
            ->set('drillTypeName', 'Man Overboard Updated')
            ->set('drillTypeDescription', 'Updated description')
            ->call('saveDrillType')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('drill_types', [
            'id' => $drillType->id,
            'name' => 'Man Overboard Updated',
            'description' => 'Updated description',
        ]);

        Livewire::test(SettingsPage::class)
            ->call('deleteDrillType', $drillType->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('drill_types', [
            'id' => $drillType->id,
        ]);
    }

    public function test_administrator_cannot_delete_a_drill_type_that_is_in_use(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Administrator->value,
            'rig_id' => null,
        ]);

        $rig = Rig::factory()->create();
        $drillType = DrillType::query()->create([
            'name' => 'Fire Drill',
            'is_active' => true,
        ]);
        $eventType = EventType::query()->create([
            'name' => 'Fire',
            'is_active' => true,
        ]);
        $sto = User::factory()->create([
            'role' => UserRole::STO->value,
            'rig_id' => $rig->id,
        ]);

        app(DrillWorkflowService::class)->saveDraft(new DrillRecord(), [
            'rig_id' => $rig->id,
            'drill_type_id' => $drillType->id,
            'event_type_id' => $eventType->id,
            'drill_type_ids' => [$drillType->id],
            'event_type_ids' => [$eventType->id],
            'drill_date' => now()->toDateString(),
        ], $sto);

        $this->actingAs($admin);

        Livewire::test(SettingsPage::class)
            ->call('deleteDrillType', $drillType->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('drill_types', [
            'id' => $drillType->id,
        ]);
    }

    public function test_administrator_can_edit_and_delete_an_event_type(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Administrator->value,
            'rig_id' => null,
        ]);

        $eventType = EventType::query()->create([
            'name' => 'Gas Release',
            'description' => 'Original description',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(SettingsPage::class)
            ->call('editEventType', $eventType->id)
            ->set('eventTypeName', 'Gas Release Updated')
            ->set('eventTypeDescription', 'Updated description')
            ->call('saveEventType')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('event_types', [
            'id' => $eventType->id,
            'name' => 'Gas Release Updated',
            'description' => 'Updated description',
        ]);

        Livewire::test(SettingsPage::class)
            ->call('deleteEventType', $eventType->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('event_types', [
            'id' => $eventType->id,
        ]);
    }

    public function test_administrator_cannot_delete_an_event_type_that_is_in_use(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Administrator->value,
            'rig_id' => null,
        ]);

        $rig = Rig::factory()->create();
        $drillType = DrillType::query()->create([
            'name' => 'Fire Drill',
            'is_active' => true,
        ]);
        $eventType = EventType::query()->create([
            'name' => 'Fire',
            'is_active' => true,
        ]);
        $sto = User::factory()->create([
            'role' => UserRole::STO->value,
            'rig_id' => $rig->id,
        ]);

        app(DrillWorkflowService::class)->saveDraft(new DrillRecord(), [
            'rig_id' => $rig->id,
            'drill_type_id' => $drillType->id,
            'event_type_id' => $eventType->id,
            'drill_type_ids' => [$drillType->id],
            'event_type_ids' => [$eventType->id],
            'drill_date' => now()->toDateString(),
        ], $sto);

        $this->actingAs($admin);

        Livewire::test(SettingsPage::class)
            ->call('deleteEventType', $eventType->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('event_types', [
            'id' => $eventType->id,
        ]);
    }
}
