<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>{{ $record->reference_no }}</title>
        <style>
            @page { margin: 92px 36px 48px; }

            body {
                font-family: DejaVu Sans, sans-serif;
                color: #1f2937;
                font-size: 11px;
                line-height: 1.45;
            }

            .page-header {
                position: fixed;
                top: -68px;
                left: 0;
                right: 0;
                height: 48px;
                border-bottom: 1px solid #d1d5db;
            }

            .header-logo {
                width: 116px;
                height: auto;
            }

            .header-title {
                position: absolute;
                right: 0;
                top: 5px;
                text-align: right;
                font-size: 10px;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            h1 {
                margin: 0 0 4px;
                font-size: 21px;
                color: #111827;
            }

            h2 {
                margin: 0 0 18px;
                font-size: 14px;
                font-weight: normal;
                color: #4b5563;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 18px;
            }

            th,
            td {
                border: 1px solid #d1d5db;
                padding: 7px;
                text-align: left;
                vertical-align: top;
            }

            th {
                width: 25%;
                background: #f9fafb;
                color: #374151;
            }

            .section-title {
                margin-top: 18px;
                margin-bottom: 8px;
                padding-bottom: 5px;
                border-bottom: 2px solid #111827;
                font-size: 14px;
                font-weight: bold;
                color: #111827;
            }

            .attachments-table {
                width: 100%;
                margin-bottom: 18px;
                table-layout: fixed;
            }

            .attachment-cell {
                width: 50%;
                page-break-inside: avoid;
                padding: 10px;
            }

            .attachment-number {
                margin-bottom: 7px;
                font-size: 10px;
                font-weight: bold;
                color: #111827;
            }

            .attachment-image {
                display: block;
                max-width: 100%;
                max-height: 260px;
                margin: 0 auto 8px;
            }

            .attachment-caption {
                border-top: 1px solid #e5e7eb;
                padding-top: 7px;
                font-size: 10px;
                color: #374151;
            }

            .muted {
                color: #6b7280;
            }

            .signature-section {
                page-break-inside: avoid;
                margin-top: 26px;
            }

            .signature-table {
                table-layout: fixed;
            }

            .signature-table th {
                width: auto;
                text-align: center;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.7px;
            }

            .signature-table td {
                height: 38px;
                text-align: center;
                vertical-align: middle;
            }

            .signature-name {
                font-weight: bold;
                color: #111827;
            }

            .signature-meta {
                margin-top: 3px;
                font-size: 9px;
                color: #6b7280;
            }

            .system-note {
                margin-top: -8px;
                font-size: 9px;
                color: #6b7280;
                text-align: center;
            }
        </style>
    </head>
    <body>
        @php
            $resolvedLogoPath = $logoPath ?? public_path('images/vantris-energy-berhad-logo.png');
            $resolvedAttachments = $attachmentsForPdf ?? collect();
        @endphp

        <div class="page-header">
            @if (file_exists($resolvedLogoPath))
                <img class="header-logo" src="{{ $resolvedLogoPath }}" alt="Vantris Energy Berhad">
            @endif
            <div class="header-title">
                Emergency Response Drill<br>
                {{ $record->reference_no }}
            </div>
        </div>

        <h1>ER Drill Record</h1>
        <h2>{{ $record->reference_no }}</h2>

        <table>
            <tr><th>Rig</th><td>{{ $record->rig->name }}</td></tr>
            <tr><th>Drill Type</th><td>{{ $record->drillTypeNames() }}</td></tr>
            <tr><th>Event Type</th><td>{{ $record->eventTypeNames() }}</td></tr>
            <tr><th>Date</th><td>{{ $record->drill_date?->format('d M Y') }}</td></tr>
            <tr><th>Time</th><td>{{ $record->drill_time?->format('H:i') }}</td></tr>
            <tr><th>Status</th><td>{{ $record->status->name }}</td></tr>
            <tr><th>Location</th><td>{{ $record->event_location }}</td></tr>
            <tr><th>Scenario</th><td>{{ $record->scenario }}</td></tr>
            <tr><th>Applicable DSHA</th><td>{{ $record->applicable_dsha }}</td></tr>
            <tr><th>Performance Standard</th><td>{{ $record->performance_standard }}</td></tr>
            <tr><th>Performance Result</th><td>{{ $record->performance_standards_met }}</td></tr>
            <tr><th>Objectives</th><td>{{ $record->objectives }}</td></tr>
            <tr><th>Debrief Attendees</th><td>{{ $record->debrief_attendees }}</td></tr>
            <tr><th>Positive Observations</th><td>{{ $record->positive_observations }}</td></tr>
            <tr><th>Improvement Opportunities</th><td>{{ $record->improvement_opportunities }}</td></tr>
            <tr><th>Other Comments</th><td>{{ $record->other_comments }}</td></tr>
        </table>

        <div class="section-title">Timeline Events</div>
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($record->events as $event)
                    <tr>
                        <td>{{ $event->event_time }}</td>
                        <td>{{ $event->event_description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="muted">No timeline events recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Follow-up Actions</div>
        <table>
            <thead>
                <tr>
                    <th>Owner</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($record->actions as $action)
                    <tr>
                        <td>{{ $action->action_owner }}</td>
                        <td>{{ $action->action_description }}</td>
                        <td>{{ optional($action->due_date)->format('d M Y') }}</td>
                        <td>{{ $action->status->name }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted">No follow-up actions recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Attachments</div>
        @if ($resolvedAttachments->isNotEmpty())
            <table class="attachments-table">
                @foreach ($resolvedAttachments->chunk(2) as $rowIndex => $attachmentRow)
                    <tr>
                        @foreach ($attachmentRow->values() as $attachmentIndex => $attachment)
                            <td class="attachment-cell">
                                <div class="attachment-number">Attachment {{ ($rowIndex * 2) + $attachmentIndex + 1 }}</div>
                                @if ($attachment['preview_path'])
                                    <img class="attachment-image" src="{{ $attachment['preview_path'] }}" alt="{{ $attachment['file_name'] }}">
                                @else
                                    <div class="muted">Preview unavailable: {{ $attachment['file_name'] }}</div>
                                @endif
                                <div class="attachment-caption">{{ $attachment['caption'] ?: $attachment['file_name'] }}</div>
                            </td>
                        @endforeach
                        @if ($attachmentRow->count() === 1)
                            <td class="attachment-cell">&nbsp;</td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @else
            <table>
                <tr>
                    <td class="muted">No attachments added.</td>
                </tr>
            </table>
        @endif

        <div class="section-title">Workflow History</div>
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($record->workflowHistory as $history)
                    <tr>
                        <td>{{ $record->rig->formatDateTime($history->created_at) }}</td>
                        <td>
                            {{ $history->actor_person_name ?? $history->actor?->currentAssigneeName() ?? 'System' }}
                            @if ($history->actor_account_name)
                                <div style="font-size: 9px; color: #6b7280;">
                                    Account: {{ $history->actor_account_name }}
                                    @if ($history->actor_role_code)
                                        | Role: {{ $history->actor_role_code }}
                                    @endif
                                    @if ($history->actor_rig_code)
                                        | Rig: {{ $history->actor_rig_code }}
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>{{ str($history->action)->replace('_', ' ')->headline() }}</td>
                        <td>{{ $history->comments }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted">No workflow actions recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="signature-section">
            <div class="section-title">Sign Off</div>
            <table class="signature-table">
                <thead>
                    <tr>
                        <th>Prepared By</th>
                        <th>Verified By</th>
                        <th>Approved By</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="signature-name">{{ $record->sto_name ?: 'STO' }}</div>
                            <div class="signature-meta">STO{{ $record->submitted_at ? ' | '.$record->rig->formatDateTime($record->submitted_at) : '' }}</div>
                        </td>
                        <td>
                            <div class="signature-name">{{ $record->be_name ?: 'BE' }}</div>
                            <div class="signature-meta">BE{{ $record->verified_at ? ' | '.$record->rig->formatDateTime($record->verified_at) : '' }}</div>
                        </td>
                        <td>
                            <div class="signature-name">{{ $record->oim_name ?: 'OIM' }}</div>
                            <div class="signature-meta">OIM{{ $record->approved_at ? ' | '.$record->rig->formatDateTime($record->approved_at) : '' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="system-note">This is a system generated document. No signature is required.</div>
        </div>
    </body>
</html>
