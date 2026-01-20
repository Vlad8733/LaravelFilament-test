<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Service for managing shopping cart operations.
 *
 * Handles adding, updating, removing items and calculating totals.
 */
class CartService
{
    /**
     * Get current cart items for the authenticated user or session
     */
    public function getCartItems(): Collection
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        return CartItem::with(['product.images', 'variant'])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
            ->get();
    }

    /**
     * Get cart items count
     */
    public function getItemCount(): int
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        return CartItem::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
            ->sum('quantity');
    }

    /**
     * Add item to cart or update quantity if already exists
     *
     * @throws \Exception if product is inactive or out of stock
     */
    public function addItem(int $productId, int $quantity = 1, ?int $variantId = null): CartItem
    {
        $product = Product::findOrFail($productId);

        if (! $product->is_active) {
            throw new \Exception(__('cart.product_unavailable'));
        }

        $variant = $variantId ? ProductVariant::find($variantId) : null;
        $availableStock = $this->getAvailableStock($product, $variant);

        if ($availableStock <= 0) {
            throw new \Exception(__('cart.out_of_stock'));
        }

        $userId = Auth::id();
        $sessionId = Session::getId();

        return DB::transaction(function () use ($product, $variant, $quantity, $userId, $sessionId, $availableStock) {
            $existingItem = CartItem::query()
                ->when($userId, fn ($q) => $q->where('user_id', $userId))
                ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
                ->where('product_id', $product->id)
                ->where('variant_id', $variant?->id)
                ->lockForUpdate()
                ->first();

            if ($existingItem) {
                $newQuantity = min($existingItem->quantity + $quantity, $availableStock);
                $existingItem->update(['quantity' => $newQuantity]);

                return $existingItem->fresh();
            }

            return CartItem::create([
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'quantity' => min($quantity, $availableStock),
            ]);
        });
    }

    /**
     * Update cart item quantity
     *
     * @throws \Exception if quantity exceeds available stock
     */
    public function updateQuantity(int $cartItemId, int $quantity): CartItem
    {
        $cartItem = $this->findCartItem($cartItemId);

        if (! $cartItem) {
            throw new \Exception(__('cart.item_not_found'));
        }

        $availableStock = $this->getAvailableStock($cartItem->product, $cartItem->variant);

        if ($quantity > $availableStock) {
            throw new \Exception(__('cart.exceeds_stock', ['available' => $availableStock]));
        }

        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }

        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->fresh();
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $cartItemId): ?CartItem
    {
        $cartItem = $this->findCartItem($cartItemId);

        if ($cartItem) {
            $cartItem->delete();
        }

        return $cartItem;
    }

    /**
     * Clear all items from cart
     */
    public function clearCart(): void
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        CartItem::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
            ->delete();
    }

    /**
     * Calculate cart subtotal
     */
    public function calculateSubtotal(?Collection $items = null): float
    {
        $items = $items ?? $this->getCartItems();

        return $items->sum(function ($item) {
            $price = $item->variant?->price ?? $item->product->getCurrentPrice();

            return $price * $item->quantity;
        });
    }

    /**
     * Merge guest cart into user cart after login
     */
    public function mergeGuestCart(int $userId): void
    {
        $sessionId = Session::getId();

        $guestItems = CartItem::where('session_id', $sessionId)->get();

        if ($guestItems->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($guestItems, $userId) {
            foreach ($guestItems as $guestItem) {
                $existingItem = CartItem::where('user_id', $userId)
                    ->where('product_id', $guestItem->product_id)
                    ->where('variant_id', $guestItem->variant_id)
                    ->first();

                if ($existingItem) {
                    $availableStock = $this->getAvailableStock($guestItem->product, $guestItem->variant);
                    $newQuantity = min($existingItem->quantity + $guestItem->quantity, $availableStock);
                    $existingItem->update(['quantity' => $newQuantity]);
                    $guestItem->delete();
                } else {
                    $guestItem->update([
                        'user_id' => $userId,
                        'session_id' => null,
                    ]);
                }
            }
        });
    }

    /**
     * Validate cart items have sufficient stock
     *
     * @return array Array of validation errors, empty if all valid
     */
    public function validateStock(): array
    {
        $errors = [];
        $items = $this->getCartItems();

        foreach ($items as $item) {
            $availableStock = $this->getAvailableStock($item->product, $item->variant);

            if ($item->quantity > $availableStock) {
                $errors[] = [
                    'item_id' => $item->id,
                    'product' => $item->product->name,
                    'requested' => $item->quantity,
                    'available' => $availableStock,
                ];
            }
        }

        return $errors;
    }

    /**
     * Find cart item belonging to current user/session
     */
    private function findCartItem(int $cartItemId): ?CartItem
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        return CartItem::where('id', $cartItemId)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
            ->first();
    }

    /**
     * Get available stock for a product/variant
     */
    private function getAvailableStock(Product $product, ?ProductVariant $variant): int
    {
        if ($variant) {
            return $variant->stock_quantity ?? 0;
        }

        return $product->stock_quantity ?? 0;
    }
}
