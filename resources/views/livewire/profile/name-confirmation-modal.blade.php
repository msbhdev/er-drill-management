<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_holder_name = '';
    public bool $showModal = false;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $this->current_holder_name = $user->currentAssigneeName();
        $this->showModal = $user->needsNameConfirmation();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'current_holder_name' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $user->syncAssigneeSchedule(
            $validated['current_holder_name'],
            now()->toDateString(),
            null,
            'Updated from the confirmation modal.'
        );
        $user->forceFill(['name_confirmed_at' => now()])->save();

        Auth::setUser($user->fresh()->load('rig'));

        $this->current_holder_name = Auth::user()->currentAssigneeName();
        $this->showModal = false;

        $this->dispatch('profile-updated');
    }
}; ?>

<div>
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-6 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-[2rem] border border-white/70 bg-white p-6 shadow-2xl shadow-stone-950/20 sm:p-8">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-700">Name confirmation required</div>
                    <h2 class="mt-3 text-2xl font-extrabold text-stone-900">Please confirm the current onboard person for this account</h2>
                    <p class="mt-3 text-sm leading-6 text-stone-600">
                        The onboard person name is inserted into drill reports and approval records. Please review and update it every 14 days.
                    </p>
                </div>

                <form wire:submit="save" class="mt-6 space-y-5">
                    <div>
                        <x-input-label for="modal_current_holder_name" :value="__('Current Holder')" />
                        <x-text-input wire:model="current_holder_name" id="modal_current_holder_name" name="modal_current_holder_name" type="text" class="mt-2 block w-full rounded-2xl border-stone-300 bg-stone-50" autocomplete="name" autofocus />
                        <x-input-error :messages="$errors->get('current_holder_name')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <a href="{{ route('profile') }}" wire:navigate class="text-sm font-semibold text-stone-500 transition hover:text-stone-900">
                            Review profile details
                        </a>

                        <x-primary-button class="justify-center">
                            {{ __('Confirm Holder') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
