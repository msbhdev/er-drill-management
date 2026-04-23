<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public $user;
    public string $current_holder_name = '';

    public function mount(): void
    {
        $this->refreshUser();
    }

    public function updateProfileInformation(): void
    {
        $validated = $this->validate([
            'current_holder_name' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $user->syncAssigneeSchedule(
            $validated['current_holder_name'],
            now()->toDateString(),
            null,
            'Updated from the profile page.'
        );
        $user->forceFill(['name_confirmed_at' => now()])->save();

        Auth::setUser($user->fresh()->load('rig'));

        $this->refreshUser();

        $this->dispatch('profile-updated');
    }

    protected function refreshUser(): void
    {
        $this->user = Auth::user()->load('rig');
        $this->current_holder_name = $this->user->currentAssigneeName();
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-semibold text-stone-900">
            Account Information
        </h2>

        <p class="mt-1 text-sm text-stone-600">
            Keep the active onboard person current. The holder name is snapshotted into approvals, reports, and audit history.
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Current holder</div>
                <x-text-input wire:model="current_holder_name" id="current_holder_name" name="current_holder_name" type="text" class="mt-3 block w-full rounded-2xl border-stone-300 bg-white text-lg font-bold text-stone-900" autocomplete="name" />
                <x-input-error :messages="$errors->get('current_holder_name')" class="mt-2" />
            </div>
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Role</div>
                <div class="mt-2 text-lg font-bold text-stone-900">{{ $user->role }}</div>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Account label</div>
                <div class="mt-2 text-lg font-bold text-stone-900">{{ $user->full_name }}</div>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Rig assignment</div>
                <div class="mt-2 text-lg font-bold text-stone-900">{{ $user->rig?->name ?? 'All rigs' }}</div>
                <div class="mt-1 text-sm text-stone-500">{{ $user->preferredTimezone() }}</div>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Email</div>
                <div class="mt-2 text-lg font-bold text-stone-900">{{ $user->email }}</div>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Last login</div>
                <div class="mt-2 text-lg font-bold text-stone-900">{{ $user->formatDateTime($user->last_login_at) ?: 'First sign-in pending' }}</div>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Name confirmed</div>
                <div class="mt-2 text-lg font-bold text-stone-900">{{ $user->formatDateTime($user->name_confirmed_at) ?: 'Confirmation pending' }}</div>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Status</div>
                <div class="mt-2 text-lg font-bold {{ $user->active_status ? 'text-emerald-700' : 'text-rose-700' }}">{{ $user->active_status ? 'Active' : 'Disabled' }}</div>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Update name</div>
                <p class="mt-2 text-sm leading-6 text-stone-600">
                    Confirm the onboard person at least once every 14 days so the latest holder appears correctly in reports.
                </p>

                <div class="mt-4 flex items-center gap-4">
                    <x-primary-button>{{ __('Update') }}</x-primary-button>

                    <x-action-message class="me-3" on="profile-updated">
                        {{ __('Saved.') }}
                    </x-action-message>
                </div>
            </div>
        </div>
    </form>
</section>
