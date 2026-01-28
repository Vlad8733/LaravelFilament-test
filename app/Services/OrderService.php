<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function calculateSubtotal(Collection $cartItems): float
    {
        return $cartItems->sum(function ($item) {
            $src = $item->variant ?? $item->product;

            return (float) ($src->sale_price ?? $src->price ?? 0) * $item->quantity;
        });
    }

    public function calculateDiscount(Collection $cartItems, ?Coupon $coupon): float
    {
        if (! $coupon) {
            return 0;
        }
        $subtotal = $this->calculateSubtotal($cartItems);

        return $coupon->type === 'percentage'
            ? round($subtotal * ($coupon->value / 100), 2)
            : min($coupon->value, $subtotal);
    }

    public function createOrder(array $data, Collection $cartItems, ?Coupon $coupon = null): Order
    {
        return DB::transaction(function () use ($data, $cartItems, $coupon) {
            $subtotal = $this->calculateSubtotal($cartItems);
            $discount = $this->calculateDiscount($cartItems, $coupon);
            $status = OrderStatus::pending();

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => auth()->id(),
                'order_status_id' => $status?->id,
                'customer_name' => $data['name'],
                'customer_email' => $data['email'],
                'customer_phone' => $data['phone'] ?? null,
                'shipping_address' => $data['address'],
                'shipping_city' => $data['city'] ?? null,
                'shipping_state' => $data['state'] ?? null,
                'shipping_postal_code' => $data['postal_code'] ?? null,
                'shipping_country' => $data['country'] ?? null,
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => max(0, $subtotal - $discount),
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
            ]);

            $this->createOrderItems($order, $cartItems);
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'order_status_id' => $status?->id,
                'notes' => 'Order placed',
            ]);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            $this->clearCart($cartItems);

            return $order;
        });
    }

    protected function createOrderItems(Order $order, Collection $cartItems): void
    {
        foreach ($cartItems as $i) {
            $src = $i->variant ?? $i->product;
            $p = $src->sale_price ?? $src->price ?? 0;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $i->product_id,
                'product_variant_id' => $i->variant_id,
                'product_name' => $i->product->name,
                'variant_name' => $i->variant?->name,
                'quantity' => $i->quantity,
                'price' => $p,
                'subtotal' => $p * $i->quantity,
            ]);
            $this->decrementStock($i);
        }
    }

    protected function decrementStock(CartItem $i): void
    {
        $i->variant ? $i->variant->decrement('stock_quantity', $i->quantity)
                    : $i->product->decrement('stock_quantity', $i->quantity);
    }

    protected function clearCart(Collection $items): void
    {
        CartItem::whereIn('id', $items->pluck('id'))->delete();
    }

    protected function generateOrderNumber(): string
    {
        do {
            $num = 'ORD-'.strtoupper(Str::random(8));
        } while (Order::where('order_number', $num)->exists());

        return $num;
    }

    public function getCartItems(): Collection
    {
        $uid = auth()->id();
        $sid = session()->getId();

        return CartItem::with(['product.images', 'product.category', 'variant'])
            ->when($uid, fn ($q) => $q->where('user_id', $uid))
            ->when(! $uid, fn ($q) => $q->where('session_id', $sid))
            ->get();
    }

    public function validateCoupon(string $code, float $subtotal): ?Coupon
    {
        $c = Coupon::where('code', $code)->where('is_active', true)->first();
        if (! $c) {
            return null;
        }

        $now = now();
        if ($c->starts_at && $now->lt($c->starts_at)) {
            return null;
        }
        if ($c->expires_at && $now->gt($c->expires_at)) {
            return null;
        }
        if ($c->usage_limit && $c->used_count >= $c->usage_limit) {
            return null;
        }
        if ($c->minimum_amount && $subtotal < $c->minimum_amount) {
            return null;
        }

        return $c;
    }
}
