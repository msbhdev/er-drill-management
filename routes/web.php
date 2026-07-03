<?php

use App\Http\Controllers\DrillAttachmentDownloadController;
use App\Http\Controllers\ReportExportController;
use App\Livewire\Admin\ArchivedDrillsPage;
use App\Livewire\Admin\SettingsPage;
use App\Livewire\DashboardPage;
use App\Livewire\Drills\EditorPage;
use App\Livewire\Drills\IndexPage;
use App\Livewire\Reports\IndexPage as ReportsIndexPage;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('dashboard', DashboardPage::class)
        ->name('dashboard');

    Route::get('drills', IndexPage::class)
        ->name('drills.index');

    Route::get('drills/create', EditorPage::class)
        ->name('drills.create');

    Route::get('drills/{drillRecord}', EditorPage::class)
        ->name('drills.show');

    Route::get('drills/{drillRecord}/print', [ReportExportController::class, 'drillPdf'])
        ->name('drills.print');

    Route::get('drills/{drillRecord}/view', [ReportExportController::class, 'drillPdfView'])
        ->name('drills.view');

    Route::get('reports', ReportsIndexPage::class)
        ->middleware('role:RM,Management,Administrator')
        ->name('reports.index');

    Route::get('reports/export/excel', [ReportExportController::class, 'excel'])
        ->middleware('role:RM,Management,Administrator')
        ->name('reports.export.excel');

    Route::get('reports/export/pdf', [ReportExportController::class, 'pdf'])
        ->middleware('role:RM,Management,Administrator')
        ->name('reports.export.pdf');

    Route::get('attachments/{drillAttachment}', DrillAttachmentDownloadController::class)
        ->name('attachments.show');

    Route::get('admin/settings', SettingsPage::class)
        ->middleware('role:Administrator')
        ->name('admin.settings');

    Route::get('admin/deleted-drills', ArchivedDrillsPage::class)
        ->middleware('role:Administrator')
        ->name('admin.deleted-drills');
});

require __DIR__.'/auth.php';
