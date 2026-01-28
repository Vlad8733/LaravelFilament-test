<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function show()
    {
        $uid = Auth::id();
        $items = CartItem::with(['product.images', 'variant'])->where('user_id', $uid)->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        $sub = $this->calculateSubtotal($items);
        $u = Auth::user();

        return view('checkout.index', [
            'cartItems' => $items,
            'subtotal' => $sub,
            'discount' => 0,
            'total' => $sub,
            'cartCount' => $items->sum('quantity'),
            'savedAddresses' => $u->addresses()->orderByDesc('is_default')->get(),
            'savedPaymentMethods' => $u->paymentMethods()->orderByDesc('is_default')->get(),
        ]);
    }

    public function placeOrder(Request $request)
    {
        $uid = auth()->id();
        if (! $uid) {
            return redirect()->route('login');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'address' => 'required|string',
                'payment_method' => 'required|in:fake',
            ]);

            $items = CartItem::with(['product', 'variant'])->where('user_id', $uid)->get();
            if ($items->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Your cart is empty');
            }

            $sub = $this->calculateSubtotal($items);
            [$disc, $cid, $code] = $this->applyCouponDiscount($items);
            $total = $sub - $disc;

            $order = $this->createOrder($request, $sub, $disc, $code, $total, $uid);
            $this->createOrderItems($order, $items);
            $this->createOrderStatusHistory($order, $uid);

            CartItem::where('user_id', $uid)->delete();
            session()->forget('coupon');
            session(['last_order_id' => $order->id, 'recent_order_id' => $order->id]);
            activity_log('placed_order:'.__('activity_log.log.placed_order'));

            return response()->json([
                'success' => true, 'message' => 'Order placed successfully',
                'redirect' => route('checkout.success', $order->id),
            ]);
        } catch (\Exception $e) {
            Log::error('Order placement error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to place order: '.$e->getMessage()], 500);
        }
    }

    public function success($id)
    {
        $order = Order::with(['items.product', 'status'])->findOrFail($id);

        if (auth()->check()) {
            if ($order->customer_email !== auth()->user()->email) {
                abort(403, 'Access Denied');
            }
        } else {
            if (session('last_order_id') != $id) {
                abort(403, 'Access Denied');
            }
        }

        return view('checkout.success', ['order' => $order]);
    }

    public function verifyOrder($id)
    {
        return view('checkout.verify-order', ['order' => Order::findOrFail($id)]);
    }

    public function verifyOrderPost(Request $request, $id)
    {
        $request->validate(['email' => 'required|email']);
        $order = Order::findOrFail($id);

        if ($order->customer_email !== $request->email) {
            return back()->withErrors(['email' => 'Email does not match order email address.']);
        }
        session(['recent_order_id' => $id]);

        return redirect()->route('checkout.success', $id);
    }

    protected function calculateSubtotal($items): float
    {
        return $items->sum(function ($i) {
            $src = $i->variant ?? $i->product;

            return ($src->sale_price ?? $src->price ?? 0) * $i->quantity;
        });
    }

    protected function applyCouponDiscount($items): array
    {
        $data = session('coupon');
        if (! $data) {
            return [0, null, null];
        }

        $c = Coupon::find($data['id']);
        if (! $c || ! $c->isValid()) {
            return [0, null, null];
        }

        $applicable = 0;
        foreach ($items as $i) {
            if ($c->appliesTo($i->product)) {
                $applicable += $i->product->getCurrentPrice() * $i->quantity;
            }
        }

        if ($applicable > 0) {
            $c->incrementUsage();

            return [$c->calculateDiscount($applicable), $c->id, $c->code];
        }

        return [0, null, null];
    }

    protected function createOrder(Request $r, float $sub, float $disc, ?string $code, float $total, int $uid): Order
    {
        $status = OrderStatus::where('slug', 'pending')->first();

        return Order::create([
            'order_number' => 'ORD-'.strtoupper(uniqid()),
            'user_id' => $uid,
            'order_status_id' => $status?->id,
            'customer_name' => $r->name,
            'customer_email' => $r->email,
            'shipping_address' => $r->address,
            'notes' => $r->notes,
            'subtotal' => $sub,
            'discount_amount' => $disc,
            'coupon_code' => $code,
            'total' => $total,
            'payment_method' => 'fake',
            'payment_status' => 'completed',
        ]);
    }

    protected function createOrderItems(Order $order, $items): void
    {
        foreach ($items as $i) {
            $v = $i->variant;
            $price = $v ? ($v->sale_price ?? $v->price) : $i->product->getCurrentPrice();

            $vname = null;
            if ($v) {
                $attrs = is_array($v->attributes) ? collect($v->attributes)->map(fn ($val, $k) => "$k: $val")->join(', ') : null;
                $vname = $attrs ?: $v->sku;
            }

            OrderItem::create([
                'order_id' => $order->id, 'product_id' => $i->product_id, 'variant_id' => $i->variant_id ?? null,
                'product_name' => $i->product->name, 'variant_name' => $vname,
                'quantity' => $i->quantity, 'product_price' => $price, 'total' => $price * $i->quantity,
            ]);

            try {
                $v ? $v->decrement('stock_quantity', $i->quantity) : $i->product->decrement('stock_quantity', $i->quantity);
            } catch (\Exception $e) {
                Log::warning('Failed to decrement stock: '.$e->getMessage());
            }
        }
    }

    protected function createOrderStatusHistory(Order $order, int $uid): void
    {
        $status = OrderStatus::where('slug', 'pending')->first();
        if (! $status) {
            return;
        }

        try {
            OrderStatusHistory::create([
                'order_id' => $order->id, 'order_status_id' => $status->id, 'changed_by' => $uid,
                'notes' => 'Order placed successfully', 'changed_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Could not create order history: '.$e->getMessage());
        }
    }
}
