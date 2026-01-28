<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Collection;

class CouponService
{
    public function validate(string $code, float $subtotal = 0): array
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return [
                'valid' => false,
                'coupon' => null,
                'error' => __('coupons.not_found'),
            ];
        }

        if (! $coupon->is_active) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'error' => __('coupons.inactive'),
            ];
        }

        $now = now();

        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'error' => __('coupons.not_started'),
            ];
        }

        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'error' => __('coupons.expired'),
            ];
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'error' => __('coupons.usage_limit_reached'),
            ];
        }

        if ($coupon->minimum_amount && $subtotal < $coupon->minimum_amount) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'error' => __('coupons.minimum_amount', ['amount' => number_format((float) $coupon->minimum_amount, 2)]),
            ];
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'error' => null,
        ];
    }

    public function calculateDiscount(Coupon $coupon, Collection $cartItems): float
    {
        $applicableTotal = $this->getApplicableTotal($coupon, $cartItems);

        if ($applicableTotal <= 0) {
            return 0;
        }

        return $coupon->calculateDiscount($applicableTotal);
    }

    public function getApplicableTotal(Coupon $coupon, Collection $cartItems): float
    {
        return $cartItems->sum(function ($item) use ($coupon) {
            if (! $coupon->appliesTo($item->product)) {
                return 0;
            }

            $price = $item->variant?->price ?? $item->product->getCurrentPrice();

            return $price * $item->quantity;
        });
    }

    public function getApplicableProducts(Coupon $coupon, Collection $cartItems): Collection
    {
        return $cartItems->filter(fn ($item) => $coupon->appliesTo($item->product));
    }

    public function incrementUsage(Coupon $coupon): bool
    {

        $affected = Coupon::where('id', $coupon->id)
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                    ->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->increment('used_count');

        return $affected > 0;
    }

    public function appliesToProduct(Coupon $coupon, Product $product): bool
    {
        return $coupon->appliesTo($product);
    }
}
