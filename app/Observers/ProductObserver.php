<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Support\Arr;

class ProductObserver
{
    public function created(Product $product)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'created product: ' . $product->id . ' - ' . $product->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'created_at' => now(),
        ]);
    }

    public function updated(Product $product)
    {
        $changes = $product->getChanges();
        // Очищаем служебные поля
        Arr::forget($changes, ['updated_at']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated product: ' . $product->id . ' - changes: ' . json_encode($changes),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'created_at' => now(),
        ]);
    }

    public function deleted(Product $product)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted product: ' . $product->id . ' - ' . $product->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'created_at' => now(),
        ]);
    }
}
