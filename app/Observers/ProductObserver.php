<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Services\CacheService;
use Illuminate\Support\Arr;

class ProductObserver
{
    public function created(Product $p)
    {
        CacheService::clearProductCache();
        ActivityLog::create([
            'user_id' => auth()->id(), 'action' => 'created product: '.$p->id.' - '.$p->name,
            'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(),
            'subject_type' => Product::class, 'subject_id' => $p->id, 'created_at' => now(),
        ]);
    }

    public function updated(Product $p)
    {
        CacheService::clearProductCache($p->id);
        $ch = $p->getChanges();
        Arr::forget($ch, ['updated_at']);
        ActivityLog::create([
            'user_id' => auth()->id(), 'action' => 'updated product: '.$p->id.' - changes: '.json_encode($ch),
            'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(),
            'subject_type' => Product::class, 'subject_id' => $p->id, 'created_at' => now(),
        ]);
    }

    public function deleted(Product $p)
    {
        CacheService::clearProductCache($p->id);
        ActivityLog::create([
            'user_id' => auth()->id(), 'action' => 'deleted product: '.$p->id.' - '.$p->name,
            'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(),
            'subject_type' => Product::class, 'subject_id' => $p->id, 'created_at' => now(),
        ]);
    }
}
