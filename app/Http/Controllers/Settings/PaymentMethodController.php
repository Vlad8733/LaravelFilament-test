<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:card,paypal',
            'holder_name' => 'required_if:type,card|string|max:255',
            'last_four' => 'required_if:type,card|string|size:4',
            'brand' => 'nullable|string|max:50',
            'expiry_month' => 'required_if:type,card|string|size:2',
            'expiry_year' => 'required_if:type,card|string|size:4',
            'is_default' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $isDefault = $request->boolean('is_default');

        if ($user->paymentMethods()->count() === 0) {
            $isDefault = true;
        }

        if ($isDefault) {
            $user->paymentMethods()->update(['is_default' => false]);
        }

        $mockToken = 'tok_'.bin2hex(random_bytes(12));

        $paymentMethod = $user->paymentMethods()->create([
            'type' => $request->type,
            'provider' => $request->type === 'card' ? 'stripe' : 'paypal',
            'token' => $mockToken,
            'last_four' => $request->last_four,
            'brand' => $request->brand ?? PaymentMethod::detectBrand($request->last_four ?? ''),
            'holder_name' => $request->holder_name,
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
            'is_default' => $isDefault,
        ]);

        activity_log('payment_method_added');

        return response()->json([
            'success' => true,
            'message' => __('settings.payment_method_added'),
            'paymentMethod' => [
                'id' => $paymentMethod->id,
                'type' => $paymentMethod->type,
                'brand_display' => $paymentMethod->brand_display,
                'masked_number' => $paymentMethod->masked_number,
                'expiry_string' => $paymentMethod->expiry_string,
                'is_default' => $paymentMethod->is_default,
            ],
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $this->authorize('update', $paymentMethod);

        $request->validate([
            'holder_name' => 'required|string|max:255',
            'last_four' => 'nullable|string|size:4',
            'brand' => 'nullable|string|max:50',
            'expiry_month' => 'required|string|size:2',
            'expiry_year' => 'required|string|size:4',
        ]);

        $updateData = [
            'holder_name' => $request->holder_name,
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
        ];

        if ($request->last_four && ! str_contains($request->input('card_number', ''), '*')) {
            $updateData['last_four'] = $request->last_four;
            if ($request->brand) {
                $updateData['brand'] = $request->brand;
            }
        }

        $paymentMethod->update($updateData);

        return response()->json([
            'success' => true,
            'message' => __('settings.payment_method_updated'),
        ]);
    }

    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        $this->authorize('delete', $paymentMethod);

        $wasDefault = $paymentMethod->is_default;
        $paymentMethod->delete();

        if ($wasDefault) {
            $newDefault = Auth::user()->paymentMethods()->first();
            $newDefault?->update(['is_default' => true]);
        }

        activity_log('payment_method_deleted');

        return response()->json([
            'success' => true,
            'message' => __('settings.payment_method_deleted'),
        ]);
    }

    public function setDefault(PaymentMethod $paymentMethod): JsonResponse
    {
        $this->authorize('update', $paymentMethod);

        $paymentMethod->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => __('settings.default_payment_method_set'),
        ]);
    }
}
