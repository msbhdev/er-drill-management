<?php

namespace App\Http\Controllers;

use App\Models\ArchivedDrillRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivedDrillAttachmentController extends Controller
{
    /**
     * Serve a retained attachment file from a deleted drill's archive snapshot.
     *
     * The attachment row is long gone, so the file is located by its index in
     * the snapshot's attachment list. The path comes from the trusted snapshot
     * (never user input), and access is limited to administrators.
     */
    public function __invoke(Request $request, ArchivedDrillRecord $archivedDrillRecord, int $index)
    {
        abort_unless($request->user()?->isAdministrator(), 403);

        $attachment = ($archivedDrillRecord->snapshot['attachments'] ?? [])[$index] ?? null;
        $filePath = $attachment['file_path'] ?? null;

        abort_unless($filePath && Storage::disk('public')->exists($filePath), 404);

        return Storage::disk('public')->response(
            $filePath,
            $attachment['file_name'] ?: basename($filePath)
        );
    }
}
