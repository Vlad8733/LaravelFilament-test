<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $qty = max(1, (int) $request->input('quantity', 1));
        $variantId = $request->input('variant_id');
        $product = Product::findOrFail($productId);

        $cartItemQuery = CartItem::where('user_id', $userId)
            ->where('product_id', $productId);
        if ($variantId) {
            $cartItemQuery->where('variant_id', $variantId);
        } else {
            $cartItemQuery->whereNull('variant_id');
        }
        $cartItem = $cartItemQuery->first();
        // Validate stock availability
        $available = null;
        if ($variantId) {
            $variant = \App\Models\ProductVariant::find($variantId);
            if (! $variant || $variant->product_id != $productId) {
                return response()->json(['success' => false, 'message' => 'Invalid variant'], 400);
            }
            $available = $variant->stock_quantity;
        } else {
            $available = $product->stock_quantity;
        }

        $existingQty = $cartItem ? $cartItem->quantity : 0;
        if (($existingQty + $qty) > $available) {
            return response()->json(['success' => false, 'message' => 'Requested quantity not available'], 400);
        }

        if ($cartItem) {
            $cartItem->quantity += $qty;
            $cartItem->save();
            activity_log('added_to_cart:'.__('activity_log.log.added_to_cart', ['product' => $product->name, 'qty' => $qty]));
        } else {
            // If a cart row exists for this user+product but with different variant (or null),
            // we must handle it because older schema had unique(user_id,product_id).
            $existingAny = CartItem::where('user_id', $userId)->where('product_id', $productId)->first();
            if ($existingAny) {
                // Prefer to merge into existing row: if existing has no variant, set it to the incoming variant.
                // If existing has a different variant, we merge quantities and set variant to the incoming one
                // to reflect the user's latest choice (avoids unique constraint failure).
                $existingAny->quantity += $qty;
                if (is_null($existingAny->variant_id) && $variantId) {
                    $existingAny->variant_id = $variantId;
                } elseif ($existingAny->variant_id && $variantId && $existingAny->variant_id != $variantId) {
                    $existingAny->variant_id = $variantId;
                }
                $existingAny->save();
                activity_log('added_to_cart:'.__('activity_log.log.added_to_cart', ['product' => $product->name, 'qty' => $qty]));
            } else {
                try {
                    CartItem::create([
                        'user_id' => $userId,
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'quantity' => $qty,
                        'session_id' => session()->getId(),
                    ]);
                    activity_log('added_to_cart:'.__('activity_log.log.added_to_cart', ['product' => $product->name, 'qty' => $qty]));
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    // Fallback: merge into any existing row to avoid duplicate key error
                    $fallback = CartItem::where('user_id', $userId)->where('product_id', $productId)->first();
                    if ($fallback) {
                        $fallback->quantity += $qty;
                        if (is_null($fallback->variant_id) && $variantId) {
                            $fallback->variant_id = $variantId;
                        }
                        $fallback->save();
                    } else {
                        // rethrow if somehow still not resolvable
                        throw $e;
                    }
                }
            }
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
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $qty = max(1, (int) $request->input('quantity', 1));
        $cartItem = CartItem::where('id', $itemId)
            ->where('user_id', $userId)
            ->first();
        if (! $cartItem) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }
        $cartItem->quantity = $qty;
        $cartItem->save();
        activity_log('updated_cart:'.__('activity_log.log.updated_cart', ['product' => $cartItem->product->name, 'qty' => $qty]));
        // Пересчитываем итоги
        $cartItems = CartItem::where('user_id', $userId)->with(['product', 'variant'])->get();

        $subtotal = $cartItems->sum(function ($item) {
            $source = $item->variant ?? $item->product;
            $price = $source->sale_price ?? $source->price ?? 0;

            return $price * $item->quantity;
        });

        // Use variant price when present
        $priceSource = $cartItem->variant ?? $cartItem->product;
        $itemSubtotal = ($priceSource->sale_price ?? $priceSource->price) * $qty;

        return response()->json([
            'success' => true,
            'subtotal' => $itemSubtotal,
            'total' => $subtotal,
            'cartCount' => $cartItems->sum('quantity'),
        ]);
    }

    // Remove item from cart
    public function remove(Request $request, $itemId)
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $cartItem = CartItem::where('id', $itemId)
            ->where('user_id', $userId)
            ->first();
        if (! $cartItem) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }
        $productName = $cartItem->product->name;
        $cartItem->delete();
        activity_log('removed_from_cart:'.__('activity_log.log.removed_from_cart', ['product' => $productName]));
        $cartCount = CartItem::where('user_id', $userId)->sum('quantity');

        return response()->json([
            'success' => true,
            'cartCount' => $cartCount,
        ]);
    }

    // Show cart page
    public function show()
    {
        // Можно просто редиректить на index
        return redirect()->route('cart.index');
    }

    /**
     * Показать страницу checkout
     */
    public function checkout()
    {
        $userId = Auth::id();

        $cartItems = CartItem::with(['product.images', 'variant'])
            ->where('user_id', $userId)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        $subtotal = $cartItems->sum(function ($item) {
            $source = $item->variant ?? $item->product;
            $price = $source->sale_price ?? $source->price ?? 0;

            return $price * $item->quantity;
        });

        $discount = 0;
        $total = $subtotal - $discount;

        $cartCount = $cartItems->sum('quantity');

        return view('checkout.index', compact('cartItems', 'subtotal', 'discount', 'total', 'cartCount'));
    }

    // Place order
    public function placeOrder(Request $request)
    {
        $userId = auth()->id();
        // activity_log('Оформил заказ'); // Перемещено ниже, после успешного создания заказа
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'address' => 'required|string',
                'payment_method' => 'required|in:fake',
            ]);

            $userId = auth()->id();
            if (! $userId) {
                return redirect()->route('login');
            }

            // Получаем корзину
            $cartItems = CartItem::with(['product', 'variant'])
                ->where('user_id', $userId)
                ->get();

            if ($cartItems->isEmpty()) {
                return redirect()->route('cart.index')
                    ->with('error', 'Your cart is empty');
            }

            // Подсчёт суммы
            $cartTotal = $cartItems->sum(function ($item) {
                $source = $item->variant ?? $item->product;

                return ($source->sale_price ?? $source->price ?? 0) * $item->quantity;
            });

            // Получаем купон из сессии
            $couponData = session('coupon');
            $discount = 0;
            $couponId = null;
            $couponCode = null;

            if ($couponData) {
                $coupon = Coupon::find($couponData['id']);

                if ($coupon && $coupon->isValid()) {
                    // Пересчитываем скидку для применимых товаров
                    $applicableTotal = 0;
                    foreach ($cartItems as $item) {
                        if ($coupon->appliesTo($item->product)) {
                            $applicableTotal += $item->product->getCurrentPrice() * $item->quantity;
                        }
                    }

                    if ($applicableTotal > 0) {
                        $discount = $coupon->calculateDiscount($applicableTotal);
                        $couponId = $coupon->id;
                        $couponCode = $coupon->code;

                        // Увеличиваем счётчик использований
                        $coupon->incrementUsage();
                    }
                }
            }

            $finalTotal = $cartTotal - $discount;

            // Получаем статус Pending
            $pendingStatus = OrderStatus::where('slug', 'pending')->first();

            // Создаём заказ
            $order = Order::create([
                'order_number' => 'ORD-'.strtoupper(uniqid()),
                'order_status_id' => $pendingStatus ? $pendingStatus->id : null,
                'customer_name' => $request->name,
                'customer_email' => $request->email,
                'shipping_address' => $request->address,
                'notes' => $request->notes,
                'subtotal' => $cartTotal,
                'discount_amount' => $discount,
                'coupon_code' => $couponCode,
                'total' => $finalTotal,
                'payment_method' => 'fake',
                'payment_status' => 'completed',
            ]);

            // Сохраняем ID заказа в сессию
            session(['last_order_id' => $order->id]);

            // Создаём items заказа
            // Сохраняем items заказа и уменьшаем склад
            foreach ($cartItems as $item) {
                $variant = $item->variant;
                $price = $variant ? ($variant->sale_price ?? $variant->price) : $item->product->getCurrentPrice();

                // Build variant name for display
                $variantName = null;
                if ($variant) {
                    $attrs = is_array($variant->attributes)
                        ? collect($variant->attributes)->map(fn ($v, $k) => "$k: $v")->join(', ')
                        : null;
                    $variantName = $attrs ?: $variant->sku;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id ?? null,
                    'product_name' => $item->product->name,
                    'variant_name' => $variantName,
                    'quantity' => $item->quantity,
                    'product_price' => $price,
                    'total' => $price * $item->quantity,
                ]);

                // Уменьшаем stock на уровне варианта или продукта
                try {
                    if ($variant) {
                        $variant->decrement('stock_quantity', $item->quantity);
                    } else {
                        $item->product->decrement('stock_quantity', $item->quantity);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to decrement stock: '.$e->getMessage());
                }
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
                    \Log::warning('Could not create order history: '.$e->getMessage());
                }
            }

            // Сохраняем ID заказа в сессию
            session(['recent_order_id' => $order->id]);

            // Очищаем корзину и купон
            CartItem::where('user_id', $userId)->delete();
            session()->forget('coupon');

            activity_log('placed_order:'.__('activity_log.log.placed_order')); // Перемещено сюда, после успешного создания заказа

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'redirect' => route('checkout.success', $order->id),
            ]);

        } catch (\Exception $e) {
            \Log::error('Order placement error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: '.$e->getMessage(),
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
                'email' => 'Email does not match order email address.',
            ]);
        }

        session(['recent_order_id' => $orderId]);

        return redirect()->route('checkout.success', $orderId);
    }

    public function getCartCount()
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['count' => 0]);
        }

        $count = CartItem::where('user_id', $userId)->sum('quantity');

        return response()->json(['count' => $count]);
    }

    public function index()
    {
        $userId = auth()->id();

        if (! $userId) {
            return redirect()->route('login')->with('error', 'Please login to view your cart.');
        }

        $cartItems = \App\Models\CartItem::with(['product', 'variant'])->where('user_id', $userId)->get();

        $subtotal = $cartItems->sum(function ($item) {
            $source = $item->variant ?? $item->product;
            $price = $source->sale_price ?? $source->price ?? 0;

            return $price * $item->quantity;
        });

        // Получаем купон из сессии
        $coupon = session('coupon');
        $discount = $coupon['discount'] ?? 0;
        $total = $subtotal - $discount;

        return view('cart.index', compact('cartItems', 'subtotal', 'total', 'discount', 'coupon'));
    }

    /**
     * Применить купон к корзине
     */
    public function applyCoupon(Request $request)
    {
        $userId = auth()->id();
        try {
            $request->validate([
                'code' => 'required|string|max:20',
            ]);

            if (! $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to apply coupon',
                ], 401);
            }

            $code = strtoupper(trim($request->code));

            $coupon = Coupon::where('code', $code)->first();

            if (! $coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid coupon code',
                ], 404);
            }

            if (! $coupon->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This coupon is expired or no longer valid',
                ], 400);
            }

            // Получаем корзину
            $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty',
                ], 400);
            }

            // Считаем сумму применимых товаров
            $applicableTotal = 0;
            $applicableItems = [];

            foreach ($cartItems as $item) {
                if ($coupon->appliesTo($item->product)) {
                    $itemTotal = $item->product->getCurrentPrice() * $item->quantity;
                    $applicableTotal += $itemTotal;
                    $applicableItems[] = $item;
                }
            }

            if ($applicableTotal == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This coupon does not apply to any items in your cart',
                ], 400);
            }

            // Проверяем минимальную сумму
            if ($coupon->minimum_amount && $applicableTotal < $coupon->minimum_amount) {
                return response()->json([
                    'success' => false,
                    'message' => "Minimum order amount of \${$coupon->minimum_amount} required for this coupon",
                ], 400);
            }

            // Считаем скидку
            $discount = $coupon->calculateDiscount($applicableTotal);

            // Сохраняем купон в сессию
            session([
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'discount' => $discount,
                ],
            ]);

            // Полная сумма корзины
            $subtotal = $cartItems->sum(function ($item) {
                return $item->product->getCurrentPrice() * $item->quantity;
            });

            $total = $subtotal - $discount;

            activity_log('applied_coupon:'.__('activity_log.log.applied_coupon', ['coupon' => $request->input('code')]));

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully!',
                'coupon' => $coupon->code,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'total' => $total,
                'discount_formatted' => '$'.number_format($discount, 2),
                'total_formatted' => '$'.number_format($total, 2),
            ]);

        } catch (\Exception $e) {
            \Log::error('Coupon apply error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon',
            ], 500);
        }
    }

    /**
     * Удалить купон из корзины
     */
    public function removeCoupon(Request $request)
    {
        $userId = auth()->id();
        session()->forget('coupon');

        $userId = auth()->id();
        $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->getCurrentPrice() * $item->quantity;
        });

        activity_log('removed_coupon:'.__('activity_log.log.removed_coupon'));

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed',
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'total_formatted' => '$'.number_format($subtotal, 2),
        ]);
    }
}
