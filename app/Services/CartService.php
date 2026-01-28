<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getCartItems(): Collection
    {
        $uid = Auth::id();
        $sid = Session::getId();

        return CartItem::with(['product.images', 'variant'])
            ->when($uid, fn ($q) => $q->where('user_id', $uid))
            ->when(! $uid, fn ($q) => $q->where('session_id', $sid))
            ->get();
    }

    public function getItemCount(): int
    {
        $uid = Auth::id();
        $sid = Session::getId();

        return CartItem::query()
            ->when($uid, fn ($q) => $q->where('user_id', $uid))
            ->when(! $sid, fn ($q) => $q->where('session_id', $sid))
            ->sum('quantity');
    }

    public function addItem(int $productId, int $qty = 1, ?int $variantId = null): CartItem
    {
        $prod = Product::findOrFail($productId);
        if (! $prod->is_active) {
            throw new \Exception(__('cart.product_unavailable'));
        }

        $var = $variantId ? ProductVariant::find($variantId) : null;
        $stock = $this->getAvailableStock($prod, $var);
        if ($stock <= 0) {
            throw new \Exception(__('cart.out_of_stock'));
        }

        $uid = Auth::id();
        $sid = Session::getId();

        return DB::transaction(function () use ($prod, $var, $qty, $uid, $sid, $stock) {
            $existing = CartItem::query()
                ->when($uid, fn ($q) => $q->where('user_id', $uid))
                ->when(! $uid, fn ($q) => $q->where('session_id', $sid))
                ->where('product_id', $prod->id)
                ->where('variant_id', $var?->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->update(['quantity' => min($existing->quantity + $qty, $stock)]);

                return $existing->fresh();
            }

            return CartItem::create([
                'user_id' => $uid,
                'session_id' => $uid ? null : $sid,
                'product_id' => $prod->id,
                'variant_id' => $var?->id,
                'quantity' => min($qty, $stock),
            ]);
        });
    }

    public function updateQuantity(int $id, int $qty): CartItem
    {
        $item = $this->findCartItem($id);
        if (! $item) {
            throw new \Exception(__('cart.item_not_found'));
        }

        $stock = $this->getAvailableStock($item->product, $item->variant);
        if ($qty > $stock) {
            throw new \Exception(__('cart.exceeds_stock', ['available' => $stock]));
        }
        if ($qty <= 0) {
            return $this->removeItem($id);
        }

        $item->update(['quantity' => $qty]);

        return $item->fresh();
    }

    public function removeItem(int $id): ?CartItem
    {
        $item = $this->findCartItem($id);
        if ($item) {
            $item->delete();
        }

        return $item;
    }

    public function clearCart(): void
    {
        $uid = Auth::id();
        $sid = Session::getId();
        CartItem::query()
            ->when($uid, fn ($q) => $q->where('user_id', $uid))
            ->when(! $uid, fn ($q) => $q->where('session_id', $sid))
            ->delete();
    }

    public function calculateSubtotal(?Collection $items = null): float
    {
        $items = $items ?? $this->getCartItems();

        return $items->sum(fn ($i) => ($i->variant?->price ?? $i->product->getCurrentPrice()) * $i->quantity);
    }

    public function mergeGuestCart(int $uid): void
    {
        $sid = Session::getId();
        $guest = CartItem::where('session_id', $sid)->get();
        if ($guest->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($guest, $uid) {
            foreach ($guest as $g) {
                $ex = CartItem::where('user_id', $uid)
                    ->where('product_id', $g->product_id)
                    ->where('variant_id', $g->variant_id)->first();

                if ($ex) {
                    $stock = $this->getAvailableStock($g->product, $g->variant);
                    $ex->update(['quantity' => min($ex->quantity + $g->quantity, $stock)]);
                    $g->delete();
                } else {
                    $g->update(['user_id' => $uid, 'session_id' => null]);
                }
            }
        });
    }

    public function validateStock(): array
    {
        $errs = [];
        foreach ($this->getCartItems() as $i) {
            $stock = $this->getAvailableStock($i->product, $i->variant);
            if ($i->quantity > $stock) {
                $errs[] = ['item_id' => $i->id, 'product' => $i->product->name,
                    'requested' => $i->quantity, 'available' => $stock];
            }
        }

        return $errs;
    }

    private function findCartItem(int $id): ?CartItem
    {
        $uid = Auth::id();
        $sid = Session::getId();

        return CartItem::where('id', $id)
            ->when($uid, fn ($q) => $q->where('user_id', $uid))
            ->when(! $uid, fn ($q) => $q->where('session_id', $sid))->first();
    }

    private function getAvailableStock(Product $p, ?ProductVariant $v): int
    {
        return $v ? ($v->stock_quantity ?? 0) : ($p->stock_quantity ?? 0);
    }
}
