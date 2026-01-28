<?php

namespace App\Models;

use App\Traits\TwoFactorAuthenticatable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    public const SUPER_ADMIN_EMAIL = 'vladislavperviy0702@gmail.com';

    public const ROLE_USER = 'user';

    public const ROLE_SELLER = 'seller';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SUPER_ADMIN = 'super_admin';

    protected $fillable = ['name', 'email', 'password', 'avatar', 'role', 'is_seller', 'username', 'locale', 'newsletter_subscribed', 'newsletter_subscribed_at'];

    protected $guarded = ['google_id', 'google_avatar', 'github_id', 'github_avatar', 'discord_id', 'discord_avatar'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_seller' => 'boolean', 'two_factor_enabled' => 'boolean', 'two_factor_confirmed_at' => 'datetime', 'newsletter_subscribed' => 'boolean', 'newsletter_subscribed_at' => 'datetime'];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN || $this->email === self::SUPER_ADMIN_EMAIL;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    public function isSeller(): bool
    {
        return $this->role === self::ROLE_SELLER || (bool) $this->is_seller;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function hasRole(string $r): bool
    {
        return $this->isSuperAdmin() || $this->role === $r;
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->isSuperAdmin() || in_array($this->role, $roles);
    }

    public function canBeEditedBy(?User $e): bool
    {
        if ($this->isSuperAdmin()) {
            return $e && $e->id === $this->id;
        }

        return ($e && $e->isAdmin()) || ($e && $e->id === $this->id);
    }

    public function canBeDeleted(): bool
    {
        return ! $this->isSuperAdmin();
    }

    public function canChangeRole(?User $e): bool
    {
        if ($this->isSuperAdmin() || ($e && ! $e->isSuperAdmin())) {
            return false;
        }

        return true;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->isAdmin(), 'seller' => $this->isSeller() || $this->isAdmin(), default => false
        };
    }

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function followedCompanies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_follows')->withTimestamps();
    }

    public function isFollowing(Company $c): bool
    {
        return $this->followedCompanies()->where('company_id', $c->id)->exists();
    }

    public function hasCompany(): bool
    {
        return $this->company()->exists();
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function ticketMessages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function cartItems()
    {
        return $this->hasMany(\App\Models\CartItem::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(\App\Models\WishlistItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CustomerReview::class);
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function defaultAddress(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function defaultPaymentMethod(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PaymentMethod::class)->where('is_default', true);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function bans(): HasMany
    {
        return $this->hasMany(Ban::class);
    }

    public function activeBans(): HasMany
    {
        return $this->hasMany(Ban::class)->where('is_active', true)->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function fingerprints(): HasMany
    {
        return $this->hasMany(UserFingerprint::class);
    }

    public function isBanned(): bool
    {
        return Ban::checkAccountBan($this->id) !== null;
    }

    public function getActiveBan(): ?Ban
    {
        return Ban::checkAccountBan($this->id);
    }

    public function getAvatarUrlAttribute(): string
    {
        return ($this->avatar && \Storage::disk('public')->exists($this->avatar)) ? \Storage::url($this->avatar) : asset('storage/logo/no_avatar.png');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(fn (User $u) => $u->isSuperAdmin() ? throw new \Exception('Супер-админ не может быть удалён.') : null);
        static::updating(function (User $u) {
            if ($u->getOriginal('email') !== self::SUPER_ADMIN_EMAIL) {
                return;
            }
            if ($u->isDirty('email')) {
                throw new \Exception('Email супер-админа не может быть изменён.');
            }
            if ($u->isDirty('role') && $u->role !== self::ROLE_SUPER_ADMIN) {
                throw new \Exception('Роль супер-админа не может быть изменена.');
            }
        });
    }
}
