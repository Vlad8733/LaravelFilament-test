<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        if (! $request->file('csv_file')->isValid()) {
            return back()->with('error', 'Uploaded file is not valid.');
        }

        Storage::disk('local')->makeDirectory('imports');

        $path = $request->file('csv_file')->store('imports', 'local');

        $importJob = \App\Models\ImportJob::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => auth()->id() ?? null,
            'file_path' => $path,
            'status' => 'pending',
        ]);

        \App\Jobs\ImportProductsJob::dispatch($path, $importJob->id)->onQueue('imports');

        return back()->with('success', 'Import uploaded and queued for processing.')->with('import_job_id', $importJob->id);
    }
}
