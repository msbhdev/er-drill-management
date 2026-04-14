<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response
            ->assertOk()
            ->assertSeeVolt('profile.update-profile-information-form')
            ->assertSeeVolt('profile.update-password-form');
    }

    public function test_users_can_update_their_full_name_from_the_profile_page(): void
    {
        $now = now()->startOfSecond();
        $this->travelTo($now);

        $user = User::factory()->create([
            'full_name' => 'Original Holder',
            'name_confirmed_at' => $now->copy()->subDays(21),
        ]);

        $this->actingAs($user);

        $component = Volt::test('profile.update-profile-information-form')
            ->set('full_name', 'Updated Holder')
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect()
            ->assertDispatched('profile-updated');

        $user->refresh();

        $this->assertSame('Updated Holder', $user->full_name);
        $this->assertTrue($user->name_confirmed_at->eq($now));

        $this->travelBack();
    }

    public function test_name_confirmation_modal_opens_after_14_days(): void
    {
        $user = User::factory()->create([
            'name_confirmed_at' => now()->subDays(14),
        ]);

        $this->actingAs($user);

        Volt::test('profile.name-confirmation-modal')
            ->assertSet('showModal', true);
    }

    public function test_name_confirmation_modal_stays_hidden_with_recent_confirmation(): void
    {
        $user = User::factory()->create([
            'name_confirmed_at' => now()->subDays(13),
        ]);

        $this->actingAs($user);

        Volt::test('profile.name-confirmation-modal')
            ->assertSet('showModal', false);
    }

    public function test_name_confirmation_modal_can_update_the_full_name(): void
    {
        $now = now()->startOfSecond();
        $this->travelTo($now);

        $user = User::factory()->create([
            'full_name' => 'Old Name',
            'name_confirmed_at' => $now->copy()->subDays(30),
        ]);

        $this->actingAs($user);

        $component = Volt::test('profile.name-confirmation-modal')
            ->set('full_name', 'Current Name')
            ->call('save');

        $component
            ->assertHasNoErrors()
            ->assertSet('showModal', false)
            ->assertDispatched('profile-updated');

        $user->refresh();

        $this->assertSame('Current Name', $user->full_name);
        $this->assertTrue($user->name_confirmed_at->eq($now));

        $this->travelBack();
    }

    public function test_profile_page_displays_times_in_the_rig_timezone(): void
    {
        $user = User::factory()->create([
            'last_login_at' => '2026-04-14 02:52:00',
            'name_confirmed_at' => '2026-04-14 02:28:00',
        ]);

        $user->rig->update(['timezone' => 'Asia/Bangkok']);

        $response = $this->actingAs($user)->get('/profile');

        $response
            ->assertOk()
            ->assertSee('14 Apr 2026 09:52')
            ->assertSee('14 Apr 2026 09:28')
            ->assertSee('Asia/Bangkok');
    }
}
