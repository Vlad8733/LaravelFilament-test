<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductComparison extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get comparison items for current user/session
     */
    public static function getItems()
    {
        $userId = auth()->id();
        $sessionId = session()->getId();

        return static::with(['product.images', 'product.category'])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get count of items in comparison
     */
    public static function getCount(): int
    {
        $userId = auth()->id();
        $sessionId = session()->getId();

        return static::when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
            ->count();
    }

    /**
     * Check if product is in comparison
     */
    public static function hasProduct(int $productId): bool
    {
        $userId = auth()->id();
        $sessionId = session()->getId();

        return static::when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Add product to comparison
     */
    public static function addProduct(int $productId): array
    {
        $userId = auth()->id();
        $sessionId = session()->getId();

        // Check if already exists
        if (static::hasProduct($productId)) {
            return ['success' => false, 'message' => __('compare.already_added')];
        }

        // Limit to 4 products
        $count = static::getCount();
        if ($count >= 4) {
            return ['success' => false, 'message' => __('compare.limit_reached')];
        }

        static::create([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
            'product_id' => $productId,
        ]);

        return ['success' => true, 'message' => __('compare.added'), 'count' => $count + 1];
    }

    /**
     * Remove product from comparison
     */
    public static function removeProduct(int $productId): array
    {
        $userId = auth()->id();
        $sessionId = session()->getId();

        $deleted = static::when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
            ->where('product_id', $productId)
            ->delete();

        return [
            'success' => $deleted > 0,
            'message' => $deleted > 0 ? __('compare.removed') : __('compare.not_found'),
            'count' => static::getCount(),
        ];
    }

    /**
     * Clear all comparison items
     */
    public static function clearAll(): array
    {
        $userId = auth()->id();
        $sessionId = session()->getId();

        static::when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
            ->delete();

        return ['success' => true, 'message' => __('compare.cleared'), 'count' => 0];
    }
}
