<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'label' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:2',
            'is_default' => 'boolean',
        ]);

        $user = Auth::user();
        $isDefault = $request->boolean('is_default');

        if ($user->addresses()->count() === 0) {
            $isDefault = true;
        }

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'label' => $request->label,
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'address_line_1' => $request->address_line1,
            'address_line_2' => $request->address_line2,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'is_default' => $isDefault,
        ]);

        activity_log('address_added');

        return response()->json([
            'success' => true,
            'message' => __('settings.address_added'),
            'address' => $address,
        ]);
    }

    public function update(Request $request, UserAddress $address): JsonResponse
    {
        $this->authorize('update', $address);

        $request->validate([
            'label' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:2',
        ]);

        $address->update([
            'label' => $request->label,
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'address_line_1' => $request->address_line1,
            'address_line_2' => $request->address_line2,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('settings.address_updated'),
            'address' => $address->fresh(),
        ]);
    }

    public function destroy(UserAddress $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $newDefault = Auth::user()->addresses()->first();
            $newDefault?->update(['is_default' => true]);
        }

        activity_log('address_deleted');

        return response()->json([
            'success' => true,
            'message' => __('settings.address_deleted'),
        ]);
    }

    public function setDefault(UserAddress $address): JsonResponse
    {
        $this->authorize('update', $address);

        $address->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => __('settings.default_address_set'),
        ]);
    }
}
