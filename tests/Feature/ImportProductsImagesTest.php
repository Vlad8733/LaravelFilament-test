<?php

namespace Tests\Feature;

use App\Jobs\ImportProductsJob;
use App\Models\ImportJob;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportProductsImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_downloads_and_saves_images()
    {
        Storage::fake('local');
        Storage::fake('public');
        Http::fake([
            '*' => Http::response('image-content', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $csv = "name,sku,images\n".
               "Product Image A,PIA-001,https://example.com/a.jpg\n";

        Storage::disk('local')->put('imports/test_images.csv', $csv);

        $import = ImportJob::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => null,
            'file_path' => 'imports/test_images.csv',
        ]);

        $job = new ImportProductsJob('imports/test_images.csv', $import->id);
        $job->handle();

        $this->assertDatabaseHas('products', ['sku' => 'PIA-001']);

        $product = Product::where('sku', 'PIA-001')->first();
        $this->assertNotNull($product);
        $this->assertGreaterThan(0, $product->images()->count());

        // ensure file was saved to public disk
        $imagePath = $product->images()->first()->image_path;
        $this->assertTrue(Storage::disk('public')->exists($imagePath));
    }
}
