<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Livewire\Admin\SettingsPage;
use App\Models\Rig;
use App\Models\User;
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
}
