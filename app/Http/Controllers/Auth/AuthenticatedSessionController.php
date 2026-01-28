<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $r)
    {
        $creds = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        $u = User::where('email', $creds['email'])->first();

        if (! $u || ! Hash::check($creds['password'], $u->password)) {
            if ($u) {
                LoginHistory::recordLogin($u, $r->ip(), $r->userAgent(), false);
            }
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        if ($u->hasTwoFactorEnabled()) {
            $r->session()->put('2fa:user:id', $u->id);
            $r->session()->put('2fa:user:remember', $r->boolean('remember'));

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($u, $r->boolean('remember'));
        LoginHistory::recordLogin($u, $r->ip(), $r->userAgent(), true);
        $r->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $r)
    {
        Auth::guard('web')->logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect('/');
    }
}
