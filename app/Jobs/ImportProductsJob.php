<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\ImportJob;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ImportFinishedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    public int $maxExceptions = 2;

    public int $backoff = 60;

    protected string $path;

    protected int $importJobId;

    public function __construct(string $path, int $importJobId)
    {
        $this->path = $path;
        $this->importJobId = $importJobId;
        $this->queue = 'imports';
    }

    public function handle(): void
    {
        $fullPath = Storage::path($this->path);
        if (! file_exists($fullPath)) {
            return;
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            return;
        }

        $totalLines = 0;
        while (! feof($handle)) {
            $line = fgets($handle);
            if ($line !== false) {
                $totalLines++;
            }
        }

        rewind($handle);
        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return;
        }

        $importRecord = ImportJob::find($this->importJobId);
        $mapping = $importRecord ? ($importRecord->mapping ?? null) : null;

        if (DB::table('import_jobs')->where('id', $this->importJobId)->exists()) {
            Log::info('ImportProductsJob: setting import processing', ['import_id' => $this->importJobId]);
            DB::table('import_jobs')->where('id', $this->importJobId)->update([
                'status' => 'processing',
                'started_at' => now(),
                'total_rows' => max(0, $totalLines - 1),
                'updated_at' => now(),
            ]);
        } else {
            Log::warning('ImportProductsJob: import record not found at start', ['import_id' => $this->importJobId]);
        }

        $buffer = [];
        $bufferSize = 200;
        $rowNum = 1;
        $failed = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $rowAssoc = array_combine($header, $row) ?: [];

            if (is_array($mapping) && count($mapping) > 0) {
                foreach ($mapping as $modelField => $csvHeader) {
                    if (is_string($csvHeader) && array_key_exists($csvHeader, $rowAssoc)) {
                        $rowAssoc[$modelField] = $rowAssoc[$csvHeader];
                    }
                }
            }

            $buffer[] = ['row' => $rowNum, 'data' => $rowAssoc];

            if (count($buffer) >= $bufferSize) {
                $this->processBuffer($buffer, $failed);
                $buffer = [];
            }
        }

        if (! empty($buffer)) {
            $this->processBuffer($buffer, $failed);
        }

        fclose($handle);

        if (DB::table('import_jobs')->where('id', $this->importJobId)->exists()) {
            Log::info('ImportProductsJob: updating import finished', ['import_id' => $this->importJobId, 'rows' => $rowNum - 1, 'failed' => count($failed)]);
            DB::table('import_jobs')->where('id', $this->importJobId)->update([
                'processed_rows' => DB::raw('processed_rows + '.max(0, $rowNum - 1)),
                'failed_count' => DB::raw('failed_count + '.count($failed)),
                'status' => 'completed',
                'finished_at' => now(),
                'updated_at' => now(),
            ]);

            $import = DB::table('import_jobs')->where('id', $this->importJobId)->first();
            if ($import && $import->user_id) {
                $user = User::find($import->user_id);
                if ($user) {
                    $user->notify(new ImportFinishedNotification(
                        $this->importJobId,
                        'completed',
                        max(0, $rowNum - 1),
                        count($failed),
                        $import->failed_file_path ?? null
                    ));
                }
            }
        } else {
            Log::warning('ImportProductsJob: import record not found at finish', ['import_id' => $this->importJobId]);
        }

        if (! empty($failed)) {

            $allKeys = [];
            foreach ($failed as $f) {
                foreach (array_keys($f) as $k) {
                    $allKeys[$k] = true;
                }
            }
            $headers = array_keys($allKeys);

            $fp = fopen('php://temp', 'r+');
            fputcsv($fp, $headers);
            foreach ($failed as $f) {
                $row = [];
                foreach ($headers as $h) {
                    $val = $f[$h] ?? '';
                    if (is_array($val) || is_object($val)) {
                        if ($h === 'errors' && is_array($val)) {
                            $val = implode('; ', $val);
                        } else {
                            $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                        }
                    }
                    $row[] = $val;
                }
                fputcsv($fp, $row);
            }
            rewind($fp);
            $contents = stream_get_contents($fp);
            fclose($fp);

            $failedPath = 'imports/failed_import_'.time().'.csv';
            Storage::disk('local')->put($failedPath, $contents);

            if (DB::table('import_jobs')->where('id', $this->importJobId)->exists()) {
                DB::table('import_jobs')->where('id', $this->importJobId)->update([
                    'failed_file_path' => $failedPath,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    protected function processBuffer(array $buffer, array &$failed): void
    {

        $maybeImport = ImportJob::find($this->importJobId);
        if ($maybeImport && $maybeImport->status === 'cancelled') {

            throw new \RuntimeException('cancelled');
        }

        DB::beginTransaction();
        try {
            foreach ($buffer as $item) {
                $rowNum = $item['row'];
                $data = $item['data'];

                $validator = Validator::make($data, [
                    'name' => 'required|max:255',
                    'slug' => 'nullable|alpha_dash|max:255',
                    'sku' => 'nullable|max:255',
                    'price' => 'nullable|numeric',
                    'sale_price' => 'nullable|numeric',
                    'stock_quantity' => 'nullable|integer',
                    'category' => 'nullable|max:255',
                    'is_active' => 'nullable|in:0,1',
                    'is_featured' => 'nullable|in:0,1',
                ]);

                if ($validator->fails()) {
                    $failed[] = array_merge(['row' => $rowNum, 'errors' => $validator->errors()->all()], $data);

                    continue;
                }

                $categoryId = null;
                if (! empty($data['category'])) {
                    $category = Category::firstOrCreate(
                        ['name' => $data['category']],
                        ['slug' => Str::slug($data['category'])]
                    );
                    $categoryId = $category->id;
                }

                $product = null;
                $skuVal = $data['sku'] ?? null;
                $slugVal = $data['slug'] ?? null;
                $idVal = $data['id'] ?? null;

                if (! empty($skuVal)) {
                    $product = Product::where('sku', $skuVal)->first();
                }
                if (! $product && ! empty($slugVal)) {
                    $product = Product::where('slug', $slugVal)->first();
                }
                if (! $product && ! empty($idVal)) {
                    $product = Product::find($idVal);
                }

                $attributes = [
                    'name' => $data['name'] ?? null,
                    'slug' => ! empty($slugVal) ? $slugVal : Str::slug($data['name'] ?? ''),
                    'sku' => $skuVal ?? null,
                    'price' => $data['price'] ?? null,
                    'sale_price' => $data['sale_price'] ?? null,
                    'stock_quantity' => $data['stock_quantity'] ?? 0,
                    'category_id' => $categoryId,
                    'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
                    'is_featured' => isset($data['is_featured']) ? (bool) $data['is_featured'] : false,
                ];

                try {
                    if ($product) {
                        $product->update($attributes);
                    } else {
                        Product::create($attributes);
                    }
                } catch (\Exception $e) {
                    $failed[] = ['row' => $rowNum, 'errors' => [$e->getMessage()], 'data' => $data];

                    continue;
                }

                $product = Product::where('sku', $attributes['sku'])->orWhere('slug', $attributes['slug'])->first();

                if (! empty($data['images']) && $product) {
                    $urls = preg_split('/[;|,]+/u', $data['images']);
                    $sort = $product->images()->max('sort_order') ?? 0;
                    foreach ($urls as $i => $url) {
                        $url = trim($url);
                        if (empty($url)) {
                            continue;
                        }
                        try {
                            $storedPath = $this->downloadImageForProduct($url, $product->id);
                            if ($storedPath) {
                                $product->images()->create([
                                    'image_path' => $storedPath,
                                    'alt_text' => $product->name,
                                    'sort_order' => ++$sort,
                                    'is_primary' => ($product->images()->count() === 0 && $i === 0),
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::warning('ImportProductsJob: image download failed', ['url' => $url, 'error' => $e->getMessage()]);
                        }
                    }
                }

                if (! empty($data['variant_sku']) || ! empty($data['variant_price']) || ! empty($data['variant_attributes'])) {
                    try {
                        $variantData = [];
                        if (! empty($data['variant_sku'])) {
                            $variantData['sku'] = $data['variant_sku'];
                        }
                        if (! empty($data['variant_price'])) {
                            $variantData['price'] = $data['variant_price'];
                        }
                        if (! empty($data['variant_sale_price'])) {
                            $variantData['sale_price'] = $data['variant_sale_price'];
                        }
                        if (! empty($data['variant_stock_quantity'])) {
                            $variantData['stock_quantity'] = $data['variant_stock_quantity'];
                        }
                        if (! empty($data['variant_attributes'])) {

                            $attrs = $data['variant_attributes'];
                            $parsed = @json_decode($attrs, true);
                            if (is_array($parsed)) {
                                $variantData['attributes'] = $parsed;
                            } else {
                                $pairs = preg_split('/[;|]+/u', $attrs);
                                $map = [];
                                foreach ($pairs as $pair) {
                                    [$k, $v] = array_pad(explode(':', $pair, 2), 2, null);
                                    if ($k !== null) {
                                        $map[trim($k)] = $v !== null ? trim($v) : null;
                                    }
                                }
                                $variantData['attributes'] = $map;
                            }
                        }

                        if (! empty($variantData)) {
                            $variantModel = null;
                            if (! empty($variantData['sku'])) {
                                $variantModel = \App\Models\ProductVariant::where('sku', $variantData['sku'])->first();
                            }
                            if (! $variantModel && $product) {

                                $variantModel = $product->variants()->first();
                            }

                            if ($variantModel) {
                                $variantModel->update($variantData);
                            } elseif ($product) {
                                $product->variants()->create(array_merge($variantData, ['is_default' => ($product->variants()->count() === 0)]));
                            }
                        }
                    } catch (\Exception $e) {
                        $failed[] = ['row' => $rowNum, 'errors' => [$e->getMessage()], 'data' => $data];
                    }
                }
            }

            DB::commit();

            if ($import = ImportJob::find($this->importJobId)) {
                $processed = count($buffer);
                $newFails = 0;
                foreach ($failed as $f) {
                    if (isset($f['row']) && $f['row'] >= ($buffer[0]['row'] ?? 0) && $f['row'] <= ($buffer[count($buffer) - 1]['row'] ?? 0)) {
                        $newFails++;
                    }
                }
                $import->increment('processed_rows', $processed);
                if ($newFails > 0) {
                    $import->increment('failed_count', $newFails);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($buffer as $item) {
                $failed[] = ['row' => $item['row'], 'errors' => [$e->getMessage()], 'data' => $item['data']];
            }
            if ($import = ImportJob::find($this->importJobId)) {
                if (str_contains(strtolower($e->getMessage()), 'cancel')) {
                    $import->update(['status' => 'cancelled', 'finished_at' => now()]);
                } else {
                    $import->update(['status' => 'failed', 'finished_at' => now()]);
                }
            }
        }
    }

    protected function downloadImageForProduct(string $url, int $productId): ?string
    {
        try {
            $resp = Http::timeout(10)->get($url);
            if (! $resp->ok()) {
                return null;
            }

            $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';
            $filename = 'products/'.$productId.'/'.uniqid().'.'.$ext;
            Storage::disk('public')->put($filename, $resp->body());

            return $filename;
        } catch (\Exception $e) {
            Log::warning('ImportProductsJob: image download exception', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('ImportProductsJob failed', [
            'import_id' => $this->importJobId,
            'path' => $this->path,
            'error' => $exception->getMessage(),
        ]);

        if ($import = ImportJob::find($this->importJobId)) {
            $import->update([
                'status' => 'failed',
                'finished_at' => now(),
            ]);

            if ($import->user_id && $user = User::find($import->user_id)) {
                $user->notify(new ImportFinishedNotification(
                    $this->importJobId,
                    'failed',
                    0,
                    0,
                    null
                ));
            }
        }
    }
}
