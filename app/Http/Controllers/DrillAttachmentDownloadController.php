<?php

namespace App\Http\Controllers;

use App\Models\DrillAttachment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class DrillAttachmentDownloadController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(DrillAttachment $drillAttachment)
    {
        $this->authorize('view', $drillAttachment->drillRecord);

        return Storage::disk('public')->download(
            $drillAttachment->file_path,
            $drillAttachment->file_name ?: basename($drillAttachment->file_path)
        );
    }
}
