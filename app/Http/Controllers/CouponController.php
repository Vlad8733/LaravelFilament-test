<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCoupon(Request $r)
    {
        $r->validate(['code' => 'required|string', 'amount' => 'required|numeric|min:0']);
        $c = Coupon::where('code', strtoupper($r->code))->first();
        if (! $c || ! $c->isValid()) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired coupon code']);
        }

        $disc = $c->calculateDiscount($r->amount);

        return response()->json(['valid' => true, 'discount' => $disc, 'type' => $c->type, 'value' => $c->value, 'message' => 'Coupon applied! You save $'.number_format($disc, 2)]);
    }
}
