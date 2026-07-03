<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

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
                                <div class="font-semibold">{{ $drill->drillTypeNames() }}</div>
                                <div class="text-xs text-stone-500">{{ $drill->eventTypeNames() }}</div>
                            </td>
                            <td class="py-4">{{ $drill->drill_date?->format('d M Y') }}</td>
                            <td class="py-4">
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-700">{{ $drill->status->name }}</span>
                            </td>
                            <td class="py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('drills.show', $drill) }}" wire:navigate class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">
                                        Open
                                    </a>
                                    @if ($canDelete)
                                        <button type="button" wire:click="confirmDeletion({{ $drill->id }})" title="Delete drill record" class="rounded-full border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
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

    @if ($confirmingDeletion)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/40 p-4" wire:key="delete-modal">
            <div class="w-full max-w-lg rounded-3xl border border-white/80 bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-stone-950">Delete drill record</h2>
                        <p class="mt-1 text-sm text-stone-600">
                            This removes <span class="font-semibold text-stone-900">{{ $deletingDrillReference }}</span> from the workspace. A full audit archive (all fields, actions, and workflow history) is kept, and it can no longer be edited or restored here.
                        </p>
                    </div>
                </div>

                <div class="mt-5">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Type <span class="font-mono normal-case tracking-normal text-stone-900">{{ $deletingDrillReference }}</span> to confirm</label>
                    <input wire:model="deleteConfirmationReference" wire:keydown.enter="deleteDrill" type="text" autocomplete="off" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" placeholder="Reference number">
                    @error('deleteConfirmationReference')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="cancelDeletion" class="rounded-full border border-stone-300 px-5 py-2.5 text-sm font-semibold text-stone-700">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteDrill" wire:loading.attr="disabled" class="rounded-full bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-600/20 hover:bg-red-700 disabled:opacity-60">
                        Delete permanently
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
