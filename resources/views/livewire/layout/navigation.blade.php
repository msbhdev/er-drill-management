<?php

use App\Livewire\Actions\Logout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    #[On('profile-updated')]
    public function refreshUserDetails(): void
    {
        // Re-render the navigation after a profile name update.
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
    $navItems = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'pattern' => 'dashboard', 'show' => true],
        ['route' => 'drills.index', 'label' => 'Drills', 'pattern' => 'drills.*', 'show' => true],
        ['route' => 'reports.index', 'label' => 'Reports', 'pattern' => 'reports.*', 'show' => in_array(auth()->user()->role, ['RM', 'Management', 'Administrator'], true)],
        ['route' => 'admin.settings', 'label' => 'Admin', 'pattern' => 'admin.*', 'show' => auth()->user()->role === 'Administrator'],
    ];
@endphp

<nav x-data="{ open: false }" style="position: sticky; top: 0; z-index: 40; border-bottom: 1px solid rgba(43,45,138,0.08); background: rgba(255,255,255,0.85); backdrop-filter: blur(10px);">
    <style>
        .nav-desktop { display: flex; align-items: center; gap: 0.5rem; }
        .nav-desktop-actions { display: flex; align-items: center; gap: 0.75rem; }
        .nav-hamburger { display: none; align-items: center; justify-content: center; border-radius: 9999px; border: 1px solid rgba(43,45,138,0.2); padding: 0.5rem; color: #2B2D8A; background: white; cursor: pointer; }
        .nav-mobile-drawer { display: none; border-top: 1px solid rgba(43,45,138,0.08); background: white; }
        .nav-mobile-drawer.is-open { display: block; }
        @media (max-width: 767px) {
            .nav-desktop, .nav-desktop-actions { display: none !important; }
            .nav-hamburger { display: inline-flex !important; }
        }
    </style>
    <div style="max-width: 80rem; margin: 0 auto; padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <a href="{{ route('dashboard') }}" wire:navigate style="display: flex; align-items: center; gap: 1rem; text-decoration: none;">
                <x-application-logo class="h-10 w-auto max-w-[10rem]" />
                <div style="padding-left: 1rem; border-left: 1px solid rgba(43,45,138,0.15);">
                    <div style="font-size: 10px; font-weight: 700; letter-spacing: 0.28em; text-transform: uppercase; color: #2B2D8A;">ER Drill</div>
                    <div style="font-size: 0.875rem; font-weight: 700; color: #1A1C5E;">Management</div>
                </div>
            </a>

            <div class="nav-desktop">
                @foreach ($navItems as $item)
                    @if ($item['show'])
                        @php $active = request()->routeIs($item['pattern']); @endphp
                        <a href="{{ route($item['route']) }}" wire:navigate
                           style="border-radius: 9999px; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; transition: all 0.15s; {{ $active ? 'background: #2B2D8A; color: white; box-shadow: 0 4px 14px rgba(43,45,138,0.25);' : 'color: #475569; background: transparent;' }}"
                           @if (! $active)
                               onmouseover="this.style.background='rgba(43,45,138,0.06)'; this.style.color='#2B2D8A';"
                               onmouseout="this.style.background='transparent'; this.style.color='#475569';"
                           @endif >
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="nav-desktop-actions">
            <livewire:layout.notifications-dropdown />

            <div style="border-radius: 1rem; border: 1px solid rgba(43,45,138,0.1); background: #F7F8FC; padding: 0.5rem 1rem; text-align: right;">
                <div style="font-size: 0.875rem; font-weight: 700; color: #1A1C5E;">{{ auth()->user()->full_name }}</div>
                <div style="font-size: 0.6875rem; letter-spacing: 0.2em; text-transform: uppercase; color: #2B2D8A; font-weight: 600;">
                    {{ auth()->user()->role }} @if (auth()->user()->rig) · {{ auth()->user()->rig->code }} @endif
                </div>
            </div>

            <a href="{{ route('profile') }}" wire:navigate
               style="border-radius: 9999px; background: #2B2D8A; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; color: white; text-decoration: none; box-shadow: 0 4px 14px rgba(43,45,138,0.25);">
                Profile
            </a>

            <button wire:click="logout"
                    style="border-radius: 9999px; border: 1px solid rgba(43,45,138,0.2); background: white; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; color: #475569; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.borderColor='#2B2D8A'; this.style.color='#2B2D8A';"
                    onmouseout="this.style.borderColor='rgba(43,45,138,0.2)'; this.style.color='#475569';">
                Log Out
            </button>
        </div>

        <button @click="open = ! open" class="nav-hamburger">
            <svg style="width: 1.5rem; height: 1.5rem;" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div :class="{ 'is-open': open }" class="nav-mobile-drawer">
        <div style="padding: 1rem 1.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach ($navItems as $item)
                @if ($item['show'])
                    @php $active = request()->routeIs($item['pattern']); @endphp
                    <a href="{{ route($item['route']) }}" wire:navigate
                       style="display: block; border-radius: 0.875rem; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; {{ $active ? 'background: #2B2D8A; color: white;' : 'background: #F7F8FC; color: #1A1C5E;' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
            <a href="{{ route('profile') }}" wire:navigate style="display: block; border-radius: 0.875rem; background: #F7F8FC; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; color: #1A1C5E; text-decoration: none;">
                Profile
            </a>
            <button wire:click="logout" style="display: block; width: 100%; border-radius: 0.875rem; border: 1px solid rgba(43,45,138,0.2); background: white; padding: 0.75rem 1rem; text-align: left; font-size: 0.875rem; font-weight: 600; color: #475569; cursor: pointer;">
                Log Out
            </button>
        </div>
    </div>
</nav>
