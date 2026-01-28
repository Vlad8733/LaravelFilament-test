<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Order;
use App\Models\SocialAccount;
use Illuminate\Http\JsonResponse;
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

    public function updateAccount(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_]+$/',
                'unique:users,username,'.$user->id,
            ],
        ], [
            'username.regex' => __('settings.username_invalid_format'),
            'username.unique' => __('settings.username_taken'),
        ]);

        $user->update([
            'username' => $request->username,
        ]);

        activity_log('username_changed');

        return back()->with('success', __('settings.account_updated'));
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
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

    public function unlinkSocialAccount(SocialAccount $socialAccount): JsonResponse
    {
        if ($socialAccount->user_id !== Auth::id()) {
            abort(403);
        }

        $provider = $socialAccount->provider_display;
        $socialAccount->delete();

        activity_log('social_account_unlinked:'.$provider);

        return response()->json([
            'success' => true,
            'message' => __('settings.social_account_unlinked', ['provider' => $provider]),
        ]);
    }

    public function updateNewsletter(Request $request): JsonResponse
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

    public function unfollowCompany(Company $company): JsonResponse
    {
        Auth::user()->followedCompanies()->detach($company->id);

        return response()->json([
            'success' => true,
            'message' => __('settings.company_unfollowed'),
        ]);
    }

    public function getLoginHistory(): JsonResponse
    {
        $histories = Auth::user()
            ->loginHistories()
            ->latest('logged_in_at')
            ->limit(20)
            ->get()
            ->map(fn ($history) => [
                'id' => $history->id,
                'device_icon' => $history->device_icon,
                'browser' => $history->browser,
                'platform' => $history->platform,
                'ip_address' => $history->ip_address,
                'location' => $history->location,
                'time_ago' => $history->time_ago,
                'is_current' => $history->ip_address === request()->ip(),
            ]);

        return response()->json([
            'success' => true,
            'histories' => $histories,
        ]);
    }
}
