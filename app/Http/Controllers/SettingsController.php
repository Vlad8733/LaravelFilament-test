<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LoginHistory;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\SocialAccount;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        return view('settings.index', [
            'loginHistories' => $user->loginHistories()->latest('logged_in_at')->limit(10)->get(),
            'addresses' => $user->addresses()->orderByDesc('is_default')->get(),
            'paymentMethods' => $user->paymentMethods()->orderByDesc('is_default')->get(),
            'socialAccounts' => $user->socialAccounts,
            'followedCompanies' => $user->followedCompanies()->withCount('products')->get(),
            'availableProviders' => SocialAccount::availableProviders(),
            'orders' => Order::where('user_id', $user->id)->with('items')->latest()->limit(10)->get(),
        ]);
    }

    public function updateLocale(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:en,ru,lv',
        ]);

        $user = Auth::user();
        $user->locale = $request->locale;
        $user->save();

        App::setLocale($request->locale);
        session(['locale' => $request->locale]);

        return back()->with('success', __('settings.language_updated'));
    }

    // =========================================================
    // PASSWORD
    // =========================================================

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => __('settings.current_password_incorrect'),
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        activity_log('password_changed');

        return response()->json([
            'success' => true,
            'message' => __('settings.password_updated'),
        ]);
    }

    // =========================================================
    // ADDRESSES
    // =========================================================

    public function storeAddress(Request $request)
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

        // If this is first address or marked as default
        if ($user->addresses()->count() === 0) {
            $isDefault = true;
        }

        // Unset other defaults if this one is default
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

    public function updateAddress(Request $request, UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

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

    public function deleteAddress(UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // Set new default if deleted was default
        if ($wasDefault) {
            $newDefault = Auth::user()->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        activity_log('address_deleted');

        return response()->json([
            'success' => true,
            'message' => __('settings.address_deleted'),
        ]);
    }

    public function setDefaultAddress(UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => __('settings.default_address_set'),
        ]);
    }

    // =========================================================
    // PAYMENT METHODS
    // =========================================================

    public function storePaymentMethod(Request $request)
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

        // Note: In production, you would get a token from Stripe/PayPal here
        // We're storing a mock token for demonstration
        $mockToken = 'tok_' . bin2hex(random_bytes(12));

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

    public function updatePaymentMethod(Request $request, PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->user_id !== Auth::id()) {
            abort(403);
        }

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

        // Update card number if provided (not masked)
        if ($request->last_four && !str_contains($request->input('card_number', ''), '*')) {
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

    public function deletePaymentMethod(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->user_id !== Auth::id()) {
            abort(403);
        }

        $wasDefault = $paymentMethod->is_default;
        $paymentMethod->delete();

        if ($wasDefault) {
            $newDefault = Auth::user()->paymentMethods()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        activity_log('payment_method_deleted');

        return response()->json([
            'success' => true,
            'message' => __('settings.payment_method_deleted'),
        ]);
    }

    public function setDefaultPaymentMethod(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->user_id !== Auth::id()) {
            abort(403);
        }

        $paymentMethod->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => __('settings.default_payment_method_set'),
        ]);
    }

    // =========================================================
    // SOCIAL ACCOUNTS
    // =========================================================

    public function unlinkSocialAccount(SocialAccount $socialAccount)
    {
        if ($socialAccount->user_id !== Auth::id()) {
            abort(403);
        }

        $provider = $socialAccount->provider_display;
        $socialAccount->delete();

        activity_log('social_account_unlinked:' . $provider);

        return response()->json([
            'success' => true,
            'message' => __('settings.social_account_unlinked', ['provider' => $provider]),
        ]);
    }

    // =========================================================
    // NEWSLETTER & SUBSCRIPTIONS
    // =========================================================

    public function updateNewsletter(Request $request)
    {
        $user = Auth::user();
        $subscribed = $request->boolean('subscribed');

        $user->update([
            'newsletter_subscribed' => $subscribed,
            'newsletter_subscribed_at' => $subscribed ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $subscribed 
                ? __('settings.newsletter_subscribed') 
                : __('settings.newsletter_unsubscribed'),
        ]);
    }

    public function unfollowCompany(Company $company)
    {
        Auth::user()->followedCompanies()->detach($company->id);

        return response()->json([
            'success' => true,
            'message' => __('settings.company_unfollowed'),
        ]);
    }

    // =========================================================
    // LOGIN HISTORY
    // =========================================================

    public function getLoginHistory()
    {
        $histories = Auth::user()
            ->loginHistories()
            ->latest('logged_in_at')
            ->limit(20)
            ->get()
            ->map(function ($history) {
                return [
                    'id' => $history->id,
                    'device_icon' => $history->device_icon,
                    'browser' => $history->browser,
                    'platform' => $history->platform,
                    'ip_address' => $history->ip_address,
                    'location' => $history->location,
                    'time_ago' => $history->time_ago,
                    'is_current' => $history->ip_address === request()->ip(),
                ];
            });

        return response()->json([
            'success' => true,
            'histories' => $histories,
        ]);
    }
}
