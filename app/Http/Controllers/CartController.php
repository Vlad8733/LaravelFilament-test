<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CartController extends Controller
{
    protected function dbCartItemsForUser($userId)
    {
        // eager-load relations used in views
        return CartItem::with(['product', 'product.images', 'product.category'])
            ->where('user_id', $userId)
            ->get();
    }

    // Add product to cart (DB-backed)
    public function add(Request $request, $productId)
    {
        $userId = auth()->id();
        if (! $userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $qty = max(1, (int) $request->input('quantity', 1));
        $product = Product::find($productId);
        if (! $product) return response()->json(['success' => false, 'message' => 'Product not found'], 404);

        $item = CartItem::where('user_id', $userId)->where('product_id', $productId)->first();
        if ($item) {
            $newQty = $item->quantity + $qty;
            if (property_exists($product, 'stock_quantity') && $product->stock_quantity !== null) {
                $newQty = min($newQty, $product->stock_quantity);
            }
            $item->quantity = $newQty;
            $item->save();
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => min($qty, $product->stock_quantity ?? $qty),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Added to cart']);
    }

    // Update quantity (DB)
    public function updateQuantity(Request $request, $productId)
    {
        $userId = auth()->id();
        $product = Product::find($productId);
        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $quantity = max(1, (int) $request->input('quantity', 1));
        if ($product->stock_quantity !== null && $quantity > $product->stock_quantity) {
            return response()->json(['success' => false, 'message' => 'Not enough stock available'], 400);
        }

        $item = CartItem::where('user_id', $userId)->where('product_id', $productId)->first();
        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Product not found in cart'], 404);
        }

        $item->quantity = $quantity;
        $item->save();

        // build cart summary
        $items = $this->dbCartItemsForUser($userId);
        $cartCount = $items->sum('quantity');
        $cartTotal = $items->sum(fn($it) => ($it->product->price ?? 0) * $it->quantity);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully!',
            'cartCount' => $cartCount,
            'cartTotal' => $cartTotal,
        ]);
    }

    // Remove item (DB)
    public function remove($productId)
    {
        $userId = auth()->id();
        CartItem::where('user_id', $userId)->where('product_id', $productId)->delete();

        $items = $this->dbCartItemsForUser($userId);
        $cartCount = $items->sum('quantity');
        $cartTotal = $items->sum(fn($it) => ($it->product->price ?? 0) * $it->quantity);

        return response()->json([
            'success' => true,
            'message' => 'Product removed from cart',
            'cartCount' => $cartCount,
            'cartTotal' => $cartTotal
        ]);
    }

    // Count (DB)
    public function getCartCount()
    {
        $userId = auth()->id();
        $count = $userId ? CartItem::where('user_id', $userId)->sum('quantity') : 0;
        return response()->json(['count' => $count]);
    }

    // Show cart page (DB -> view expects same structure as before)
    // helper: robust image URL resolver
    private function resolveImageUrl(mixed $img): ?string
    {
        if (! $img) {
            return null;
        }

        if (is_object($img)) {
            $possible = $img->image_path ?? $img->path ?? $img->file ?? $img->filename ?? $img->name ?? $img->url ?? $img->src ?? $img->image ?? null;
        } elseif (is_array($img)) {
            $possible = $img['image_path'] ?? $img['path'] ?? $img['file'] ?? $img['filename'] ?? $img['name'] ?? $img['url'] ?? $img['src'] ?? $img['image'] ?? null;
        } else {
            $possible = (string) $img;
        }

        if (empty($possible)) {
            return null;
        }

        // If already absolute URL -> return as is
        if (\Illuminate\Support\Str::startsWith($possible, ['http://','https://'])) {
            return $possible;
        }

        // If path starts with leading slash -> strip it for asset()
        $possible = ltrim($possible, '/');

        // If DB stores bare filename, ensure it's under products/
        if (! \Illuminate\Support\Str::contains($possible, '/')) {
            $possible = 'products/' . $possible;
        }

        // Prefer public storage file existence check (optional)
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($possible)) {
            // return relative URL via asset helper (will match current host/port)
            return asset('storage/' . $possible);
        }

        // Fallback to asset path (still relative to current host)
        return asset('storage/' . $possible);
    }

    public function show()
    {
        $userId = auth()->id();
        $items = $this->dbCartItemsForUser($userId);

        $cart = $items->map(function($it){
            $p = $it->product;
            $image = null;
            $images = [];

            if ($p) {
                if (isset($p->images) && $p->images instanceof \Illuminate\Support\Collection && $p->images->isNotEmpty()) {
                    $images = $p->images->map(function($img){
                        return $this->resolveImageUrl($img);
                    })->filter()->values()->toArray();

                    $image = $images[0] ?? null;
                }

                if (! $image) {
                    $image = $this->resolveImageUrl($p->image ?? $p->thumbnail ?? null);
                }
            }

            return [
                'id' => $p->id ?? null,
                'price' => $p->price ?? 0,
                'quantity' => $it->quantity,
                'name' => $p->name ?? '',
                'description' => $p->description ?? null,
                'sku' => $p->sku ?? null,
                'product' => $p,
                'image' => $image,   // full URL or null
                'images' => $images, // array of full URLs
            ];
        })->toArray();

        $coupon = session()->get('coupon', null);
        $cartTotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));
        $discount = 0;
        if ($coupon && is_array($coupon)) { /* ... */ }
        $finalTotal = max(0, $cartTotal - $discount);

        return view('cart.index', compact('cart', 'coupon', 'cartTotal', 'discount', 'finalTotal'));
    }

    // Checkout uses DB cart items
    public function checkout()
    {
        $userId = auth()->id();
        $items = $this->dbCartItemsForUser($userId);
        if ($items->isEmpty()) {
            return redirect()->route('products.index')->with('error', 'Your cart is empty');
        }

        $cart = $items->map(function ($it) {
            $p = $it->product;
            $image = null;
            $images = [];

            if ($p) {
                if (isset($p->images) && $p->images instanceof \Illuminate\Support\Collection && $p->images->isNotEmpty()) {
                    $images = $p->images->map(function($img){
                        return $this->resolveImageUrl($img);
                    })->filter()->values()->toArray();
                    $image = $images[0] ?? null;
                }

                if (! $image) {
                    $image = $this->resolveImageUrl($p->image ?? $p->thumbnail ?? null);
                }
            }

            return [
                'id' => $p->id ?? null,
                'price' => $p->price ?? 0,
                'quantity' => $it->quantity ?? 1,
                'name' => $p->name ?? '',
                'description' => $p->description ?? null,
                'sku' => $p->sku ?? null,
                'product' => $p,
                'image' => $image,
                'images' => $images,
            ];
        })->toArray();

        $cartTotal = array_sum(array_map(fn($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 1), $cart));
        $discount = 0;
        $coupon = session()->get('coupon', null);
        if ($coupon && is_array($coupon)) { /* coupon logic */ }
        $finalTotal = max(0, $cartTotal - $discount);

        return view('checkout.index', compact('cart', 'coupon', 'cartTotal', 'discount', 'finalTotal'));
    }

    // Place order — create order from DB cart items and clear them
    public function placeOrder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string',
            'payment_method' => 'required|string|in:fake,stripe,paypal',
        ]);

        $userId = auth()->id();
        $items = $this->dbCartItemsForUser($userId);
        if ($items->isEmpty()) return redirect()->route('products.index')->with('error', 'Your cart is empty');

        $cartArray = $items->map(function($it){
            $p = $it->product;
            return ['id'=>$p->id,'quantity'=>$it->quantity,'price'=>$p->price ?? 0];
        })->toArray();

        $cartTotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cartArray));
        $discount = 0;
        $coupon = session()->get('coupon');
        if ($coupon) { /* validate and calc discount as before */ }

        $finalTotal = max(0, $cartTotal - $discount);

        // Create order
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'customer_name' => $request->name,
            'customer_email' => $request->email,
            'customer_address' => $request->address,
            'total' => $finalTotal,
            'subtotal' => $cartTotal,
            'discount' => $discount,
            'coupon_code' => $coupon['code'] ?? null,
            'payment_method' => $request->payment_method,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        foreach ($cartArray as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['price'] * $item['quantity'],
            ]);

            Product::where('id', $item['id'])->decrement('stock_quantity', $item['quantity']);
        }

        // Clear DB cart items and coupon session
        CartItem::where('user_id', $userId)->delete();
        session()->forget(['coupon']);

        return redirect()->route('checkout.success', $order)->with('success', 'Order placed successfully!');
    }

    // coupon methods left unchanged (they operate on session)
    public function applyCoupon(Request $request) { /* ...existing code... */ }
    public function removeCoupon() { session()->forget('coupon'); return response()->json(['success'=>true,'message'=>'Coupon removed successfully!']); }
}
