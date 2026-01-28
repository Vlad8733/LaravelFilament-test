<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        if (! $request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.challenge');
    }

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

        if (strlen($code) === 6) {
            $google2fa = app(Google2FA::class);

            if ($google2fa->verifyKey($user->two_factor_secret, $code)) {
                return $this->loginUser($request, $user, $remember);
            }
        }

        if ($user->useRecoveryCode($request->code)) {
            return $this->loginUser($request, $user, $remember);
        }

        return back()->withErrors(['code' => __('The code is invalid.')]);
    }

    protected function loginUser(Request $request, $user, bool $remember)
    {
        $request->session()->forget(['2fa:user:id', '2fa:user:remember']);

        Auth::login($user, $remember);

        LoginHistory::recordLogin($user, $request->ip(), $request->userAgent(), true);

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}
