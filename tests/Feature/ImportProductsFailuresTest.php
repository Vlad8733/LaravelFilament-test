<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\ImportJob;
use App\Jobs\ImportProductsJob;

class ImportProductsFailuresTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_records_failed_rows_and_writes_failed_csv()
    {
        Storage::fake('local');
        Storage::fake('public');
        Http::fake();

        // Missing 'name' column value should fail validation
        $csv = "name,sku,price\n" .
               ",FAIL-001,5.00\n";

        Storage::disk('local')->put('imports/test_failures.csv', $csv);

        $import = ImportJob::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => null,
            'file_path' => 'imports/test_failures.csv',
        ]);

        $job = new ImportProductsJob('imports/test_failures.csv', $import->id);
        $job->handle();

        $importRef = \App\Models\ImportJob::find($import->id);
        $this->assertEquals('completed', $importRef->status);
        $this->assertGreaterThan(0, $importRef->failed_count);
        $this->assertNotNull($importRef->failed_file_path);
        $this->assertTrue(Storage::disk('local')->exists($importRef->failed_file_path));
    }
}
