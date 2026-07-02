<?php

use Livewire\Volt\Component;

new class extends Component
{
    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function openNotification(string $id): void
    {
        $notification = auth()->user()->notifications()->whereKey($id)->first();

        if (! $notification) {
            return;
        }

        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        if ($url) {
            $this->redirect($url, navigate: true);
        }
    }

    public function with(): array
    {
        $user = auth()->user();

        return [
            'unreadCount' => $user->unreadNotifications()->count(),
            'items' => $user->notifications()->latest()->limit(10)->get(),
        ];
    }
}; ?>

<div wire:poll.30s x-data="{ open: false }" style="position: relative;">
    <button type="button" @click="open = ! open" aria-label="Notifications"
            style="position: relative; display: inline-flex; align-items: center; justify-content: center; height: 2.75rem; width: 2.75rem; border-radius: 9999px; border: 1px solid rgba(43,45,138,0.2); background: white; color: #2B2D8A; cursor: pointer; transition: all 0.15s;"
            onmouseover="this.style.borderColor='#2B2D8A'; this.style.background='rgba(43,45,138,0.06)';"
            onmouseout="this.style.borderColor='rgba(43,45,138,0.2)'; this.style.background='white';">
        <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if ($unreadCount > 0)
            <span style="position: absolute; top: -0.25rem; right: -0.25rem; min-width: 1.25rem; height: 1.25rem; padding: 0 0.3rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 9999px; background: #e11d48; color: white; font-size: 0.6875rem; font-weight: 700; box-shadow: 0 2px 6px rgba(225,29,72,0.4);">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition.opacity @click.outside="open = false" @keydown.escape.window="open = false"
         style="position: absolute; right: 0; margin-top: 0.75rem; width: 22rem; max-width: calc(100vw - 2rem); z-index: 50; border-radius: 1rem; border: 1px solid rgba(43,45,138,0.12); background: white; box-shadow: 0 20px 50px -20px rgba(43,45,138,0.35); overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(43,45,138,0.08); background: #F7F8FC;">
            <span style="font-size: 0.8125rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #2B2D8A;">Notifications</span>
            @if ($unreadCount > 0)
                <button wire:click="markAllRead" type="button"
                        style="font-size: 0.75rem; font-weight: 600; color: #2B2D8A; background: none; border: none; cursor: pointer;">
                    Mark all read
                </button>
            @endif
        </div>

        <div style="max-height: 24rem; overflow-y: auto;">
            @forelse ($items as $item)
                <button wire:key="notif-{{ $item->id }}" wire:click="openNotification('{{ $item->id }}')" type="button"
                        style="display: block; width: 100%; text-align: left; padding: 0.875rem 1rem; border: none; border-bottom: 1px solid rgba(43,45,138,0.06); cursor: pointer; background: {{ $item->read_at ? 'white' : 'rgba(0,163,200,0.06)' }};">
                    <div style="display: flex; align-items: flex-start; gap: 0.625rem;">
                        @unless ($item->read_at)
                            <span style="margin-top: 0.4rem; flex-shrink: 0; width: 0.5rem; height: 0.5rem; border-radius: 9999px; background: #00A3C8;"></span>
                        @endunless
                        <div style="min-width: 0; flex: 1;">
                            <div style="font-size: 0.875rem; font-weight: 700; color: #1A1C5E;">{{ $item->data['title'] ?? 'Notification' }}</div>
                            <div style="margin-top: 0.125rem; font-size: 0.8125rem; line-height: 1.45; color: #475569;">{{ $item->data['message'] ?? '' }}</div>
                            <div style="margin-top: 0.375rem; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.12em; color: #94a3b8;">{{ $item->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </button>
            @empty
                <div style="padding: 2rem 1rem; text-align: center; font-size: 0.875rem; color: #94a3b8;">
                    No notifications yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
