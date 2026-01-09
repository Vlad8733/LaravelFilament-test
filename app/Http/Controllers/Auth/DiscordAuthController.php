<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class DiscordAuthController extends Controller
{
    /**
     * Redirect to Discord for authentication
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('discord')->redirect();
    }

    /**
     * Handle Discord callback
     */
    public function callback(): RedirectResponse
    {
        try {
            $discordUser = Socialite::driver('discord')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', __('auth.social_auth_failed'));
        }

        // Check if user exists by Discord ID
        $user = User::where('discord_id', $discordUser->getId())->first();

        if ($user) {
            $this->updateSocialAccount($user, $discordUser);
            $this->loginUser($user);

            return redirect()->intended('/');
        }

        // Check if user exists by email
        $user = User::where('email', $discordUser->getEmail())->first();

        if ($user) {
            // Link Discord to existing account
            $user->update([
                'discord_id' => $discordUser->getId(),
                'discord_avatar' => $discordUser->getAvatar(),
            ]);
            $this->updateSocialAccount($user, $discordUser);
            $this->loginUser($user);

            return redirect()->intended('/');
        }

        // Create new user
        $user = User::create([
            'name' => $discordUser->getName() ?? $discordUser->getNickname(),
            'email' => $discordUser->getEmail(),
            'password' => Hash::make(Str::random(24)),
            'discord_id' => $discordUser->getId(),
            'discord_avatar' => $discordUser->getAvatar(),
            'email_verified_at' => now(),
        ]);

        $this->updateSocialAccount($user, $discordUser);
        $this->loginUser($user);

        return redirect()->intended('/');
    }

    /**
     * Update or create social account record
     *
     * @param  \Laravel\Socialite\Two\User  $discordUser
     */
    private function updateSocialAccount(User $user, $discordUser): void
    {
        SocialAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => 'discord',
            ],
            [
                'provider_id' => $discordUser->getId(),
                'provider_email' => $discordUser->getEmail(),
                'provider_avatar' => $discordUser->getAvatar(),
                'token' => $discordUser->token ?? null,
                'refresh_token' => $discordUser->refreshToken ?? null,
            ]
        );
    }

    /**
     * Login user and regenerate session
     */
    private function loginUser(User $user): void
    {
        Auth::login($user, remember: true);
        session()->regenerate();
    }
}
