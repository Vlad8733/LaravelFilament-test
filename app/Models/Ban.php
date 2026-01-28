<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ban extends Model
{
    protected $fillable = [
        'type', 'value', 'user_id', 'reason', 'admin_comment', 'public_message',
        'expires_at', 'banned_by', 'is_active', 'unbanned_by', 'unbanned_at', 'unban_reason',
    ];

    protected $casts = ['expires_at' => 'datetime', 'unbanned_at' => 'datetime', 'is_active' => 'boolean'];

    public const REASONS = [
        'spam' => 'Spam / Advertising', 'fraud' => 'Fraud', 'abuse' => 'Abusive Behavior',
        'fake_account' => 'Fake Account', 'multiple_accounts' => 'Multiple Accounts',
        'payment_fraud' => 'Payment Fraud', 'terms_violation' => 'Terms of Service Violation',
        'security_threat' => 'Security Threat', 'bot_activity' => 'Bot Activity', 'other' => 'Other',
    ];

    public const TYPES = ['account' => 'Account Ban', 'ip' => 'IP Ban', 'fingerprint' => 'Device Ban'];

    public const DURATIONS = [
        'permanent' => 'Permanent', '1_hour' => '1 Hour', '6_hours' => '6 Hours', '24_hours' => '24 Hours',
        '3_days' => '3 Days', '7_days' => '7 Days', '14_days' => '14 Days', '30_days' => '30 Days',
        '90_days' => '90 Days', '180_days' => '180 Days', '365_days' => '1 Year', 'custom' => 'Custom',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bannedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function unbannedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unbanned_by');
    }

    public function accessAttempts(): HasMany
    {
        return $this->hasMany(BanAccessAttempt::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopeExpired(Builder $q): Builder
    {
        return $q->where('is_active', true)->whereNotNull('expires_at')->where('expires_at', '<=', now());
    }

    public function scopeByType(Builder $q, string $t): Builder
    {
        return $q->where('type', $t);
    }

    public function scopeForAccount(Builder $q, int $uid): Builder
    {
        return $q->where('type', 'account')->where('user_id', $uid);
    }

    public function scopeForIp(Builder $q, string $ip): Builder
    {
        return $q->where('type', 'ip')->where('value', $ip);
    }

    public function scopeForFingerprint(Builder $q, string $fp): Builder
    {
        return $q->where('type', 'fingerprint')->where('value', $fp);
    }

    public function isExpired(): bool
    {
        if (! $this->is_active) {
            return true;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPermanent(): bool
    {
        return $this->expires_at === null;
    }

    public function getRemainingTime(): ?string
    {
        if ($this->isPermanent()) {
            return 'Permanent';
        }
        if ($this->isExpired()) {
            return 'Expired';
        }

        return $this->expires_at->diffForHumans(['parts' => 2]);
    }

    public function unban(int $by, ?string $reason = null): void
    {
        $this->update(['is_active' => false, 'unbanned_by' => $by, 'unbanned_at' => now(), 'unban_reason' => $reason]);
    }

    public function logAccessAttempt(?int $uid, string $ip, ?string $fp, ?string $ua, ?string $url): void
    {
        $this->accessAttempts()->create([
            'user_id' => $uid, 'ip_address' => $ip, 'fingerprint' => $fp,
            'user_agent' => $ua, 'url' => $url, 'attempted_at' => now(),
        ]);
    }

    public static function checkAccountBan(int $uid): ?self
    {
        return static::active()->forAccount($uid)->first();
    }

    public static function checkIpBan(string $ip): ?self
    {
        return static::active()->forIp($ip)->first();
    }

    public static function checkFingerprintBan(string $fp): ?self
    {
        return static::active()->forFingerprint($fp)->first();
    }

    public static function checkAllBans(?int $uid, string $ip, ?string $fp): ?self
    {
        if ($uid && ($b = static::checkAccountBan($uid))) {
            return $b;
        }
        if ($b = static::checkIpBan($ip)) {
            return $b;
        }
        if ($fp && ($b = static::checkFingerprintBan($fp))) {
            return $b;
        }

        return null;
    }

    public static function banAccount(int $uid, string $reason, ?string $comment = null, ?string $msg = null, ?\DateTime $exp = null, ?int $by = null): self
    {
        return static::create([
            'type' => 'account', 'value' => (string) $uid, 'user_id' => $uid, 'reason' => $reason,
            'admin_comment' => $comment, 'public_message' => $msg, 'expires_at' => $exp, 'banned_by' => $by, 'is_active' => true,
        ]);
    }

    public static function banIp(string $ip, string $reason, ?int $uid = null, ?string $comment = null, ?string $msg = null, ?\DateTime $exp = null, ?int $by = null): self
    {
        return static::create([
            'type' => 'ip', 'value' => $ip, 'user_id' => $uid, 'reason' => $reason,
            'admin_comment' => $comment, 'public_message' => $msg, 'expires_at' => $exp, 'banned_by' => $by, 'is_active' => true,
        ]);
    }

    public static function banFingerprint(string $fp, string $reason, ?int $uid = null, ?string $comment = null, ?string $msg = null, ?\DateTime $exp = null, ?int $by = null): self
    {
        return static::create([
            'type' => 'fingerprint', 'value' => $fp, 'user_id' => $uid, 'reason' => $reason,
            'admin_comment' => $comment, 'public_message' => $msg, 'expires_at' => $exp, 'banned_by' => $by, 'is_active' => true,
        ]);
    }

    public static function deactivateExpiredBans(): int
    {
        return static::expired()->update(['is_active' => false]);
    }
}
