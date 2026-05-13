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
                        <x-multiselect-dropdown
                            :options="$drillTypes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->all()"
                            wire-model="drillTypeIds"
                            placeholder="Select drill types"
                            search-placeholder="Search drill types"
                            :disabled="! $editable"
                        />
                        @error('drillTypeIds') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                        @error('drillTypeIds.*') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Event Type</label>
                        <x-multiselect-dropdown
                            :options="$eventTypes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->all()"
                            wire-model="eventTypeIds"
                            placeholder="Select event types"
                            search-placeholder="Search event types"
                            :disabled="! $editable"
                        />
                        @error('eventTypeIds') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
                        @error('eventTypeIds.*') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
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
                                    <input wire:model="newAttachments.{{ $index }}" type="file" accept="image/*" class="mt-2 block w-full cursor-pointer rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm text-stone-600 file:mr-4 file:rounded-full file:border-0 file:bg-stone-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-stone-800">
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

        <div class="xl:self-start" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <section style="position: relative; overflow: hidden; border-radius: 1.75rem; border: 1px solid rgba(43,45,138,0.08); background: white; padding: 1.75rem; box-shadow: 0 12px 40px -16px rgba(43,45,138,0.15), 0 2px 6px rgba(43,45,138,0.04);">
                {{-- Gem accents matching the dashboard hero --}}
                <div style="position: absolute; top: -40px; right: -40px; width: 160px; height: 160px; border-radius: 9999px; background: radial-gradient(circle, rgba(0,163,200,0.10) 0%, transparent 70%); pointer-events: none;"></div>
                <div style="position: absolute; bottom: -60px; left: -40px; width: 140px; height: 140px; border-radius: 9999px; background: radial-gradient(circle, rgba(123,63,184,0.07) 0%, transparent 70%); pointer-events: none;"></div>

                <div style="position: relative;">
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 9999px; border: 1px solid rgba(43,45,138,0.18); background: rgba(43,45,138,0.04); padding: 0.3rem 0.875rem; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #2B2D8A;">
                        <span style="width: 0.375rem; height: 0.375rem; border-radius: 9999px; background: #00A3C8;"></span>
                        Workflow
                    </div>
                    <h2 style="margin-top: 0.875rem; font-size: 1.25rem; font-weight: 800; color: #1A1C5E;">Workflow Actions</h2>
                    <p style="margin-top: 0.5rem; font-size: 0.875rem; line-height: 1.65; color: #64748b;">Only the role assigned to the current workflow step can progress or return the record.</p>

                    @if ($record)
                        <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 0.625rem;">
                            @foreach ([['STO', $record->sto_name], ['BE', $record->be_name], ['OIM', $record->oim_name]] as $snap)
                                <div style="border-radius: 0.875rem; border: 1px solid rgba(43,45,138,0.08); background: #F7F8FC; padding: 0.875rem 1rem;">
                                    <div style="font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: #2B2D8A;">{{ $snap[0] }} Snapshot</div>
                                    <div style="margin-top: 0.3rem; font-size: 0.9375rem; font-weight: 700; color: #1A1C5E;">{{ $snap[1] ?: 'Not assigned' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.625rem;">
                        @if ($editable)
                            <button wire:click="resetForm" type="button"
                                    style="width: 100%; border-radius: 0.875rem; border: 1px solid rgba(43,45,138,0.18); background: white; padding: 0.8rem 1rem; font-size: 0.875rem; font-weight: 600; color: #475569; cursor: pointer; transition: all 0.15s;"
                                    onmouseover="this.style.borderColor='#2B2D8A'; this.style.color='#2B2D8A';"
                                    onmouseout="this.style.borderColor='rgba(43,45,138,0.18)'; this.style.color='#475569';">
                                Reset Form
                            </button>
                            <button wire:click="saveDraft" type="button"
                                    style="width: 100%; border-radius: 0.875rem; background: rgba(43,45,138,0.08); padding: 0.8rem 1rem; font-size: 0.875rem; font-weight: 700; color: #2B2D8A; border: none; cursor: pointer; transition: background 0.15s;"
                                    onmouseover="this.style.background='rgba(43,45,138,0.14)';"
                                    onmouseout="this.style.background='rgba(43,45,138,0.08)';">
                                Save Draft
                            </button>
                            <button wire:click="submit" type="button"
                                    style="width: 100%; border-radius: 0.875rem; background: #2B2D8A; padding: 0.8rem 1rem; font-size: 0.875rem; font-weight: 700; color: white; border: none; cursor: pointer; box-shadow: 0 6px 18px rgba(43,45,138,0.28); transition: opacity 0.15s;"
                                    onmouseover="this.style.opacity='0.9';"
                                    onmouseout="this.style.opacity='1';">
                                Submit to BE
                            </button>
                        @endif

                        @if ($canVerify)
                            <button wire:click="verify" type="button"
                                    style="width: 100%; border-radius: 0.875rem; background: #00A3C8; padding: 0.8rem 1rem; font-size: 0.875rem; font-weight: 700; color: white; border: none; cursor: pointer; box-shadow: 0 6px 18px rgba(0,163,200,0.35); transition: opacity 0.15s;"
                                    onmouseover="this.style.opacity='0.9';"
                                    onmouseout="this.style.opacity='1';">
                                Verify Drill
                            </button>
                            <button wire:click="returnByBe" type="button"
                                    style="width: 100%; border-radius: 0.875rem; border: 1px solid rgba(232,118,42,0.4); background: rgba(232,118,42,0.08); padding: 0.8rem 1rem; font-size: 0.875rem; font-weight: 700; color: #a85416; cursor: pointer; transition: background 0.15s;"
                                    onmouseover="this.style.background='rgba(232,118,42,0.16)';"
                                    onmouseout="this.style.background='rgba(232,118,42,0.08)';">
                                Return to STO
                            </button>
                        @endif

                        @if ($canApprove)
                            <button wire:click="approve" type="button"
                                    style="width: 100%; border-radius: 0.875rem; background: #7B3FB8; padding: 0.8rem 1rem; font-size: 0.875rem; font-weight: 700; color: white; border: none; cursor: pointer; box-shadow: 0 6px 18px rgba(123,63,184,0.32); transition: opacity 0.15s;"
                                    onmouseover="this.style.opacity='0.9';"
                                    onmouseout="this.style.opacity='1';">
                                Approve Drill
                            </button>
                            <button wire:click="returnByOim" type="button"
                                    style="width: 100%; border-radius: 0.875rem; border: 1px solid rgba(232,118,42,0.4); background: rgba(232,118,42,0.08); padding: 0.8rem 1rem; font-size: 0.875rem; font-weight: 700; color: #a85416; cursor: pointer; transition: background 0.15s;"
                                    onmouseover="this.style.background='rgba(232,118,42,0.16)';"
                                    onmouseout="this.style.background='rgba(232,118,42,0.08)';">
                                Return to STO
                            </button>
                        @endif

                        @if ($canClose)
                            <button wire:click="closeRecord" type="button"
                                    style="width: 100%; border-radius: 0.875rem; background: #047857; padding: 0.8rem 1rem; font-size: 0.875rem; font-weight: 700; color: white; border: none; cursor: pointer; box-shadow: 0 6px 18px rgba(4,120,87,0.28); transition: opacity 0.15s;"
                                    onmouseover="this.style.opacity='0.9';"
                                    onmouseout="this.style.opacity='1';">
                                Close Drill
                            </button>
                        @endif
                    </div>

                    <div style="margin-top: 1.5rem;">
                        <label style="display: block; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: #2B2D8A; margin-bottom: 0.5rem;">Workflow Comments</label>
                        <textarea wire:model="reviewComments" rows="4"
                                  style="width: 100%; border-radius: 0.875rem; border: 1px solid rgba(43,45,138,0.18); background: #F7F8FC; padding: 0.75rem 1rem; font-size: 0.875rem; color: #1A1C5E; box-sizing: border-box; font-family: inherit; resize: vertical;"
                                  onfocus="this.style.borderColor='#2B2D8A'; this.style.background='white';"
                                  onblur="this.style.borderColor='rgba(43,45,138,0.18)'; this.style.background='#F7F8FC';"></textarea>
                        @error('reviewComments') <div style="margin-top: 0.5rem; font-size: 0.8125rem; color: #b45309;">{{ $message }}</div> @enderror
                    </div>
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



