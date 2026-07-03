<?php

namespace App\Http\Controllers;

use App\Models\ArchivedDrillRecord;
use App\Models\DrillAction;
use App\Models\DrillRecord;
use App\Services\DrillReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly DrillReportService $reportService) {}

    public function excel(Request $request): StreamedResponse
    {
        $records = $this->reportService->queryForUser($request->user(), $request->all())->get();

        $spreadsheet = new Spreadsheet;
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
                    $record->drillTypeNames(),
                    $record->eventTypeNames(),
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
            'drillTypes',
            'eventTypes',
            'dshas',
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
            'logoPath' => public_path('images/vantris-energy-berhad-logo.png'),
            'attachmentsForPdf' => $this->attachmentsForPdf($drillRecord),
        ])->download("{$drillRecord->reference_no}.pdf");
    }

    public function drillPdfView(Request $request, DrillRecord $drillRecord)
    {
        $this->authorize('view', $drillRecord);

        $drillRecord->load([
            'rig',
            'drillType',
            'eventType',
            'drillTypes',
            'eventTypes',
            'dshas',
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
            'logoPath' => public_path('images/vantris-energy-berhad-logo.png'),
            'attachmentsForPdf' => $this->attachmentsForPdf($drillRecord),
        ])->stream("{$drillRecord->reference_no}.pdf");
    }

    public function archivedDrillPdf(Request $request, ArchivedDrillRecord $archivedDrillRecord)
    {
        abort_unless($request->user()?->isAdministrator(), 403);

        return Pdf::loadView('reports.drill-print', [
            'record' => $archivedDrillRecord->toDrillRecord(),
            'logoPath' => public_path('images/vantris-energy-berhad-logo.png'),
            'attachmentsForPdf' => $this->attachmentsFromSnapshot($archivedDrillRecord->snapshot['attachments'] ?? []),
        ])->stream("{$archivedDrillRecord->reference_no}.pdf");
    }

    private function attachmentsFromSnapshot(array $attachments)
    {
        return collect($attachments)
            ->map(function ($attachment) {
                $filePath = $attachment['file_path'] ?? null;
                $path = $filePath ? Storage::disk('public')->path($filePath) : null;

                return [
                    'caption' => $attachment['caption'] ?? null,
                    'file_name' => $attachment['file_name'] ?? ($filePath ? basename($filePath) : 'attachment'),
                    'preview_path' => $path && file_exists($path) && @getimagesize($path) ? $path : null,
                ];
            })
            ->values();
    }

    private function attachmentsForPdf(DrillRecord $drillRecord)
    {
        return $drillRecord->attachments
            ->map(function ($attachment) {
                $path = Storage::disk('public')->path($attachment->file_path);

                return [
                    'caption' => $attachment->caption,
                    'file_name' => $attachment->file_name ?: basename($attachment->file_path),
                    'preview_path' => file_exists($path) && @getimagesize($path) ? $path : null,
                ];
            })
            ->values();
    }
}
