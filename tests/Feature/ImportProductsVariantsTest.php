<?php

namespace Tests\Feature;

use App\Jobs\ImportProductsJob;
use App\Models\ImportJob;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportProductsVariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_creates_variants()
    {
        Storage::fake('local');
        Storage::fake('public');
        Http::fake();

        $csv = "name,sku,variant_sku,variant_price,variant_attributes\n".
               "Variant Product,VP-001,VP-001-RED,15.00,{'color':'red'}\n";

        Storage::disk('local')->put('imports/test_variants.csv', $csv);

        $import = ImportJob::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => null,
            'file_path' => 'imports/test_variants.csv',
        ]);

        $job = new ImportProductsJob('imports/test_variants.csv', $import->id);
        $job->handle();

        $this->assertDatabaseHas('products', ['sku' => 'VP-001']);
        $product = Product::where('sku', 'VP-001')->first();
        $this->assertNotNull($product);
        $this->assertGreaterThan(0, $product->variants()->count());
        $this->assertDatabaseHas('product_variants', ['sku' => 'VP-001-RED']);
    }
}
