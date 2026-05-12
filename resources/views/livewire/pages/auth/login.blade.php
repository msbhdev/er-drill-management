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

    <form wire:submit="login" style="display: flex; flex-direction: column; gap: 1.25rem;">

        {{-- Email --}}
        <div>
            <label for="email" style="display: block; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: #2B2D8A; margin-bottom: 0.5rem;">Email</label>
            <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username"
                   style="display: block; width: 100%; padding: 0.75rem 1rem; border: 1px solid rgba(43,45,138,0.18); border-radius: 0.75rem; background: #F7F8FC; font-size: 0.9375rem; color: #1A1C5E; box-sizing: border-box; transition: border-color 0.15s, background 0.15s;"
                   onfocus="this.style.borderColor='#2B2D8A'; this.style.background='white';"
                   onblur="this.style.borderColor='rgba(43,45,138,0.18)'; this.style.background='#F7F8FC';" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div>
            <label for="password" style="display: block; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: #2B2D8A; margin-bottom: 0.5rem;">Password</label>
            <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password"
                   style="display: block; width: 100%; padding: 0.75rem 1rem; border: 1px solid rgba(43,45,138,0.18); border-radius: 0.75rem; background: #F7F8FC; font-size: 0.9375rem; color: #1A1C5E; box-sizing: border-box; transition: border-color 0.15s, background 0.15s;"
                   onfocus="this.style.borderColor='#2B2D8A'; this.style.background='white';"
                   onblur="this.style.borderColor='rgba(43,45,138,0.18)'; this.style.background='#F7F8FC';" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        {{-- Remember me + forgot password --}}
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <label for="remember" style="display: inline-flex; align-items: center; cursor: pointer;">
                <input wire:model="form.remember" id="remember" type="checkbox" name="remember"
                       style="width: 1rem; height: 1rem; border-radius: 0.25rem; border: 1px solid rgba(43,45,138,0.3); accent-color: #2B2D8A;" />
                <span style="margin-left: 0.5rem; font-size: 0.875rem; color: #475569;">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate
                   style="font-size: 0.875rem; font-weight: 600; color: #2B2D8A; text-decoration: none;">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        {{-- Submit button --}}
        <button type="submit"
                style="width: 100%; margin-top: 0.5rem; padding: 0.875rem 1.5rem; border: none; border-radius: 0.75rem; background: #2B2D8A; color: white; font-size: 0.9375rem; font-weight: 700; letter-spacing: 0.02em; cursor: pointer; box-shadow: 0 6px 20px rgba(43,45,138,0.28); transition: opacity 0.15s;"
                onmouseover="this.style.opacity='0.9';"
                onmouseout="this.style.opacity='1';">
            {{ __('Log in') }}
        </button>
    </form>
</div>
