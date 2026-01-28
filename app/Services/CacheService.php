<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    public const TTL_SHORT = 300;

    public const TTL_MEDIUM = 1800;

    public const TTL_LONG = 3600;

    public const TTL_DAY = 86400;

    public const PREFIX_CATEGORIES = 'categories';

    public const PREFIX_PRODUCTS = 'products';

    public const PREFIX_FEATURED = 'featured';

    public const PREFIX_STATS = 'stats';

    public static function getActiveCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            self::PREFIX_CATEGORIES.':active',
            self::TTL_MEDIUM,
            fn () => Category::where('is_active', true)
                ->withCount('products')
                ->orderBy('name')
                ->get()
        );
    }

    public static function getCategoriesForMenu(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            self::PREFIX_CATEGORIES.':menu',
            self::TTL_LONG,
            fn () => Category::where('is_active', true)
                ->select('id', 'name', 'slug', 'image')
                ->orderBy('name')
                ->get()
        );
    }

    public static function getFeaturedProducts(int $limit = 8): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            self::PREFIX_FEATURED.":products:{$limit}",
            self::TTL_MEDIUM,
            fn () => Product::with(['category', 'images'])
                ->active()
                ->where('is_featured', true)
                ->latest()
                ->take($limit)
                ->get()
        );
    }

    public static function getNewArrivals(int $limit = 8): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            self::PREFIX_PRODUCTS.":new:{$limit}",
            self::TTL_MEDIUM,
            fn () => Product::with(['category', 'images'])
                ->active()
                ->latest()
                ->take($limit)
                ->get()
        );
    }

    public static function getOnSaleProducts(int $limit = 8): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            self::PREFIX_PRODUCTS.":sale:{$limit}",
            self::TTL_MEDIUM,
            fn () => Product::with(['category', 'images'])
                ->active()
                ->whereNotNull('sale_price')
                ->where('sale_price', '>', 0)
                ->latest()
                ->take($limit)
                ->get()
        );
    }

    public static function getPopularProducts(int $limit = 8): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            self::PREFIX_PRODUCTS.":popular:{$limit}",
            self::TTL_LONG,
            fn () => Product::with(['category', 'images'])
                ->active()
                ->withCount('orderItems')
                ->orderByDesc('order_items_count')
                ->take($limit)
                ->get()
        );
    }

    public static function getProduct(int $productId): ?Product
    {
        return Cache::remember(
            self::PREFIX_PRODUCTS.":single:{$productId}",
            self::TTL_SHORT,
            fn () => Product::with(['category', 'images', 'variants'])
                ->find($productId)
        );
    }

    public static function getProductBySlug(string $slug): ?Product
    {
        return Cache::remember(
            self::PREFIX_PRODUCTS.":slug:{$slug}",
            self::TTL_SHORT,
            fn () => Product::with(['category', 'images', 'variants'])
                ->where('slug', $slug)
                ->first()
        );
    }

    public static function getProductsByCategory(int $categoryId, int $limit = 12): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            self::PREFIX_PRODUCTS.":category:{$categoryId}:{$limit}",
            self::TTL_MEDIUM,
            fn () => Product::with(['images'])
                ->active()
                ->where('category_id', $categoryId)
                ->latest()
                ->take($limit)
                ->get()
        );
    }

    public static function getHomepageStats(): array
    {
        return Cache::remember(
            self::PREFIX_STATS.':homepage',
            self::TTL_LONG,
            fn () => [
                'total_products' => Product::active()->count(),
                'total_categories' => Category::where('is_active', true)->count(),
                'products_on_sale' => Product::active()->whereNotNull('sale_price')->count(),
            ]
        );
    }

    public static function clearProductCache(?int $productId = null): void
    {
        if ($productId) {
            Cache::forget(self::PREFIX_PRODUCTS.":single:{$productId}");

            $product = Product::find($productId);
            if ($product) {
                Cache::forget(self::PREFIX_PRODUCTS.":slug:{$product->slug}");
            }
        }

        Cache::forget(self::PREFIX_FEATURED.':products:8');
        Cache::forget(self::PREFIX_PRODUCTS.':new:8');
        Cache::forget(self::PREFIX_PRODUCTS.':sale:8');
        Cache::forget(self::PREFIX_PRODUCTS.':popular:8');
        Cache::forget(self::PREFIX_STATS.':homepage');
    }

    public static function clearCategoryCache(): void
    {
        Cache::forget(self::PREFIX_CATEGORIES.':active');
        Cache::forget(self::PREFIX_CATEGORIES.':menu');
        Cache::forget(self::PREFIX_STATS.':homepage');
    }

    public static function clearAll(): void
    {
        self::clearProductCache();
        self::clearCategoryCache();
    }
}
