<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartCouponController extends Controller
{
    public function apply(Request $request): JsonResponse
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

            $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty',
                ], 400);
            }

            [$applicableTotal, $applicableItems] = $this->calculateApplicableTotal($cartItems, $coupon);

            if ($applicableTotal == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This coupon does not apply to any items in your cart',
                ], 400);
            }

            if ($coupon->minimum_amount && $applicableTotal < $coupon->minimum_amount) {
                return response()->json([
                    'success' => false,
                    'message' => "Minimum order amount of \${$coupon->minimum_amount} required for this coupon",
                ], 400);
            }

            $discount = $coupon->calculateDiscount($applicableTotal);

            session([
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'discount' => $discount,
                ],
            ]);

            $subtotal = $cartItems->sum(fn ($item) => $item->product->getCurrentPrice() * $item->quantity);
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
            Log::error('Coupon apply error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon',
            ], 500);
        }
    }

    public function remove(): JsonResponse
    {
        session()->forget('coupon');

        $userId = auth()->id();
        $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

        $subtotal = $cartItems->sum(fn ($item) => $item->product->getCurrentPrice() * $item->quantity);

        activity_log('removed_coupon:'.__('activity_log.log.removed_coupon'));

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed',
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'total_formatted' => '$'.number_format($subtotal, 2),
        ]);
    }

    protected function calculateApplicableTotal($cartItems, Coupon $coupon): array
    {
        $applicableTotal = 0;
        $applicableItems = [];

        foreach ($cartItems as $item) {
            if ($coupon->appliesTo($item->product)) {
                $itemTotal = $item->product->getCurrentPrice() * $item->quantity;
                $applicableTotal += $itemTotal;
                $applicableItems[] = $item;
            }
        }

        return [$applicableTotal, $applicableItems];
    }
}
