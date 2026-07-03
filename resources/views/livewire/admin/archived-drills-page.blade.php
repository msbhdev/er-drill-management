<div class="space-y-6">
    <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
        <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Audit Archive</div>
        <h1 class="mt-2 text-3xl font-extrabold text-stone-950">Deleted Drills</h1>
        <p class="mt-2 max-w-3xl text-sm text-stone-600">
            A permanent, read-only record of drills that administrators have deleted. Each entry keeps a full snapshot of the drill at the time it was removed — all fields, follow-up actions, events, and workflow history — plus who deleted it and when.
        </p>
    </section>

    <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
        <div class="max-w-md">
            <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Search</label>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Reference, rig, or who deleted it" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm">
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead>
                    <tr class="text-left text-stone-500">
                        <th class="pb-3 font-semibold">Reference</th>
                        <th class="pb-3 font-semibold">Rig</th>
                        <th class="pb-3 font-semibold">Status when deleted</th>
                        <th class="pb-3 font-semibold">Drill Date</th>
                        <th class="pb-3 font-semibold">Deleted By</th>
                        <th class="pb-3 font-semibold">Deleted At</th>
                        <th class="pb-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($archives as $archive)
                        <tr class="text-stone-700">
                            <td class="py-4 font-semibold text-stone-900">{{ $archive->reference_no }}</td>
                            <td class="py-4">{{ $archive->rig_name ?? '—' }}</td>
                            <td class="py-4">
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-700">{{ $archive->status_name ?? 'Unknown' }}</span>
                            </td>
                            <td class="py-4">{{ $archive->drill_date?->format('d M Y') ?? '—' }}</td>
                            <td class="py-4">{{ $archive->deleted_by_name ?? '—' }}</td>
                            <td class="py-4">{{ $archive->archived_at?->format('d M Y, H:i') ?? '—' }}</td>
                            <td class="py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" wire:click="viewArchive({{ $archive->id }})" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700 hover:bg-stone-100">
                                        View
                                    </button>
                                    <a href="{{ route('admin.deleted-drills.print', $archive) }}" target="_blank" rel="noopener" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700 hover:bg-stone-100">
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-stone-500">No deleted drills have been archived yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $archives->links() }}
        </div>
    </section>

    @if ($viewing)
        @php
            $snapshot = $viewing->snapshot ?? [];
            $drill = $snapshot['drill'] ?? [];
            $people = $snapshot['people'] ?? [];
            $detailFields = [
                'drill_time' => 'Drill Time',
                'event_location' => 'Event Location',
                'on_duty_crews' => 'On-Duty Crews',
                'scenario' => 'Scenario',
                'applicable_dsha' => 'Applicable DSHA (legacy text)',
                'performance_standard' => 'Performance Standard',
                'performance_standards_met' => 'Performance Result',
                'objectives' => 'Objectives',
                'debrief_attendees' => 'Debrief Attendees',
                'positive_observations' => 'Positive Observations',
                'improvement_opportunities' => 'Improvement Opportunities',
                'other_comments' => 'Other Comments',
            ];
        @endphp

        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-stone-900/40 p-4" wire:key="archive-modal-{{ $viewing->id }}">
            <div class="my-8 w-full max-w-3xl rounded-3xl border border-white/80 bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Archived Drill</div>
                        <h2 class="mt-1 text-2xl font-bold text-stone-950">{{ $viewing->reference_no }}</h2>
                        <p class="mt-1 text-sm text-stone-500">
                            {{ $viewing->rig_name ?? '—' }} · {{ $viewing->status_name ?? 'Unknown' }} when deleted
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <a href="{{ route('admin.deleted-drills.print', $viewing) }}" target="_blank" rel="noopener" class="rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-stone-900/15 hover:bg-stone-800">
                            Print PDF
                        </a>
                        <button type="button" wire:click="closeArchive" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-100">
                            Close
                        </button>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800">
                    Deleted by {{ $viewing->deleted_by_name ?? 'Unknown' }} on {{ $viewing->archived_at?->format('d M Y, H:i') ?? '—' }} · original drill ID {{ $viewing->original_drill_id ?? '—' }}
                </div>

                {{-- Classification --}}
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Drill Date</div>
                        <div class="mt-1 text-sm font-semibold text-stone-900">{{ $viewing->drill_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Drill Types</div>
                        <div class="mt-1 text-sm font-semibold text-stone-900">
                            {{ collect($snapshot['drill_types'] ?? [])->pluck('name')->implode(', ') ?: '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Event Types</div>
                        <div class="mt-1 text-sm font-semibold text-stone-900">
                            {{ collect($snapshot['event_types'] ?? [])->pluck('name')->implode(', ') ?: '—' }}
                        </div>
                    </div>
                </div>

                @if (! empty($snapshot['dshas']))
                    <div class="mt-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Applicable DSHAs</div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($snapshot['dshas'] as $dsha)
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-700">{{ $dsha['code'] ?? '' }} — {{ $dsha['name'] ?? '' }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Drill details --}}
                <div class="mt-6 space-y-4 border-t border-stone-100 pt-5">
                    @foreach ($detailFields as $key => $label)
                        @if (! empty($drill[$key]))
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">{{ $label }}</div>
                                <p class="mt-1 whitespace-pre-line text-sm leading-6 text-stone-700">{{ $drill[$key] }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Events --}}
                @if (! empty($snapshot['events']))
                    <div class="mt-6 border-t border-stone-100 pt-5">
                        <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-stone-700">Drill Events</h3>
                        <div class="mt-3 space-y-2">
                            @foreach ($snapshot['events'] as $event)
                                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-3 text-sm text-stone-700">
                                    @if (! empty($event['event_time']))
                                        <span class="font-semibold text-stone-900">{{ $event['event_time'] }}</span> ·
                                    @endif
                                    {{ $event['event_description'] ?? '' }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                @if (! empty($snapshot['actions']))
                    <div class="mt-6 border-t border-stone-100 pt-5">
                        <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-stone-700">Follow-up Actions</h3>
                        <div class="mt-3 space-y-2">
                            @foreach ($snapshot['actions'] as $action)
                                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-3 text-sm text-stone-700">
                                    <div class="font-semibold text-stone-900">{{ $action['action_description'] ?? '' }}</div>
                                    <div class="mt-1 text-xs text-stone-500">
                                        Owner: {{ $action['action_owner'] ?: '—' }}
                                        · Due: {{ $action['due_date'] ? \Illuminate\Support\Str::of($action['due_date'])->substr(0, 10) : '—' }}
                                        · Status: {{ $action['status']['name'] ?? '—' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Attachments --}}
                @if (! empty($snapshot['attachments']))
                    <div class="mt-6 border-t border-stone-100 pt-5">
                        <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-stone-700">Attachments</h3>
                        <p class="mt-1 text-xs text-stone-500">Files are retained on the server. Open a file to view or download it.</p>
                        <div class="mt-3 space-y-2">
                            @foreach ($snapshot['attachments'] as $attachment)
                                <div class="flex items-start justify-between gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-3 text-sm text-stone-700">
                                    <div class="min-w-0">
                                        <div class="font-semibold text-stone-900">{{ $attachment['file_name'] ?? basename($attachment['file_path'] ?? '') }}</div>
                                        @if (! empty($attachment['caption']))
                                            <div class="text-xs text-stone-600">{{ $attachment['caption'] }}</div>
                                        @endif
                                        <div class="mt-1 break-all font-mono text-xs text-stone-400">{{ $attachment['file_path'] ?? '' }}</div>
                                    </div>
                                    <a href="{{ route('admin.deleted-drills.attachment', ['archivedDrillRecord' => $viewing, 'index' => $loop->index]) }}" target="_blank" rel="noopener" class="shrink-0 rounded-full border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100">
                                        Open file
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Assigned people --}}
                <div class="mt-6 grid gap-4 border-t border-stone-100 pt-5 sm:grid-cols-2">
                    @foreach (['creator' => 'Created By', 'sto' => 'STO', 'be' => 'BE', 'oim' => 'OIM'] as $roleKey => $roleLabel)
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">{{ $roleLabel }}</div>
                            <div class="mt-1 text-sm font-semibold text-stone-900">{{ $people[$roleKey]['full_name'] ?? '—' }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Workflow history --}}
                @if (! empty($snapshot['workflow_history']))
                    <div class="mt-6 border-t border-stone-100 pt-5">
                        <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-stone-700">Workflow History</h3>
                        <div class="mt-3 space-y-2">
                            @foreach ($snapshot['workflow_history'] as $history)
                                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-3 text-sm text-stone-700">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="font-semibold text-stone-900">{{ \Illuminate\Support\Str::of($history['action'] ?? '')->replace('_', ' ')->headline() }}</span>
                                        @if (! empty($history['to_status']['name']))
                                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-600">{{ $history['to_status']['name'] }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 text-xs text-stone-500">
                                        {{ $history['actor']['full_name'] ?? 'System' }}
                                        @if (! empty($history['created_at'])) · {{ \Illuminate\Support\Str::of($history['created_at'])->replace('T', ' ')->substr(0, 16) }} @endif
                                    </div>
                                    @if (! empty($history['comments']))
                                        <p class="mt-2 text-sm leading-6 text-stone-600">{{ $history['comments'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
