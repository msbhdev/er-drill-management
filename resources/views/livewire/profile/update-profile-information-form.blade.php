<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public $user;

    public function mount(): void
    {
        $this->user = Auth::user()->load('rig');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-semibold text-stone-900">
            Account Information
        </h2>

        <p class="mt-1 text-sm text-stone-600">
            Shared role accounts are managed by the administrator. This page shows your assigned role, rig, and account status.
        </p>
    </header>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Current holder</div>
            <div class="mt-2 text-lg font-bold text-stone-900">{{ $user->full_name }}</div>
        </div>
        <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Role</div>
            <div class="mt-2 text-lg font-bold text-stone-900">{{ $user->role }}</div>
        </div>
        <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Rig assignment</div>
            <div class="mt-2 text-lg font-bold text-stone-900">{{ $user->rig?->name ?? 'All rigs' }}</div>
        </div>
        <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Email</div>
            <div class="mt-2 text-lg font-bold text-stone-900">{{ $user->email }}</div>
        </div>
        <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Last login</div>
            <div class="mt-2 text-lg font-bold text-stone-900">{{ optional($user->last_login_at)->format('d M Y H:i') ?: 'First sign-in pending' }}</div>
        </div>
        <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Status</div>
            <div class="mt-2 text-lg font-bold {{ $user->active_status ? 'text-emerald-700' : 'text-rose-700' }}">{{ $user->active_status ? 'Active' : 'Disabled' }}</div>
        </div>
    </div>
</section>
