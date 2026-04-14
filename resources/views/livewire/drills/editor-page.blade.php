<div class="space-y-6">
    <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Drill Record</div>
                <h1 class="mt-2 text-3xl font-extrabold text-stone-950">
                    {{ $record?->reference_no ?? 'New Drill Submission' }}
                </h1>
                <p class="mt-2 max-w-2xl text-sm text-stone-600">
                    Capture drill details, response events, follow-up actions, attachments, and the workflow history for this exercise.
                </p>
            </div>

            @if ($record)
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-stone-100 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-stone-700">{{ $record->status->name }}</span>
                    <a href="{{ route('drills.print', $record) }}" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700">
                        Download PDF
                    </a>
                </div>
            @endif
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
        <div class="space-y-6">
            <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Rig</label>
                        <select wire:model="rigId" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable || $rigs->count() === 1)>
                            @foreach ($rigs as $rig)
                                <option value="{{ $rig->id }}">{{ $rig->name }}</option>
                            @endforeach
                        </select>
                        @error('rigId') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Drill Date</label>
                        <input wire:model="drillDate" type="date" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)>
                        @error('drillDate') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Drill Time</label>
                        <input wire:model="drillTime" type="time" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Event Location</label>
                        <input wire:model="eventLocation" type="text" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Drill Type</label>
                        <select wire:model="drillTypeId" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)>
                            <option value="">Select drill type</option>
                            @foreach ($drillTypes as $drillType)
                                <option value="{{ $drillType->id }}">{{ $drillType->name }}</option>
                            @endforeach
                        </select>
                        @error('drillTypeId') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Event Type</label>
                        <select wire:model="eventTypeId" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)>
                            <option value="">Select event type</option>
                            @foreach ($eventTypes as $eventType)
                                <option value="{{ $eventType->id }}">{{ $eventType->name }}</option>
                            @endforeach
                        </select>
                        @error('eventTypeId') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">On Duty Crews</label>
                        <textarea wire:model="onDutyCrews" rows="2" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Scenario</label>
                        <textarea wire:model="scenario" rows="4" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Applicable DSHA</label>
                        <input wire:model="applicableDsha" type="text" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Performance Standard</label>
                        <textarea wire:model="performanceStandard" rows="3" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)></textarea>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Performance Result</label>
                        <select wire:model="performanceStandardsMet" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)>
                            <option value="">Select result</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                            <option value="Partial">Partial</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Objectives</label>
                        <textarea wire:model="objectives" rows="3" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Debrief Attendees</label>
                        <textarea wire:model="debriefAttendees" rows="3" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Positive Observations</label>
                        <textarea wire:model="positiveObservations" rows="3" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Improvement Opportunities</label>
                        <textarea wire:model="improvementOpportunities" rows="3" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Other Comments</label>
                        <textarea wire:model="otherComments" rows="3" class="mt-2 w-full rounded-2xl border-stone-300 bg-stone-50 text-sm" @disabled(! $editable)></textarea>
                    </div>
                </div>
            </section>

            <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-stone-950">Timeline Events</h2>
                    @if ($editable)
                        <button wire:click="addEvent" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700">Add Event</button>
                    @endif
                </div>
                <div class="mt-5 space-y-4">
                    @foreach ($events as $index => $event)
                        <div wire:key="event-{{ $index }}" class="grid gap-4 rounded-3xl border border-stone-200 bg-stone-50 p-4 md:grid-cols-[160px_1fr_auto]">
                            <input wire:model="events.{{ $index }}.event_time" type="time" class="rounded-2xl border-stone-300 bg-white text-sm" @disabled(! $editable)>
                            <textarea wire:model="events.{{ $index }}.event_description" rows="2" placeholder="Describe the event" class="rounded-2xl border-stone-300 bg-white text-sm" @disabled(! $editable)></textarea>
                            @if ($editable)
                                <button wire:click="removeEvent({{ $index }})" type="button" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-700">Remove</button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-stone-950">Follow-up Actions</h2>
                    @if ($editable)
                        <button wire:click="addAction" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700">Add Action</button>
                    @endif
                </div>
                <div class="mt-5 space-y-4">
                    @foreach ($actions as $index => $action)
                        <div wire:key="action-{{ $index }}" class="grid gap-4 rounded-3xl border border-stone-200 bg-stone-50 p-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <textarea wire:model="actions.{{ $index }}.action_description" rows="2" placeholder="Describe the action item" class="w-full rounded-2xl border-stone-300 bg-white text-sm" @disabled(! $editable)></textarea>
                            </div>
                            <input wire:model="actions.{{ $index }}.action_owner" type="text" placeholder="Action owner" class="rounded-2xl border-stone-300 bg-white text-sm" @disabled(! $editable)>
                            <select wire:model="actions.{{ $index }}.action_status_id" class="rounded-2xl border-stone-300 bg-white text-sm" @disabled(! $editable)>
                                @foreach ($actionStatuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                            <input wire:model="actions.{{ $index }}.due_date" type="date" class="rounded-2xl border-stone-300 bg-white text-sm" @disabled(! $editable)>
                            @if ($editable)
                                <div class="flex justify-end">
                                    <button wire:click="removeAction({{ $index }})" type="button" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-700">Remove</button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-stone-950">Attachments</h2>
                    @if ($editable)
                        <button wire:click="addNewAttachment" type="button" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700">Add Attachment</button>
                    @endif
                </div>

                @if ($editable)
                    <p class="mt-3 text-sm text-stone-500">Upload image attachments only. Maximum file size: 1.5 MB each.</p>

                    <div class="mt-5 space-y-4">
                        @foreach ($newAttachments as $index => $upload)
                            <div wire:key="new-attachment-{{ $index }}" class="grid gap-4 rounded-3xl border border-stone-200 bg-stone-50 p-4 md:grid-cols-[1fr_1fr_auto]">
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Image File</label>
                                    <input wire:model="newAttachments.{{ $index }}" type="file" accept="image/*" class="mt-2 w-full rounded-2xl border-stone-300 bg-white text-sm">
                                    @error('newAttachments.' . $index) <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Caption</label>
                                    <input wire:model="newAttachmentCaptions.{{ $index }}" type="text" placeholder="Describe this image" class="mt-2 w-full rounded-2xl border-stone-300 bg-white text-sm">
                                    @error('newAttachmentCaptions.' . $index) <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                                </div>
                                <div class="flex items-end justify-end">
                                    <button wire:click="removeNewAttachment({{ $index }})" type="button" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-700">Remove</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-5 space-y-3">
                    @foreach ($record?->attachments ?? [] as $attachment)
                        <div class="flex flex-col gap-3 rounded-3xl border border-stone-200 bg-stone-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="font-semibold text-stone-900">{{ $attachment->file_name }}</div>
                                @if ($attachment->caption)
                                    <div class="mt-1 text-sm text-stone-600">{{ $attachment->caption }}</div>
                                @endif
                                <div class="text-xs uppercase tracking-[0.18em] text-stone-500">{{ $attachment->mime_type }} Â· {{ $attachment->file_size_kb }} KB</div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('attachments.show', $attachment) }}" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700">Download</a>
                                @if ($editable)
                                    <button wire:click="removeAttachment({{ $attachment->id }})" type="button" class="rounded-full border border-rose-200 px-4 py-2 text-xs font-semibold text-rose-700">Remove</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if (($record?->attachments?->count() ?? 0) === 0 && count($newAttachments) === 0)
                        <div class="rounded-3xl border border-dashed border-stone-300 bg-stone-50 p-6 text-sm text-stone-500">
                            No attachments added yet.
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="space-y-6 xl:self-start">
            <section class="rounded-[2rem] border border-stone-900/10 bg-stone-950 p-6 text-white shadow-xl shadow-stone-900/20 xl:sticky xl:top-24">
                <h2 class="text-xl font-bold">Workflow Actions</h2>
                <p class="mt-2 text-sm leading-6 text-stone-300">Only the role assigned to the current workflow step can progress or return the record.</p>

                @if ($record)
                    <div class="mt-4 grid gap-3 text-sm">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-stone-300">STO Snapshot</div>
                            <div class="mt-2 font-semibold">{{ $record->sto_name ?: 'Not assigned' }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-stone-300">BE Snapshot</div>
                            <div class="mt-2 font-semibold">{{ $record->be_name ?: 'Not assigned' }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-stone-300">OIM Snapshot</div>
                            <div class="mt-2 font-semibold">{{ $record->oim_name ?: 'Not assigned' }}</div>
                        </div>
                    </div>
                @endif

                <div class="mt-6 space-y-3">
                    @if ($editable)
                        <button wire:click="resetForm" type="button" class="w-full rounded-2xl border border-white/15 bg-white/5 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                            Reset Form
                        </button>
                        <button wire:click="saveDraft" type="button" class="w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-semibold text-white">
                            Save Draft
                        </button>
                        <button wire:click="submit" type="button" class="w-full rounded-2xl bg-amber-400 px-4 py-3 text-sm font-semibold text-stone-950">
                            Submit to BE
                        </button>
                    @endif

                    @if ($canVerify)
                        <button wire:click="verify" type="button" class="w-full rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white">
                            Verify Drill
                        </button>
                        <button wire:click="returnByBe" type="button" class="w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-semibold text-white">
                            Return to STO
                        </button>
                    @endif

                    @if ($canApprove)
                        <button wire:click="approve" type="button" class="w-full rounded-2xl bg-teal-500 px-4 py-3 text-sm font-semibold text-white">
                            Approve Drill
                        </button>
                        <button wire:click="returnByOim" type="button" class="w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-semibold text-white">
                            Return to STO
                        </button>
                    @endif

                    @if ($canClose)
                        <button wire:click="closeRecord" type="button" class="w-full rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-stone-950">
                            Close Drill
                        </button>
                    @endif
                </div>

                <div class="mt-6">
                    <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-300">Workflow Comments</label>
                    <textarea wire:model="reviewComments" rows="4" class="mt-2 w-full rounded-2xl border-transparent bg-white/10 text-sm text-white placeholder:text-stone-300"></textarea>
                    @error('reviewComments') <div class="mt-2 text-sm text-amber-200">{{ $message }}</div> @enderror
                </div>
            </section>

            @if ($record)
                <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
                    <h2 class="text-xl font-bold text-stone-950">Workflow History</h2>
                    <div class="mt-5 space-y-4">
                        @forelse ($record->workflowHistory as $history)
                            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="text-sm font-semibold text-stone-900">{{ str($history->action)->replace('_', ' ')->headline() }}</div>
                                        <div class="mt-1 text-xs uppercase tracking-[0.18em] text-stone-500">
                                            {{ $history->actor?->full_name ?? 'System' }} · {{ $record->rig->formatDateTime($history->created_at) }}
                                        </div>
                                    </div>
                                    @if ($history->toStatus)
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-700">{{ $history->toStatus->name }}</span>
                                    @endif
                                </div>
                                @if ($history->comments)
                                    <p class="mt-3 text-sm leading-6 text-stone-600">{{ $history->comments }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-3xl border border-dashed border-stone-300 bg-stone-50 p-6 text-sm text-stone-500">
                                No workflow actions have been recorded yet.
                            </div>
                        @endforelse
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>



