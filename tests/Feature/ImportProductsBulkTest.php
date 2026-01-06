<?php

namespace Tests\Feature;

use App\Jobs\ImportProductsJob;
use App\Models\ImportJob;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportProductsBulkTest extends TestCase
{
    use RefreshDatabase;

    public function test_small_bulk_import()
    {
        Storage::fake('local');
        Storage::fake('public');
        Http::fake();

        // generate 100 small rows to simulate bulk import
        $lines = [];
        $lines[] = 'name,sku,price';
        for ($i = 1; $i <= 100; $i++) {
            $lines[] = "Bulk Product {$i},BULK-".str_pad($i, 3, '0', STR_PAD_LEFT).','.(mt_rand(100, 1000) / 100);
        }

        $csv = implode("\n", $lines)."\n";
        Storage::disk('local')->put('imports/test_bulk.csv', $csv);

        $import = ImportJob::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => null,
            'file_path' => 'imports/test_bulk.csv',
        ]);

        $job = new ImportProductsJob('imports/test_bulk.csv', $import->id);
        $job->handle();

        $this->assertEquals(100, Product::count());
        $this->assertDatabaseHas('import_jobs', ['id' => $import->id, 'status' => 'completed']);
    }
}
