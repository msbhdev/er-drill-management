<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-stone-900">Access your assigned rig account</h2>
        <p class="mt-2 text-sm leading-6 text-stone-600">
            Use the shared role email issued by your administrator. STO, BE, OIM, RM, Management, and Administrator accounts are all managed here.
        </p>
    </div>

    <form wire:submit="login" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="mt-2 block w-full rounded-2xl border-stone-300 bg-stone-50" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="mt-2 block w-full rounded-2xl border-stone-300 bg-stone-50"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-stone-300 text-teal-700 shadow-sm focus:ring-teal-700" name="remember">
                <span class="ms-2 text-sm text-stone-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-teal-700 hover:text-teal-800" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="w-full justify-center rounded-2xl bg-stone-900 px-5 py-3 text-sm font-semibold text-white shadow-xl shadow-stone-900/20 hover:bg-stone-800">
                {{ __('Log in') }}
        </x-primary-button>

        <a href="{{ url('/') }}" wire:navigate class="block w-full rounded-2xl border border-stone-300 px-5 py-3 text-center text-sm font-semibold text-stone-700 transition hover:border-stone-900 hover:text-stone-900">
            Cancel
        </a>
    </form>
</div>
