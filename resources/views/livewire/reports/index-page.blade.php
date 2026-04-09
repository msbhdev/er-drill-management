@php
    $query = array_filter([
        'search' => $search,
        'status_id' => $statusId,
        'rig_id' => $rigId,
        'drill_type_id' => $drillTypeId,
        'event_type_id' => $eventTypeId,
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
    ], fn ($value) => filled($value));
@endphp

<div class="space-y-6">
    <section class="flex flex-col gap-4 rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Reporting</div>
            <h1 class="mt-2 text-3xl font-extrabold text-stone-950">Drill Reports</h1>
            <p class="mt-2 text-sm text-stone-600">Filter the drill register, track returned approvals, and export the current report view.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('reports.export.excel', $query) }}" class="rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white shadow-xl shadow-stone-900/15">
                Export Excel
            </a>
            <a href="{{ route('reports.export.pdf', $query) }}" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">
                Export PDF
            </a>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-lg shadow-stone-900/5">
            <div class="text-sm font-semibold text-stone-500">Total records</div>
            <div class="mt-3 text-4xl font-extrabold text-stone-950">{{ $summary['total'] }}</div>
        </div>
        <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-lg shadow-stone-900/5">
            <div class="text-sm font-semibold text-stone-500">Approved</div>
            <div class="mt-3 text-4xl font-extrabold text-emerald-700">{{ $summary['approved'] }}</div>
        </div>
        <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-lg shadow-stone-900/5">
            <div class="text-sm font-semibold text-stone-500">Returned</div>
            <div class="mt-3 text-4xl font-extrabold text-amber-700">{{ $summary['returned'] }}</div>
        </div>
        <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-lg shadow-stone-900/5">
            <div class="text-sm font-semibold text-stone-500">Pending approval</div>
            <div class="mt-3 text-4xl font-extrabold text-teal-700">{{ $summary['pending'] }}</div>
        </div>
    </section>

    <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Search</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Reference, location, scenario" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
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
                        <th class="pb-3 font-semibold">Type</th>
                        <th class="pb-3 font-semibold">Date</th>
                        <th class="pb-3 font-semibold">Status</th>
                        <th class="pb-3 font-semibold">Performance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($records as $record)
                        <tr class="text-stone-700">
                            <td class="py-4 font-semibold text-stone-900">{{ $record->reference_no }}</td>
                            <td class="py-4">{{ $record->rig->name }}</td>
                            <td class="py-4">
                                <div class="font-semibold">{{ $record->drillType->name }}</div>
                                <div class="text-xs text-stone-500">{{ $record->eventType->name }}</div>
                            </td>
                            <td class="py-4">{{ $record->drill_date?->format('d M Y') }}</td>
                            <td class="py-4">
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-700">{{ $record->status->name }}</span>
                            </td>
                            <td class="py-4">{{ $record->performance_standards_met ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-stone-500">No report data matches the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $records->links() }}
        </div>
    </section>
</div>
