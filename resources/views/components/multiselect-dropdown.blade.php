@props([
    'options' => [],
    'wireModel',
    'placeholder' => 'Select options',
    'searchPlaceholder' => 'Search',
    'disabled' => false,
])

<div
    x-data="{
        open: false,
        selected: @entangle($wireModel),
        options: @js(array_values($options)),
        search: '',
        ids() { return (this.selected ?? []).map((v) => Number(v)); },
        isChecked(id) { return this.ids().includes(Number(id)); },
        toggle(id) {
            const numericId = Number(id);
            const next = this.ids();
            const idx = next.indexOf(numericId);
            if (idx === -1) { next.push(numericId); } else { next.splice(idx, 1); }
            this.selected = next;
        },
        clearAll() { this.selected = []; this.search = ''; },
        get filtered() {
            const q = this.search.trim().toLowerCase();
            return q ? this.options.filter((o) => o.name.toLowerCase().includes(q)) : this.options;
        },
        get summary() {
            const ids = this.ids();
            const names = this.options.filter((o) => ids.includes(Number(o.id))).map((o) => o.name);
            if (names.length === 0) return null;
            if (names.length <= 2) return names.join(', ');
            return names.slice(0, 2).join(', ') + ' +' + (names.length - 2) + ' more';
        },
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
    class="relative mt-2"
>
    <button
        type="button"
        @click="open = !open"
        @disabled($disabled)
        class="flex w-full items-center justify-between gap-2 rounded-2xl border border-stone-300 bg-stone-50 px-3 py-2 text-left text-sm text-stone-700 disabled:cursor-not-allowed disabled:opacity-60"
    >
        <span class="flex-1 truncate">
            <template x-if="summary"><span x-text="summary" class="text-stone-900"></span></template>
            <template x-if="!summary"><span class="text-stone-400">{{ $placeholder }}</span></template>
        </span>
        <span class="flex shrink-0 items-center gap-2">
            <template x-if="ids().length">
                <span class="rounded-full bg-stone-900 px-2 py-0.5 text-xs font-semibold text-white" x-text="ids().length"></span>
            </template>
            <svg class="h-4 w-4 text-stone-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </span>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        class="absolute left-0 right-0 z-30 mt-2 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-xl shadow-stone-900/10"
    >
        <div class="border-b border-stone-100 bg-stone-50 p-2">
            <input
                x-model="search"
                type="text"
                placeholder="{{ $searchPlaceholder }}"
                class="w-full rounded-xl border-stone-200 bg-white text-sm"
            >
        </div>
        <ul class="max-h-56 overflow-y-auto py-1">
            <template x-for="opt in filtered" :key="opt.id">
                <li>
                    <label class="flex cursor-pointer items-center gap-3 px-3 py-2 text-sm text-stone-700 hover:bg-stone-50">
                        <input
                            type="checkbox"
                            :checked="isChecked(opt.id)"
                            @change="toggle(opt.id)"
                            class="rounded border-stone-300 text-teal-700 focus:ring-teal-700"
                        >
                        <span x-text="opt.name"></span>
                    </label>
                </li>
            </template>
            <template x-if="filtered.length === 0">
                <li class="px-3 py-3 text-sm text-stone-400">No matches</li>
            </template>
        </ul>
        <div class="flex items-center justify-between border-t border-stone-100 bg-stone-50 px-3 py-2 text-xs text-stone-500">
            <span><span x-text="ids().length"></span> selected</span>
            <button type="button" @click="clearAll()" class="font-semibold text-stone-700 hover:text-stone-900">Clear</button>
        </div>
    </div>
</div>
