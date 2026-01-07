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

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find user first to check for 2FA
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            // Log failed login attempt if user exists
            if ($user) {
                LoginHistory::recordLogin($user, $request->ip(), $request->userAgent(), false);
            }
            
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Check if 2FA is enabled
        if ($user->hasTwoFactorEnabled()) {
            // Store user ID in session and redirect to 2FA challenge
            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('2fa:user:remember', $request->boolean('remember'));

            return redirect()->route('two-factor.challenge');
        }

        // No 2FA - log in directly
        Auth::login($user, $request->boolean('remember'));
        
        // Record successful login
        LoginHistory::recordLogin($user, $request->ip(), $request->userAgent(), true);

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
