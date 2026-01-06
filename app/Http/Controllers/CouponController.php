<?php

// filepath: app/Http/Controllers/CouponController.php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCoupon(Request $request) // <-- ПЕРЕИМЕНОВАЛИ
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (! $coupon || ! $coupon->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired coupon code',
            ]);
        }

        $discount = $coupon->calculateDiscount($request->amount);

        return response()->json([
            'valid' => true,
            'discount' => $discount,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'message' => 'Coupon applied! You save $'.number_format($discount, 2),
        ]);
    }
}
