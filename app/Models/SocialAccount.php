<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'provider', 'provider_id', 'provider_email', 'provider_avatar', 'token', 'refresh_token', 'token_expires_at'];

    protected $hidden = ['token', 'refresh_token'];

    protected $casts = ['token_expires_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getProviderIconAttribute(): string
    {
        return match (strtolower($this->provider)) {
            'google' => '🔴', 'facebook' => '🔵', 'github' => '⚫', 'discord' => '🟣', 'twitter', 'x' => '🐦', 'apple' => '🍎', default => '🔗'
        };
    }

    public function getProviderDisplayAttribute(): string
    {
        return match (strtolower($this->provider)) {
            'google' => 'Google', 'facebook' => 'Facebook', 'github' => 'GitHub', 'discord' => 'Discord', 'twitter' => 'Twitter', 'x' => 'X (Twitter)', 'apple' => 'Apple', default => ucfirst($this->provider)
        };
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public static function availableProviders(): array
    {
        return [
            'google' => ['name' => 'Google', 'icon' => 'google', 'color' => '#DB4437', 'enabled' => true],
            'github' => ['name' => 'GitHub', 'icon' => 'github', 'color' => '#333333', 'enabled' => true],
            'discord' => ['name' => 'Discord', 'icon' => 'discord', 'color' => '#5865F2', 'enabled' => true],
        ];
    }

    public static function findOrCreateForUser(User $u, string $prov, array $soc): self
    {
        return self::updateOrCreate(['user_id' => $u->id, 'provider' => $prov], [
            'provider_id' => $soc['id'], 'provider_email' => $soc['email'] ?? null, 'provider_avatar' => $soc['avatar'] ?? null,
            'token' => $soc['token'] ?? null, 'refresh_token' => $soc['refresh_token'] ?? null,
            'token_expires_at' => isset($soc['expires_in']) ? now()->addSeconds($soc['expires_in']) : null,
        ]);
    }
}
