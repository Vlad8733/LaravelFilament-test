<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\ImportJob;
use App\Models\Product;
use App\Jobs\ImportProductsJob;

class ImportProductsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_products_job_creates_products_and_updates_counters()
    {
        Storage::fake('local');
        Storage::fake('public');

        // Prevent external HTTP calls for image downloads
        Http::fake();

        $csv = "name,sku,price\n" .
               "Test Product A,TPA-001,12.50\n" .
               "Test Product B,TPB-002,9.99\n";

        Storage::disk('local')->put('imports/test_import.csv', $csv);

        $import = ImportJob::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => null,
            'file_path' => 'imports/test_import.csv',
        ]);

        $job = new ImportProductsJob('imports/test_import.csv', $import->id);
        $job->handle();

        // Final assertions: import job completed and products created

        $this->assertDatabaseHas('import_jobs', [
            'id' => $import->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'TPA-001',
        ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'TPB-002',
        ]);
    }
}
