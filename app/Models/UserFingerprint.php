<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFingerprint extends Model
{
    protected $fillable = [
        'user_id',
        'fingerprint',
        'ip_address',
        'user_agent',
        'components',
        'last_seen_at',
    ];

    protected $casts = [
        'components' => 'array',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function track(
        int $userId,
        string $fingerprint,
        ?string $ip = null,
        ?string $userAgent = null,
        ?array $components = null
    ): self {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'fingerprint' => $fingerprint,
            ],
            [
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'components' => $components,
                'last_seen_at' => now(),
            ]
        );
    }

    public static function findUsersByFingerprint(string $fingerprint): \Illuminate\Support\Collection
    {
        return static::where('fingerprint', $fingerprint)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();
    }

    public static function findByIp(string $ip): \Illuminate\Support\Collection
    {
        return static::where('ip_address', $ip)->get();
    }
}
