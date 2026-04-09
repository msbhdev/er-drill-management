<div class="space-y-6">
    <section class="flex flex-col gap-4 rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Drill Workspace</div>
            <h1 class="mt-2 text-3xl font-extrabold text-stone-950">Drill Queue</h1>
            <p class="mt-2 text-sm text-stone-600">Track drill records by rig, workflow stage, and drill attributes.</p>
        </div>

        @if ($canCreate)
            <a href="{{ route('drills.create') }}" wire:navigate class="rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white shadow-xl shadow-stone-900/15">
                New Drill Record
            </a>
        @endif
    </section>

    <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Search</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Reference, location, or scenario" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Status</label>
                <select wire:model.live="statusId" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Rig</label>
                <select wire:model.live="rigId" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled($rigs->count() === 1)>
                    <option value="">All rigs</option>
                    @foreach ($rigs as $rig)
                        <option value="{{ $rig->id }}">{{ $rig->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Drill Type</label>
                <select wire:model.live="drillTypeId" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
                    <option value="">All drill types</option>
                    @foreach ($drillTypes as $drillType)
                        <option value="{{ $drillType->id }}">{{ $drillType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Event Type</label>
                <select wire:model.live="eventTypeId" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
                    <option value="">All event types</option>
                    @foreach ($eventTypes as $eventType)
                        <option value="{{ $eventType->id }}">{{ $eventType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Date From</label>
                <input wire:model.live="dateFrom" type="date" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Date To</label>
                <input wire:model.live="dateTo" type="date" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
            </div>
        </div>
    </section>

    <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead>
                    <tr class="text-left text-stone-500">
                        <th class="pb-3 font-semibold">Reference</th>
                        <th class="pb-3 font-semibold">Rig</th>
                        <th class="pb-3 font-semibold">Drill / Event Type</th>
                        <th class="pb-3 font-semibold">Date</th>
                        <th class="pb-3 font-semibold">Status</th>
                        <th class="pb-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($drills as $drill)
                        <tr class="text-stone-700">
                            <td class="py-4 font-semibold text-stone-900">{{ $drill->reference_no }}</td>
                            <td class="py-4">{{ $drill->rig->name }}</td>
                            <td class="py-4">
                                <div class="font-semibold">{{ $drill->drillType->name }}</div>
                                <div class="text-xs text-stone-500">{{ $drill->eventType->name }}</div>
                            </td>
                            <td class="py-4">{{ $drill->drill_date?->format('d M Y') }}</td>
                            <td class="py-4">
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-700">{{ $drill->status->name }}</span>
                            </td>
                            <td class="py-4 text-right">
                                <a href="{{ route('drills.show', $drill) }}" wire:navigate class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-stone-500">No drill records match the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $drills->links() }}
        </div>
    </section>
</div>
