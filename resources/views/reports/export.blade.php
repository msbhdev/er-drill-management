<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>ER Drill Report</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
            h1, h2 { margin-bottom: 8px; }
            .meta { margin-bottom: 20px; color: #6b7280; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
            th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; }
            th { background: #f3f4f6; }
        </style>
    </head>
    <body>
        <h1>ER Drill Report</h1>
        <div class="meta">Generated at {{ $generatedAt }}</div>

        <h2>Drill Records</h2>
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Rig</th>
                    <th>Date</th>
                    <th>Drill Type</th>
                    <th>Event Type</th>
                    <th>Status</th>
                    <th>Performance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        <td>{{ $record->reference_no }}</td>
                        <td>{{ $record->rig->name }}</td>
                        <td>{{ $record->drill_date?->format('d M Y') }}</td>
                        <td>{{ $record->drillTypeNames() }}</td>
                        <td>{{ $record->eventTypeNames() }}</td>
                        <td>{{ $record->status->name }}</td>
                        <td>{{ $record->performance_standards_met ?: 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Overdue Actions</h2>
        <table>
            <thead>
                <tr>
                    <th>Rig</th>
                    <th>Reference</th>
                    <th>Action</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($overdueActions as $action)
                    <tr>
                        <td>{{ $action->drillRecord->rig->name }}</td>
                        <td>{{ $action->drillRecord->reference_no }}</td>
                        <td>{{ $action->action_description }}</td>
                        <td>{{ optional($action->due_date)->format('d M Y') }}</td>
                        <td>{{ $action->status->name }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No overdue actions.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
