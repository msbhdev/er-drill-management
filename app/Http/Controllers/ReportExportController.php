<?php

namespace App\Http\Controllers;

use App\Models\DrillAction;
use App\Models\DrillRecord;
use App\Services\DrillReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __construct(private readonly DrillReportService $reportService)
    {
    }

    public function excel(Request $request): StreamedResponse
    {
        $records = $this->reportService->queryForUser($request->user(), $request->all())->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Reference', 'Rig', 'Date', 'Drill Type', 'Event Type', 'Status', 'Performance Result', 'Location'],
        ]);

        foreach ($records as $index => $record) {
            $sheet->fromArray([
                [
                    $record->reference_no,
                    $record->rig->name,
                    $record->drill_date?->format('Y-m-d'),
                    $record->drillType->name,
                    $record->eventType->name,
                    $record->status->name,
                    $record->performance_standards_met,
                    $record->event_location,
                ],
            ], null, 'A'.($index + 2));
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'er-drill-report.xlsx');
    }

    public function pdf(Request $request)
    {
        $records = $this->reportService->queryForUser($request->user(), $request->all())->get();
        $overdueActions = DrillAction::query()
            ->whereHas('drillRecord', fn ($query) => $query->visibleTo($request->user()))
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereHas('status', fn ($query) => $query->where('code', '!=', 'closed'))
            ->with(['drillRecord.rig', 'status'])
            ->get();

        return Pdf::loadView('reports.export', [
            'records' => $records,
            'overdueActions' => $overdueActions,
            'generatedAt' => $request->user()->formatDateTime(now()),
        ])->download('er-drill-report.pdf');
    }

    public function drillPdf(Request $request, DrillRecord $drillRecord)
    {
        $this->authorize('view', $drillRecord);

        $drillRecord->load([
            'rig',
            'drillType',
            'eventType',
            'status',
            'events',
            'actions.status',
            'attachments',
            'workflowHistory.actor',
            'workflowHistory.fromStatus',
            'workflowHistory.toStatus',
        ]);

        return Pdf::loadView('reports.drill-print', [
            'record' => $drillRecord,
        ])->download("{$drillRecord->reference_no}.pdf");
    }
}
