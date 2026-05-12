@php($pageSizeOptions = ['5', '10', '30', '50', 'all'])

<div class="space-y-6">
    <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
        <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Administration</div>
        <h1 class="mt-2 text-3xl font-extrabold text-stone-950">System Setup</h1>
        <p class="mt-2 max-w-3xl text-sm text-stone-600">Manage users, rigs, drill types, event types, and configurable status lists from one admin console.</p>
    </section>

    <div class="space-y-6">
        <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
            <h2 class="text-xl font-bold text-stone-950">{{ $editingUserId ? 'Edit Shared Account' : 'Create Shared Account' }}</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Account Label</label>
                    <input wire:model="userFullName" type="text" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
                    @error('userFullName') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Email</label>
                    <input wire:model="userEmail" type="email" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
                    @error('userEmail') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Role</label>
                    <select wire:model.live="userRole" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
                        @foreach (config('er_drill.roles') as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Rig</label>
                    <select wire:model="userRigId" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(in_array($userRole, ['Management', 'Administrator'], true))>
                        <option value="">No rig</option>
                        @foreach ($rigs as $rig)
                            <option value="{{ $rig->id }}">{{ $rig->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 xl:col-span-4">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Description</label>
                    <textarea wire:model="userDescription" rows="2" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm"></textarea>
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Current Holder</label>
                    <input wire:model="userCurrentAssigneeName" type="text" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(in_array($userRole, ['Management', 'Administrator'], true))>
                    @error('userCurrentAssigneeName') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Access To This App</label>
                    <label class="mt-2 inline-flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm font-semibold text-stone-700">
                        <input wire:model="userHasAppAccess" type="checkbox" class="rounded border-stone-300 text-teal-700 focus:ring-teal-700">
                        ER Drill enabled
                    </label>
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Holder Effective From</label>
                    <input wire:model="userAssigneeEffectiveFrom" type="date" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(in_array($userRole, ['Management', 'Administrator'], true))>
                    @error('userAssigneeEffectiveFrom') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Holder Effective To</label>
                    <input wire:model="userAssigneeEffectiveTo" type="date" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(in_array($userRole, ['Management', 'Administrator'], true))>
                    @error('userAssigneeEffectiveTo') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                </div>
                <div class="md:col-span-2 xl:col-span-4">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Holder Remarks</label>
                    <textarea wire:model="userAssigneeRemarks" rows="2" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(in_array($userRole, ['Management', 'Administrator'], true))></textarea>
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">{{ $editingUserId ? 'Reset Password' : 'Initial Password' }}</label>
                    <input wire:model="userPassword" type="password" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm font-semibold text-stone-700">
                        <input wire:model="userActiveStatus" type="checkbox" class="rounded border-stone-300 text-teal-700 focus:ring-teal-700">
                        Active account
                    </label>
                </div>
            </div>
            <div class="mt-5 flex gap-3">
                <button wire:click="saveUser" type="button" class="rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white">Save User</button>
                @if ($editingUserId)
                    <button wire:click="$refresh" type="button" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">Refresh</button>
                @endif
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-6">
                <h2 class="text-xl font-bold text-stone-950 lg:shrink-0">User Accounts</h2>
                <div class="relative lg:mx-auto lg:w-full lg:max-w-sm">
                    <svg class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-stone-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.78l3.124 3.124a.75.75 0 1 0 1.06-1.06l-3.124-3.125A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                    </svg>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="userSearch"
                        placeholder="Search by name or email"
                        class="w-full rounded-full border-stone-300 bg-stone-50 py-2 pl-10 pr-4 text-sm placeholder:text-stone-400 focus:border-stone-400 focus:ring-stone-400"
                    >
                </div>
                <label class="flex items-center gap-3 text-sm text-stone-600 lg:shrink-0">
                    <span>Show</span>
                    <span class="inline-flex flex-wrap gap-2">
                        @foreach ($pageSizeOptions as $option)
                            <button
                                wire:click="$set('userRecordsPerPage', '{{ $option }}')"
                                type="button"
                                class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $userRecordsPerPage === $option ? 'bg-stone-900 text-white' : 'border border-stone-300 bg-white text-stone-700' }}"
                            >
                                {{ $option === 'all' ? 'All' : $option }}
                            </button>
                        @endforeach
                    </span>
                </label>
            </div>
            <div class="mt-5 grid max-h-[22.5rem] gap-3 overflow-y-auto pr-1 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($users as $user)
                    <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-semibold text-stone-900">{{ $user->full_name }}</div>
                                <div class="truncate text-xs text-stone-500">{{ $user->email }}</div>
                                <div class="mt-1 truncate text-xs text-stone-500">Holder: {{ $user->currentAssigneeName() }}</div>
                            </div>
                            <span class="shrink-0 rounded-full {{ $user->active_status ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }} px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]">
                                {{ $user->active_status ? 'Active' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="rounded-full bg-white px-3 py-1 font-semibold text-stone-700 ring-1 ring-stone-200">{{ $user->role }}</span>
                                <span class="rounded-full bg-white px-3 py-1 font-semibold text-stone-700 ring-1 ring-stone-200">{{ $user->rig?->name ?? 'All rigs' }}</span>
                            </div>
                            <div class="flex flex-wrap justify-end gap-2">
                                <button wire:click="editUser({{ $user->id }})" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">Edit</button>
                                <button wire:click="toggleUserActive({{ $user->id }})" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">Toggle</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-stone-300 bg-stone-50/60 p-6 text-center text-sm text-stone-500">
                        No accounts match @if (trim($userSearch) !== '') &ldquo;{{ $userSearch }}&rdquo; @else this filter @endif.
                    </div>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-bold text-stone-950">{{ $editingRigId ? 'Edit Rig' : 'Rigs' }}</h2>
                <label class="flex items-center gap-3 text-sm text-stone-600">
                    <span>Show</span>
                    <span class="inline-flex flex-wrap gap-2">
                        @foreach ($pageSizeOptions as $option)
                            <button
                                wire:click="$set('rigRecordsPerPage', '{{ $option }}')"
                                type="button"
                                class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $rigRecordsPerPage === $option ? 'bg-stone-900 text-white' : 'border border-stone-300 bg-white text-stone-700' }}"
                            >
                                {{ $option === 'all' ? 'All' : $option }}
                            </button>
                        @endforeach
                    </span>
                </label>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-4">
                <input wire:model="rigName" type="text" placeholder="Rig name" class="rounded-2xl border-stone-300 bg-stone-50 text-sm">
                <input wire:model="rigCode" type="text" placeholder="Code" class="rounded-2xl border-stone-300 bg-stone-50 text-sm">
                <input wire:model="rigLocation" type="text" placeholder="Location" class="rounded-2xl border-stone-300 bg-stone-50 text-sm">
                <select wire:model="rigTimezone" class="rounded-2xl border-stone-300 bg-stone-50 text-sm">
                    @foreach (config('er_drill.timezones') as $timezone => $label)
                        <option value="{{ $timezone }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-4 flex gap-3">
                <button wire:click="saveRig" type="button" class="rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white">{{ $editingRigId ? 'Update Rig' : 'Add Rig' }}</button>
                @if ($editingRigId)
                    <button wire:click="cancelRigEdit" type="button" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">Cancel</button>
                @endif
            </div>
            <div class="mt-5 max-h-[22.5rem] space-y-3 overflow-y-auto pr-1">
                @foreach ($rigs as $rig)
                    <div class="flex items-center justify-between rounded-3xl border border-stone-200 bg-stone-50 p-4">
                        <div>
                            <div class="font-semibold text-stone-900">{{ $rig->name }} ({{ $rig->code }})</div>
                            <div class="text-sm text-stone-500">{{ $rig->location ?: 'No location set' }}</div>
                            <div class="text-xs uppercase tracking-[0.16em] text-stone-400">{{ config('er_drill.timezones')[$rig->timezoneName()] ?? $rig->timezoneName() }}</div>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="editRig({{ $rig->id }})" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">Edit</button>
                            <button wire:click="toggleRig({{ $rig->id }})" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">
                                {{ $rig->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $rigs->links() }}
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-bold text-stone-950">{{ $editingDrillTypeId ? 'Edit Drill Type' : 'Drill Types' }}</h2>
                <label class="flex items-center gap-3 text-sm text-stone-600">
                    <span>Show</span>
                    <span class="inline-flex flex-wrap gap-2">
                        @foreach ($pageSizeOptions as $option)
                            <button
                                wire:click="$set('drillTypeRecordsPerPage', '{{ $option }}')"
                                type="button"
                                class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $drillTypeRecordsPerPage === $option ? 'bg-stone-900 text-white' : 'border border-stone-300 bg-white text-stone-700' }}"
                            >
                                {{ $option === 'all' ? 'All' : $option }}
                            </button>
                        @endforeach
                    </span>
                </label>
            </div>
            <div class="mt-5 grid gap-4">
                <input wire:model="drillTypeName" type="text" placeholder="Drill type name" class="rounded-2xl border-stone-300 bg-stone-50 text-sm">
                @error('drillTypeName') <div class="text-sm text-rose-600">{{ $message }}</div> @enderror
                <textarea wire:model="drillTypeDescription" rows="2" placeholder="Description" class="rounded-2xl border-stone-300 bg-stone-50 text-sm"></textarea>
                @error('drillTypeDescription') <div class="text-sm text-rose-600">{{ $message }}</div> @enderror
            </div>
            <div class="mt-4 flex gap-3">
                <button wire:click="saveDrillType" type="button" class="rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white">{{ $editingDrillTypeId ? 'Update Drill Type' : 'Add Drill Type' }}</button>
                @if ($editingDrillTypeId)
                    <button wire:click="cancelDrillTypeEdit" type="button" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">Cancel</button>
                @endif
            </div>
            <div class="mt-5 max-h-[22.5rem] space-y-3 overflow-y-auto pr-1">
                @foreach ($drillTypes as $drillType)
                    <div class="flex items-center justify-between rounded-3xl border border-stone-200 bg-stone-50 p-4">
                        <div>
                            <div class="font-semibold text-stone-900">{{ $drillType->name }}</div>
                            <div class="text-sm text-stone-500">{{ $drillType->description ?: 'No description' }}</div>
                        </div>
                        <div class="flex flex-wrap justify-end gap-2">
                            <button wire:click="editDrillType({{ $drillType->id }})" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">Edit</button>
                            <button wire:click="toggleDrillType({{ $drillType->id }})" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">
                                {{ $drillType->is_active ? 'Disable' : 'Enable' }}
                            </button>
                            <button wire:click="deleteDrillType({{ $drillType->id }})" type="button" class="rounded-full border border-rose-200 px-4 py-2 text-xs font-semibold text-rose-700">Delete</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $drillTypes->links() }}
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-bold text-stone-950">{{ $editingEventTypeId ? 'Edit Event Type' : 'Event Types' }}</h2>
                <label class="flex items-center gap-3 text-sm text-stone-600">
                    <span>Show</span>
                    <span class="inline-flex flex-wrap gap-2">
                        @foreach ($pageSizeOptions as $option)
                            <button
                                wire:click="$set('eventTypeRecordsPerPage', '{{ $option }}')"
                                type="button"
                                class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $eventTypeRecordsPerPage === $option ? 'bg-stone-900 text-white' : 'border border-stone-300 bg-white text-stone-700' }}"
                            >
                                {{ $option === 'all' ? 'All' : $option }}
                            </button>
                        @endforeach
                    </span>
                </label>
            </div>
            <div class="mt-5 grid gap-4">
                <input wire:model="eventTypeName" type="text" placeholder="Event type name" class="rounded-2xl border-stone-300 bg-stone-50 text-sm">
                @error('eventTypeName') <div class="text-sm text-rose-600">{{ $message }}</div> @enderror
                <textarea wire:model="eventTypeDescription" rows="2" placeholder="Description" class="rounded-2xl border-stone-300 bg-stone-50 text-sm"></textarea>
                @error('eventTypeDescription') <div class="text-sm text-rose-600">{{ $message }}</div> @enderror
            </div>
            <div class="mt-4 flex gap-3">
                <button wire:click="saveEventType" type="button" class="rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white">{{ $editingEventTypeId ? 'Update Event Type' : 'Add Event Type' }}</button>
                @if ($editingEventTypeId)
                    <button wire:click="cancelEventTypeEdit" type="button" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">Cancel</button>
                @endif
            </div>
            <div class="mt-5 max-h-[22.5rem] space-y-3 overflow-y-auto pr-1">
                @foreach ($eventTypes as $eventType)
                    <div class="flex items-center justify-between rounded-3xl border border-stone-200 bg-stone-50 p-4">
                        <div>
                            <div class="font-semibold text-stone-900">{{ $eventType->name }}</div>
                            <div class="text-sm text-stone-500">{{ $eventType->description ?: 'No description' }}</div>
                        </div>
                        <div class="flex flex-wrap justify-end gap-2">
                            <button wire:click="editEventType({{ $eventType->id }})" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">Edit</button>
                            <button wire:click="toggleEventType({{ $eventType->id }})" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">
                                {{ $eventType->is_active ? 'Disable' : 'Enable' }}
                            </button>
                            <button wire:click="deleteEventType({{ $eventType->id }})" type="button" class="rounded-full border border-rose-200 px-4 py-2 text-xs font-semibold text-rose-700">Delete</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $eventTypes->links() }}
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
            <h2 class="text-xl font-bold text-stone-950">Configurable Statuses</h2>
            <div class="grid gap-6 lg:grid-cols-2">
                <div>
                    <div class="grid gap-3">
                        <input wire:model="drillStatusName" type="text" placeholder="Drill status name" class="rounded-2xl border-stone-300 bg-stone-50 text-sm">
                        <input wire:model="drillStatusCode" type="text" placeholder="Drill status code" class="rounded-2xl border-stone-300 bg-stone-50 text-sm">
                    </div>
                    <button wire:click="saveDrillStatus" type="button" class="mt-4 rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white">Add Drill Status</button>
                    <div class="mt-4 space-y-2">
                        @foreach ($drillStatuses as $status)
                            <div class="flex items-center justify-between rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                                <div>
                                    <div class="font-semibold text-stone-900">{{ $status->name }}</div>
                                    <div class="text-xs uppercase tracking-[0.16em] text-stone-500">{{ $status->code }}</div>
                                </div>
                                <button wire:click="toggleDrillStatus({{ $status->id }})" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">Toggle</button>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <div class="grid gap-3">
                        <input wire:model="actionStatusName" type="text" placeholder="Action status name" class="rounded-2xl border-stone-300 bg-stone-50 text-sm">
                        <input wire:model="actionStatusCode" type="text" placeholder="Action status code" class="rounded-2xl border-stone-300 bg-stone-50 text-sm">
                    </div>
                    <button wire:click="saveActionStatus" type="button" class="mt-4 rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white">Add Action Status</button>
                    <div class="mt-4 space-y-2">
                        @foreach ($actionStatuses as $status)
                            <div class="flex items-center justify-between rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                                <div>
                                    <div class="font-semibold text-stone-900">{{ $status->name }}</div>
                                    <div class="text-xs uppercase tracking-[0.16em] text-stone-500">{{ $status->code }}</div>
                                </div>
                                <button wire:click="toggleActionStatus({{ $status->id }})" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">Toggle</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
