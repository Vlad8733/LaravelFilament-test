<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'ip_address', 'user_agent', 'device', 'browser', 'platform', 'location', 'is_successful', 'logged_in_at'];

    protected $casts = ['is_successful' => 'boolean', 'logged_in_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function parseUserAgent(string $ua): array
    {
        $dev = 'desktop';
        $br = 'Unknown';
        $pl = 'Unknown';

        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $ua)) {
            $dev = preg_match('/iPad|Tablet/i', $ua) ? 'tablet' : 'mobile';
        }

        if (preg_match('/Chrome\/[\d.]+/i', $ua) && ! preg_match('/Edg/i', $ua)) {
            $br = 'Chrome';
        } elseif (preg_match('/Firefox\/[\d.]+/i', $ua)) {
            $br = 'Firefox';
        } elseif (preg_match('/Safari\/[\d.]+/i', $ua) && ! preg_match('/Chrome/i', $ua)) {
            $br = 'Safari';
        } elseif (preg_match('/Edg\/[\d.]+/i', $ua)) {
            $br = 'Edge';
        } elseif (preg_match('/OPR\/[\d.]+/i', $ua)) {
            $br = 'Opera';
        }

        if (preg_match('/Windows/i', $ua)) {
            $pl = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS/i', $ua)) {
            $pl = 'macOS';
        } elseif (preg_match('/Linux/i', $ua) && ! preg_match('/Android/i', $ua)) {
            $pl = 'Linux';
        } elseif (preg_match('/Android/i', $ua)) {
            $pl = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            $pl = 'iOS';
        }

        return ['device' => $dev, 'browser' => $br, 'platform' => $pl];
    }

    public static function recordLogin(User $u, ?string $ip = null, ?string $ua = null, bool $ok = true): self
    {
        $p = $ua ? self::parseUserAgent($ua) : ['device' => null, 'browser' => null, 'platform' => null];

        return self::create(['user_id' => $u->id, 'ip_address' => $ip, 'user_agent' => $ua ? substr($ua, 0, 255) : null, 'device' => $p['device'], 'browser' => $p['browser'], 'platform' => $p['platform'], 'is_successful' => $ok, 'logged_in_at' => now()]);
    }

    public function getDeviceIconAttribute(): string
    {
        return match ($this->device) {
            'mobile' => '📱', 'tablet' => '📲', default => '💻'
        };
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->logged_in_at->diffForHumans();
    }
}
