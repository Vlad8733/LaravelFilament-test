<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleAuthController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Failed to authenticate with Google. Please try again.');
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {

            if ($googleUser->getAvatar() && $user->google_avatar !== $googleUser->getAvatar()) {
                $user->update(['google_avatar' => $googleUser->getAvatar()]);
            }

            $this->updateSocialAccount($user, $googleUser);

            return $this->loginUser($user);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {

            $user->update([
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
            ]);

            $this->updateSocialAccount($user, $googleUser);

            return $this->loginUser($user);
        }

        $user = User::create([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'google_avatar' => $googleUser->getAvatar(),
            'email_verified_at' => now(),
            'password' => null,
        ]);

        $this->updateSocialAccount($user, $googleUser);

        return $this->loginUser($user);
    }

    protected function updateSocialAccount(User $user, $googleUser): void
    {
        SocialAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => 'google',
            ],
            [
                'provider_id' => $googleUser->getId(),
                'provider_email' => $googleUser->getEmail(),
                'provider_avatar' => $googleUser->getAvatar(),
                'token' => $googleUser->token,
                'refresh_token' => $googleUser->refreshToken,
                'token_expires_at' => $googleUser->expiresIn
                    ? now()->addSeconds($googleUser->expiresIn)
                    : null,
            ]
        );
    }

    protected function loginUser(User $user): RedirectResponse
    {

        if ($user->hasTwoFactorEnabled()) {
            session(['2fa:user:id' => $user->id]);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, remember: true);

        LoginHistory::recordLogin($user, request()->ip(), request()->userAgent(), true);

        return redirect()->intended(route('home'));
    }
}
