<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-stone-200/80 bg-white/85 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                <x-application-logo class="h-11 w-11" />
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-500">ER Drill</div>
                    <div class="text-sm font-bold text-stone-900">Management</div>
                </div>
            </a>

            <div class="hidden items-center gap-2 md:flex">
                <a href="{{ route('dashboard') }}" wire:navigate class="{{ request()->routeIs('dashboard') ? 'bg-stone-900 text-white' : 'text-stone-700 hover:bg-stone-100' }} rounded-full px-4 py-2 text-sm font-semibold">
                    Dashboard
                </a>
                <a href="{{ route('drills.index') }}" wire:navigate class="{{ request()->routeIs('drills.*') ? 'bg-stone-900 text-white' : 'text-stone-700 hover:bg-stone-100' }} rounded-full px-4 py-2 text-sm font-semibold">
                    Drills
                </a>
                @if (in_array(auth()->user()->role, ['RM', 'Management', 'Administrator'], true))
                    <a href="{{ route('reports.index') }}" wire:navigate class="{{ request()->routeIs('reports.*') ? 'bg-stone-900 text-white' : 'text-stone-700 hover:bg-stone-100' }} rounded-full px-4 py-2 text-sm font-semibold">
                        Reports
                    </a>
                @endif
                @if (auth()->user()->role === 'Administrator')
                    <a href="{{ route('admin.settings') }}" wire:navigate class="{{ request()->routeIs('admin.*') ? 'bg-stone-900 text-white' : 'text-stone-700 hover:bg-stone-100' }} rounded-full px-4 py-2 text-sm font-semibold">
                        Admin
                    </a>
                @endif
            </div>
        </div>

        <div class="hidden items-center gap-3 md:flex">
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-2 text-right">
                <div class="text-sm font-bold text-stone-900">{{ auth()->user()->full_name }}</div>
                <div class="text-xs uppercase tracking-[0.24em] text-stone-500">
                    {{ auth()->user()->role }} @if (auth()->user()->rig) · {{ auth()->user()->rig->code }} @endif
                </div>
            </div>

            <a href="{{ route('profile') }}" wire:navigate class="rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-stone-900/10">
                Profile
            </a>

            <button wire:click="logout" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-900 hover:text-stone-900">
                Log Out
            </button>
        </div>

        <button @click="open = ! open" class="inline-flex items-center justify-center rounded-full border border-stone-300 p-2 text-stone-700 md:hidden">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-stone-200 bg-white md:hidden">
        <div class="space-y-2 px-4 py-4">
            <a href="{{ route('dashboard') }}" wire:navigate class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-800' }}">
                Dashboard
            </a>
            <a href="{{ route('drills.index') }}" wire:navigate class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('drills.*') ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-800' }}">
                Drills
            </a>
            @if (in_array(auth()->user()->role, ['RM', 'Management', 'Administrator'], true))
                <a href="{{ route('reports.index') }}" wire:navigate class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('reports.*') ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-800' }}">
                    Reports
                </a>
            @endif
            @if (auth()->user()->role === 'Administrator')
                <a href="{{ route('admin.settings') }}" wire:navigate class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.*') ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-800' }}">
                    Admin
                </a>
            @endif
            <a href="{{ route('profile') }}" wire:navigate class="block rounded-2xl bg-stone-100 px-4 py-3 text-sm font-semibold text-stone-800">
                Profile
            </a>
            <button wire:click="logout" class="block w-full rounded-2xl border border-stone-300 px-4 py-3 text-left text-sm font-semibold text-stone-700">
                Log Out
            </button>
        </div>
    </div>
</nav>
