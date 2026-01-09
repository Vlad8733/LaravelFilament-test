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

class GitHubAuthController extends Controller
{
    /**
     * Redirect to GitHub OAuth.
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Handle GitHub OAuth callback.
     */
    public function callback(): RedirectResponse
    {
        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Failed to authenticate with GitHub. Please try again.');
        }

        // Check if user exists by github_id
        $user = User::where('github_id', $githubUser->getId())->first();

        if ($user) {
            // Update avatar if changed
            if ($githubUser->getAvatar() && $user->github_avatar !== $githubUser->getAvatar()) {
                $user->update(['github_avatar' => $githubUser->getAvatar()]);
            }

            // Update/create social account record
            $this->updateSocialAccount($user, $githubUser);

            return $this->loginUser($user);
        }

        // Check if user exists by email
        $email = $githubUser->getEmail();
        if ($email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Link GitHub account to existing user
                $user->update([
                    'github_id' => $githubUser->getId(),
                    'github_avatar' => $githubUser->getAvatar(),
                ]);

                // Create social account record
                $this->updateSocialAccount($user, $githubUser);

                return $this->loginUser($user);
            }
        }

        // Create new user
        $user = User::create([
            'name' => $githubUser->getName() ?? $githubUser->getNickname(),
            'email' => $email ?? $githubUser->getId().'@github.local',
            'github_id' => $githubUser->getId(),
            'github_avatar' => $githubUser->getAvatar(),
            'email_verified_at' => $email ? now() : null,
            'password' => null,
        ]);

        // Create social account record
        $this->updateSocialAccount($user, $githubUser);

        return $this->loginUser($user);
    }

    /**
     * Update or create social account record
     */
    protected function updateSocialAccount(User $user, $githubUser): void
    {
        SocialAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => 'github',
            ],
            [
                'provider_id' => $githubUser->getId(),
                'provider_email' => $githubUser->getEmail(),
                'provider_avatar' => $githubUser->getAvatar(),
                'token' => $githubUser->token,
                'refresh_token' => $githubUser->refreshToken,
                'token_expires_at' => null, // GitHub tokens don't expire by default
            ]
        );
    }

    /**
     * Login the user and handle 2FA if enabled.
     */
    protected function loginUser(User $user): RedirectResponse
    {
        // Check if user has 2FA enabled
        if ($user->hasTwoFactorEnabled()) {
            session(['2fa:user:id' => $user->id]);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, remember: true);

        // Record login
        LoginHistory::recordLogin($user, request()->ip(), request()->userAgent(), true);

        return redirect()->intended(route('home'));
    }
}
