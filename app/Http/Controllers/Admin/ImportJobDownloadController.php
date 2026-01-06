<?php

namespace App\Http\Controllers\Admin;

use App\Models\ImportJob;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ImportJobDownloadController extends Controller
{
    public function download(ImportJob $import)
    {
        if (! $import->failed_file_path || ! Storage::disk('local')->exists($import->failed_file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($import->failed_file_path);
    }
}
