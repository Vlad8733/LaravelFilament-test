<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductImportController extends Controller
{
    public function showForm()
    {
        return view('admin.products.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        if (!$request->file('csv_file')->isValid()) {
            return back()->with('error', 'Uploaded file is not valid.');
        }

        // Ensure imports directory exists on local disk
        Storage::disk('local')->makeDirectory('imports');

            // Store on local disk explicitly
            $path = $request->file('csv_file')->store('imports', 'local');

            // create ImportJob record
            $importJob = \App\Models\ImportJob::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => auth()->id() ?? null,
                'file_path' => $path,
                'status' => 'pending',
            ]);

            // dispatch job to process file asynchronously and pass importJob id
            \App\Jobs\ImportProductsJob::dispatch($path, $importJob->id)->onQueue('imports');

            return back()->with('success', 'Import uploaded and queued for processing.')->with('import_job_id', $importJob->id);
    }
}
