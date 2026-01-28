<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CacheService;

class CategoryObserver
{
    public function created(Category $c): void
    {
        CacheService::clearCategoryCache();
    }

    public function updated(Category $c): void
    {
        CacheService::clearCategoryCache();
    }

    public function deleted(Category $c): void
    {
        CacheService::clearCategoryCache();
    }
}
