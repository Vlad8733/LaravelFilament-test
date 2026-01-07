<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_email',
        'provider_avatar',
        'token',
        'refresh_token',
        'token_expires_at',
    ];

    protected $hidden = [
        'token',
        'refresh_token',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get provider icon
     */
    public function getProviderIconAttribute(): string
    {
        return match(strtolower($this->provider)) {
            'google' => '🔴',
            'facebook' => '🔵',
            'github' => '⚫',
            'twitter', 'x' => '🐦',
            'apple' => '🍎',
            default => '🔗',
        };
    }

    /**
     * Get provider display name
     */
    public function getProviderDisplayAttribute(): string
    {
        return match(strtolower($this->provider)) {
            'google' => 'Google',
            'facebook' => 'Facebook',
            'github' => 'GitHub',
            'twitter' => 'Twitter',
            'x' => 'X (Twitter)',
            'apple' => 'Apple',
            default => ucfirst($this->provider),
        };
    }

    /**
     * Check if token is expired
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * Available providers
     */
    public static function availableProviders(): array
    {
        return [
            'google' => [
                'name' => 'Google',
                'icon' => 'google',
                'color' => '#DB4437',
                'enabled' => true,
            ],
            'facebook' => [
                'name' => 'Facebook',
                'icon' => 'facebook',
                'color' => '#4267B2',
                'enabled' => false, // Can be enabled when configured
            ],
            'github' => [
                'name' => 'GitHub',
                'icon' => 'github',
                'color' => '#333333',
                'enabled' => false, // Can be enabled when configured
            ],
        ];
    }

    /**
     * Find or create social account for user
     */
    public static function findOrCreateForUser(User $user, string $provider, array $socialUser): self
    {
        return self::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
            ],
            [
                'provider_id' => $socialUser['id'],
                'provider_email' => $socialUser['email'] ?? null,
                'provider_avatar' => $socialUser['avatar'] ?? null,
                'token' => $socialUser['token'] ?? null,
                'refresh_token' => $socialUser['refresh_token'] ?? null,
                'token_expires_at' => isset($socialUser['expires_in']) 
                    ? now()->addSeconds($socialUser['expires_in']) 
                    : null,
            ]
        );
    }
}
