<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\ImportJob;

class ImportJobDownloadController extends Controller
{
    public function download(ImportJob $import)
    {
        if (!$import->failed_file_path || !Storage::disk('local')->exists($import->failed_file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($import->failed_file_path);
    }
}
