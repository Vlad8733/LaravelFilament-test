<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request, $productId)
    {
        // Найдем продукт или вернем 404
        $product = Product::find($productId);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not available'
            ], 400);
        }

        if (!$product->isInStock()) {
            return response()->json([
                'success' => false,
                'message' => 'Product is out of stock'
            ], 400);
        }

        $cart = session()->get('cart', []);
        $quantity = $request->input('quantity', 1);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $primaryImage = $product->getPrimaryImage();
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->getCurrentPrice(),
                'original_price' => $product->price,
                'quantity' => $quantity,
                'image' => $primaryImage ? $primaryImage->image_path : null,
                'stock_quantity' => $product->stock_quantity,
            ];
        }

        // Проверяем, не превышает ли количество доступный запас
        if ($cart[$product->id]['quantity'] > $product->stock_quantity) {
            $cart[$product->id]['quantity'] = $product->stock_quantity;
        }

        session()->put('cart', $cart);

        $cartCount = array_sum(array_column($cart, 'quantity'));

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!',
            'cartCount' => $cartCount,
            'cartTotal' => array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart))
        ]);
    }

    public function updateQuantity(Request $request, $productId)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $cart = session()->get('cart', []);
        $quantity = (int) $request->input('quantity', 1);

        if ($quantity <= 0) {
            return $this->remove($productId);
        }

        if (isset($cart[$product->id])) {
            if ($quantity > $product->stock_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available'
                ], 400);
            }

            $cart[$product->id]['quantity'] = $quantity;
            session()->put('cart', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully!',
                'cartCount' => array_sum(array_column($cart, 'quantity')),
                'cartTotal' => array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart))
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found in cart'
        ], 404);
    }

    public function remove($productId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Product removed from cart',
                'cartCount' => array_sum(array_column($cart, 'quantity')),
                'cartTotal' => array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart))
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found in cart'
        ], 404);
    }

    public function getCartCount()
    {
        $cart = session()->get('cart', []);
        $count = array_sum(array_column($cart, 'quantity'));
        
        return response()->json(['count' => $count]);
    }

    public function show()
    {
        $cart = session()->get('cart', []);
        $coupon = session()->get('coupon');
        
        $cartTotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));
        $discount = 0;
        
        if ($coupon) {
            $couponModel = Coupon::where('code', $coupon['code'])->first();
            if ($couponModel && $couponModel->isValid($cartTotal)) {
                $discount = $couponModel->calculateDiscount($cartTotal);
            } else {
                session()->forget('coupon');
                $coupon = null;
            }
        }
        
        $finalTotal = max(0, $cartTotal - $discount);
        
        return view('cart.index', compact('cart', 'coupon', 'cartTotal', 'discount', 'finalTotal'));
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);
        $coupon = session()->get('coupon');
        
        if (empty($cart)) {
            return redirect()->route('products.index')->with('error', 'Your cart is empty');
        }

        $cartTotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));
        $discount = 0;
        
        if ($coupon) {
            $couponModel = Coupon::where('code', $coupon['code'])->first();
            if ($couponModel && $couponModel->isValid($cartTotal)) {
                $discount = $couponModel->calculateDiscount($cartTotal);
            } else {
                session()->forget('coupon');
                $coupon = null;
            }
        }
        
        $finalTotal = max(0, $cartTotal - $discount);
        
        return view('checkout.index', compact('cart', 'coupon', 'cartTotal', 'discount', 'finalTotal'));
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string',
            'payment_method' => 'required|string|in:fake,stripe,paypal',
        ]);

        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('products.index')->with('error', 'Your cart is empty');
        }

        $cartTotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));
        $discount = 0;
        $coupon = session()->get('coupon');
        
        if ($coupon) {
            $couponModel = Coupon::where('code', $coupon['code'])->first();
            if ($couponModel && $couponModel->isValid($cartTotal)) {
                $discount = $couponModel->calculateDiscount($cartTotal);
                $couponModel->increment('used_count');
            }
        }
        
        $finalTotal = max(0, $cartTotal - $discount);

        // Создаем заказ
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

        // Создаем элементы заказа
        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['price'] * $item['quantity'],
            ]);

            // Уменьшаем количество товара на складе
            Product::where('id', $item['id'])->decrement('stock_quantity', $item['quantity']);
        }

        // Очищаем корзину и купон
        session()->forget(['cart', 'coupon']);

        return redirect()->route('checkout.success', $order)->with('success', 'Order placed successfully!');
    }

    public function success(Order $order)
    {
        return view('checkout.success', compact('order'));
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty'
            ], 400);
        }

        $coupon = Coupon::where('code', $request->code)->first();
        
        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code'
            ], 404);
        }

        $cartTotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));
        
        if (!$coupon->isValid($cartTotal)) {
            return response()->json([
                'success' => false,
                'message' => $coupon->getValidationMessage($cartTotal)
            ], 400);
        }

        $discount = $coupon->calculateDiscount($cartTotal);
        
        session()->put('coupon', [
            'code' => $coupon->code,
            'discount' => $discount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'discount' => $discount,
            'finalTotal' => max(0, $cartTotal - $discount)
        ]);
    }

    public function removeCoupon()
    {
        session()->forget('coupon');
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully!'
        ]);
    }
}
