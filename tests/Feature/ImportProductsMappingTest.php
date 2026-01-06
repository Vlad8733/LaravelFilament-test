<?php

namespace Tests\Feature;

use App\Jobs\ImportProductsJob;
use App\Models\ImportJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportProductsMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_respects_mapping_from_import_job()
    {
        Storage::fake('local');
        Storage::fake('public');
        Http::fake();

        $csv = "ProductName,ProductSKU,Cost\n".
               "Mapped Product,MAP-001,19.95\n";

        Storage::disk('local')->put('imports/test_mapping.csv', $csv);

        $import = ImportJob::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => null,
            'file_path' => 'imports/test_mapping.csv',
            'mapping' => ['name' => 'ProductName', 'sku' => 'ProductSKU', 'price' => 'Cost'],
        ]);

        $job = new ImportProductsJob('imports/test_mapping.csv', $import->id);
        $job->handle();

        $this->assertDatabaseHas('products', ['sku' => 'MAP-001', 'name' => 'Mapped Product']);
    }
}
