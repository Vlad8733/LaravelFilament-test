<?php

namespace App\Http\Controllers;

use App\Models\Cart;
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
        // Изменили CartItem на Cart
        return Cart::with(['product', 'product.images', 'product.category'])
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

        $cartItem = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $qty;
            $cartItem->save();
        } else {
            Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $qty,
                'session_id' => session()->getId(),
            ]);
        }

        $count = Cart::where('user_id', $userId)->sum('quantity');

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

        $cartItem = Cart::where('id', $itemId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $cartItem->quantity = $qty;
        $cartItem->save();

        $subtotal = $cartItem->product->price * $qty;
        $total = Cart::where('user_id', $userId)->get()->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }

    // Remove item from cart
    public function remove($itemId)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        Cart::where('id', $itemId)
            ->where('user_id', $userId)
            ->delete();

        $count = Cart::where('user_id', $userId)->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Item removed',
            'cartCount' => $count,
        ]);
    }

    // Show cart page
    public function show()
    {
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->route('login');
        }

        $cartItems = $this->dbCartItemsForUser($userId);

        $total = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return view('cart.show', compact('cartItems', 'total'));
    }

    // Show checkout page
    public function checkout()
    {
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->route('login');
        }

        $cartItems = $this->dbCartItemsForUser($userId);

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'Your cart is empty');
        }

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $discount = 0;
        $total = $subtotal - $discount;

        return view('checkout.index', compact('cartItems', 'subtotal', 'discount', 'total'));
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
            $cartItems = Cart::with('product')
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

            // Создаём items заказа
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
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

            // Очищаем корзину
            Cart::where('user_id', $userId)->delete();

            return redirect()->route('checkout.success', $order->id);

        } catch (\Exception $e) {
            Log::error('Order placement failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // Success page
    public function success($orderId)
    {
        try {
            $order = Order::with(['items.product.images', 'status'])
                ->findOrFail($orderId);
            
            // Проверяем доступ
            if (auth()->check()) {
                if (auth()->user()->email !== $order->customer_email) {
                    abort(403, 'Access denied');
                }
            } else {
                $recentOrderId = session('recent_order_id');
                if (!$recentOrderId || $recentOrderId != $orderId) {
                    return redirect()->route('orders.verify', $orderId);
                }
            }
            
            return view('checkout.success', compact('order'));
            
        } catch (\Exception $e) {
            Log::error('Order success page error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Order not found');
        }
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
}
