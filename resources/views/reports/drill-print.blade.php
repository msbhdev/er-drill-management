<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>{{ $record->reference_no }}</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
            h1, h2, h3 { margin-bottom: 8px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; }
            th { width: 25%; background: #f9fafb; }
            .section-title { margin-top: 18px; margin-bottom: 10px; font-size: 16px; }
        </style>
    </head>
    <body>
        <h1>ER Drill Record</h1>
        <h2>{{ $record->reference_no }}</h2>

        <table>
            <tr><th>Rig</th><td>{{ $record->rig->name }}</td></tr>
            <tr><th>Drill Type</th><td>{{ $record->drillType->name }}</td></tr>
            <tr><th>Event Type</th><td>{{ $record->eventType->name }}</td></tr>
            <tr><th>Date</th><td>{{ $record->drill_date?->format('d M Y') }}</td></tr>
            <tr><th>Status</th><td>{{ $record->status->name }}</td></tr>
            <tr><th>STO</th><td>{{ $record->sto_name }}</td></tr>
            <tr><th>BE</th><td>{{ $record->be_name }}</td></tr>
            <tr><th>OIM</th><td>{{ $record->oim_name }}</td></tr>
            <tr><th>Location</th><td>{{ $record->event_location }}</td></tr>
            <tr><th>Scenario</th><td>{{ $record->scenario }}</td></tr>
            <tr><th>Objectives</th><td>{{ $record->objectives }}</td></tr>
            <tr><th>Positive Observations</th><td>{{ $record->positive_observations }}</td></tr>
            <tr><th>Improvement Opportunities</th><td>{{ $record->improvement_opportunities }}</td></tr>
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
                @foreach ($record->events as $event)
                    <tr>
                        <td>{{ $event->event_time }}</td>
                        <td>{{ $event->event_description }}</td>
                    </tr>
                @endforeach
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
                @foreach ($record->actions as $action)
                    <tr>
                        <td>{{ $action->action_owner }}</td>
                        <td>{{ $action->action_description }}</td>
                        <td>{{ optional($action->due_date)->format('d M Y') }}</td>
                        <td>{{ $action->status->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

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
                @foreach ($record->workflowHistory as $history)
                    <tr>
                        <td>{{ $history->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $history->actor?->full_name ?? 'System' }}</td>
                        <td>{{ str($history->action)->replace('_', ' ')->headline() }}</td>
                        <td>{{ $history->comments }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
