<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    /**
     * Show the 2FA challenge page
     */
    public function show(Request $request)
    {
        if (! $request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.challenge');
    }

    /**
     * Verify the 2FA code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = $request->session()->get('2fa:user:id');
        $remember = $request->session()->get('2fa:user:remember', false);

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);

        if (! $user) {
            $request->session()->forget(['2fa:user:id', '2fa:user:remember']);

            return redirect()->route('login');
        }

        $code = str_replace([' ', '-'], '', $request->code);

        // Try TOTP code first
        if (strlen($code) === 6) {
            $google2fa = app(Google2FA::class);

            if ($google2fa->verifyKey($user->two_factor_secret, $code)) {
                return $this->loginUser($request, $user, $remember);
            }
        }

        // Try recovery code
        if ($user->useRecoveryCode($request->code)) {
            return $this->loginUser($request, $user, $remember);
        }

        return back()->withErrors(['code' => __('The code is invalid.')]);
    }

    /**
     * Login the user after successful 2FA verification
     */
    protected function loginUser(Request $request, $user, bool $remember)
    {
        $request->session()->forget(['2fa:user:id', '2fa:user:remember']);

        Auth::login($user, $remember);
        
        // Record successful login with 2FA
        LoginHistory::recordLogin($user, $request->ip(), $request->userAgent(), true);

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}
