<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $uid = auth()->id();
        if (! $uid) {
            return redirect()->route('login')->with('error', 'Please login to view your cart.');
        }

        $items = CartItem::with(['product', 'variant'])->where('user_id', $uid)->get();
        $subtotal = $this->calculateSubtotal($items);
        $coupon = session('coupon');
        $discount = $coupon['discount'] ?? 0;

        return view('cart.index', [
            'cartItems' => $items,
            'subtotal' => $subtotal,
            'total' => $subtotal - $discount,
            'discount' => $discount,
            'coupon' => $coupon,
        ]);
    }

    public function show()
    {
        return redirect()->route('cart.index');
    }

    public function add(Request $request, int $productId): JsonResponse
    {
        $uid = auth()->id();
        if (! $uid) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $qty = max(1, (int) $request->input('quantity', 1));
        $variantId = $request->input('variant_id');
        $product = Product::findOrFail($productId);

        $available = $this->getAvailableStock($productId, $variantId);
        if ($available === null) {
            return response()->json(['success' => false, 'message' => 'Invalid variant'], 400);
        }

        $existing = $this->findCartItem($uid, $productId, $variantId);
        if ((($existing?->quantity ?? 0) + $qty) > $available) {
            return response()->json(['success' => false, 'message' => 'Requested quantity not available'], 400);
        }

        $this->addOrMergeCartItem($uid, $productId, $variantId, $qty);
        activity_log('added_to_cart:'.__('activity_log.log.added_to_cart', ['product' => $product->name, 'qty' => $qty]));

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cartCount' => CartItem::where('user_id', $uid)->sum('quantity'),
        ]);
    }

    public function update(Request $request, int $itemId): JsonResponse
    {
        $uid = auth()->id();
        if (! $uid) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $qty = max(1, (int) $request->input('quantity', 1));
        $item = CartItem::where('id', $itemId)->where('user_id', $uid)->first();
        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $item->quantity = $qty;
        $item->save();

        activity_log('updated_cart:'.__('activity_log.log.updated_cart', ['product' => $item->product->name, 'qty' => $qty]));

        $cartItems = CartItem::where('user_id', $uid)->with(['product', 'variant'])->get();
        $subtotal = $this->calculateSubtotal($cartItems);

        $src = $item->variant ?? $item->product;
        $itemSubtotal = ($src->sale_price ?? $src->price) * $qty;

        return response()->json([
            'success' => true,
            'subtotal' => $itemSubtotal,
            'total' => $subtotal,
            'cartCount' => $cartItems->sum('quantity'),
        ]);
    }

    public function remove(Request $request, int $itemId): JsonResponse
    {
        $uid = auth()->id();
        if (! $uid) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $item = CartItem::where('id', $itemId)->where('user_id', $uid)->first();
        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $name = $item->product->name;
        $item->delete();

        activity_log('removed_from_cart:'.__('activity_log.log.removed_from_cart', ['product' => $name]));

        return response()->json([
            'success' => true,
            'cartCount' => CartItem::where('user_id', $uid)->sum('quantity'),
        ]);
    }

    public function getCartCount(): JsonResponse
    {
        $uid = auth()->id();
        if (! $uid) {
            return response()->json(['count' => 0]);
        }

        return response()->json(['count' => CartItem::where('user_id', $uid)->sum('quantity')]);
    }

    protected function calculateSubtotal($cartItems): float
    {
        return $cartItems->sum(function ($item) {
            $source = $item->variant ?? $item->product;
            $price = $source->sale_price ?? $source->price ?? 0;

            return $price * $item->quantity;
        });
    }

    protected function getAvailableStock(int $productId, ?int $variantId): ?int
    {
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if (! $variant || $variant->product_id != $productId) {
                return null;
            }

            return $variant->stock_quantity;
        }

        return Product::find($productId)?->stock_quantity;
    }

    protected function findCartItem(int $userId, int $productId, ?int $variantId): ?CartItem
    {
        $query = CartItem::where('user_id', $userId)->where('product_id', $productId);

        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        return $query->first();
    }

    protected function addOrMergeCartItem(int $userId, int $productId, ?int $variantId, int $qty): void
    {
        $existingItem = $this->findCartItem($userId, $productId, $variantId);

        if ($existingItem) {
            $existingItem->quantity += $qty;
            $existingItem->save();

            return;
        }

        $existingAny = CartItem::where('user_id', $userId)->where('product_id', $productId)->first();

        if ($existingAny) {
            $existingAny->quantity += $qty;
            if ($variantId) {
                $existingAny->variant_id = $variantId;
            }
            $existingAny->save();

            return;
        }

        try {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $qty,
                'session_id' => session()->getId(),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            $fallback = CartItem::where('user_id', $userId)->where('product_id', $productId)->first();
            if ($fallback) {
                $fallback->quantity += $qty;
                if ($variantId) {
                    $fallback->variant_id = $variantId;
                }
                $fallback->save();
            } else {
                throw $e;
            }
        }
    }
}
