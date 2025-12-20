<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected function dbCartItemsForUser($userId)
    {
        // Было: return Cart::with(['product', ...])
        return CartItem::with(['product', 'product.images', 'product.category'])
            ->where('user_id', $userId)
            ->get();
    }

    // Add product to cart (DB-backed)
    public function add(Request $request, $productId)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $qty = max(1, (int) $request->input('quantity', 1));

        $product = Product::findOrFail($productId);

        $cartItem = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $qty;
            $cartItem->save();
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $qty,
                'session_id' => session()->getId(),
            ]);
        }

        $count = CartItem::where('user_id', $userId)->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cartCount' => $count,
        ]);
    }

    // Update cart item quantity
    public function update(Request $request, $itemId)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $qty = max(1, (int) $request->input('quantity', 1));

        $cartItem = CartItem::where('id', $itemId)
            ->where('user_id', $userId)
            ->first();

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $cartItem->quantity = $qty;
        $cartItem->save();

        // Пересчитываем итоги
        $cartItems = CartItem::where('user_id', $userId)->with('product')->get();
        
        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->getCurrentPrice() * $item->quantity;
        });

        $itemSubtotal = $cartItem->product->getCurrentPrice() * $qty;

        return response()->json([
            'success' => true,
            'subtotal' => $itemSubtotal,
            'total' => $subtotal,
            'cartCount' => $cartItems->sum('quantity')
        ]);
    }

    // Remove item from cart
    public function remove($itemId)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $deleted = CartItem::where('id', $itemId)
            ->where('user_id', $userId)
            ->delete();

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $cartCount = CartItem::where('user_id', $userId)->sum('quantity');

        return response()->json([
            'success' => true,
            'cartCount' => $cartCount
        ]);
    }

    // Show cart page
    public function show()
    {
        // Можно просто редиректить на index
        return redirect()->route('cart.index');
    }

    // Show checkout page
    public function checkout()
    {
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->route('login');
        }

        $cartItems = CartItem::with('product.images')
            ->where('user_id', $userId)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'Your cart is empty');
        }

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $discount = 0;
        $total = $subtotal - $discount;
        
        $cartCount = $cartItems->sum('quantity');

        return view('checkout.index', compact('cartItems', 'subtotal', 'discount', 'total', 'cartCount'));
    }

    // Place order
    public function placeOrder(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'address' => 'required|string',
                'payment_method' => 'required|in:fake',
            ]);

            $userId = auth()->id();
            if (!$userId) {
                return redirect()->route('login');
            }

            // Получаем корзину
            $cartItems = CartItem::with('product')
                ->where('user_id', $userId)
                ->get();

            if ($cartItems->isEmpty()) {
                return redirect()->route('cart.show')
                    ->with('error', 'Your cart is empty');
            }

            // Подсчёт суммы
            $cartTotal = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            $discount = 0;
            $finalTotal = $cartTotal - $discount;

            // Получаем статус Pending
            $pendingStatus = OrderStatus::where('slug', 'pending')->first();

            // Создаём заказ
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'order_status_id' => $pendingStatus ? $pendingStatus->id : null,
                'customer_name' => $request->name,
                'customer_email' => $request->email,
                'shipping_address' => $request->address,
                'notes' => $request->notes,
                'subtotal' => $cartTotal,
                'discount_amount' => $discount,
                'total' => $finalTotal,
                'payment_method' => 'fake',
                'payment_status' => 'completed',
            ]);

            // ДОБАВЬ ЭТУ СТРОКУ:
            session(['last_order_id' => $order->id]);

            // Создаём items заказа
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'product_price' => $item->product->price,  // Оставьте только эту
                    'total' => $item->product->price * $item->quantity,
                ]);
            }

            // Добавляем запись в историю статусов
            if ($pendingStatus) {
                try {
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'order_status_id' => $pendingStatus->id,
                        'changed_by' => $userId,
                        'notes' => 'Order placed successfully',
                        'changed_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Could not create order history: ' . $e->getMessage());
                }
            }

            // Сохраняем ID заказа в сессию
            session(['recent_order_id' => $order->id]);
            session(['last_order_id' => $order->id]);

            // Очищаем корзину
            CartItem::where('user_id', $userId)->delete();

            // ДОЛЖНО БЫТЬ ТАК:
            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'redirect' => route('checkout.success', $order->id)
            ]);

        } catch (\Exception $e) {
            \Log::error('Order placement error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }

    // Success page
    public function success($orderId)
    {
        $order = Order::with(['items.product', 'status'])->findOrFail($orderId);
        
        // Проверка владельца заказа (работает для гостей и авторизованных)
        if (auth()->check()) {
            // Если авторизован - проверяем email
            if ($order->customer_email !== auth()->user()->email) {
                abort(403, 'Access Denied - You don\'t have permission');
            }
        } else {
            // Для гостей - проверяем через сессию или токен
            $sessionOrderId = session('last_order_id');
            if ($sessionOrderId != $orderId) {
                abort(403, 'Access Denied - You don\'t have permission');
            }
        }

        return view('checkout.success', compact('order'));
    }

    // Verify order email
    public function verifyOrder($orderId)
    {
        $order = Order::findOrFail($orderId);
        return view('checkout.verify-order', compact('order'));
    }

    public function verifyOrderPost(Request $request, $orderId)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        
        $order = Order::findOrFail($orderId);
        
        if ($order->customer_email !== $request->email) {
            return back()->withErrors([
                'email' => 'Email does not match order email address.'
            ]);
        }
        
        session(['recent_order_id' => $orderId]);
        
        return redirect()->route('checkout.success', $orderId);
    }

    public function getCartCount()
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['count' => 0]);
        }

        $count = CartItem::where('user_id', $userId)->sum('quantity');

        return response()->json(['count' => $count]);
    }

    public function index()
    {
        // Получаем ID пользователя
        $userId = auth()->id();

        // Если пользователь не авторизован — редирект на логин
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to view your cart.');
        }

        // Получаем товары из корзины
        $cartItems = \App\Models\CartItem::with('product')->where('user_id', $userId)->get();

        // Считаем сумму
        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $total = $subtotal;

        return view('cart.index', compact('cartItems', 'subtotal', 'total'));
    }
}
