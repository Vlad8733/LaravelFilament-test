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

    /**
     * Email супер-админа (создатель системы)
     */
    public const SUPER_ADMIN_EMAIL = 'vladislavperviy0702@gmail.com';

    /**
     * Константы ролей
     */
    public const ROLE_USER = 'user';

    public const ROLE_SELLER = 'seller';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SUPER_ADMIN = 'super_admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'is_seller',
        'username',
        'locale',
        'newsletter_subscribed',
        'newsletter_subscribed_at',
    ];

    /**
     * Fields that should only be updated through dedicated methods.
     * Social provider IDs are excluded from fillable for security.
     */
    protected $guarded = [
        'google_id',
        'google_avatar',
        'github_id',
        'github_avatar',
        'discord_id',
        'discord_avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_seller' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'newsletter_subscribed' => 'boolean',
            'newsletter_subscribed_at' => 'datetime',
        ];
    }

    // =========================================================
    // ROLE CHECKING METHODS
    // =========================================================

    /**
     * Проверка: является ли пользователь супер-админом
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN
            || $this->email === self::SUPER_ADMIN_EMAIL;
    }

    /**
     * Проверка: является ли пользователь админом (включая супер-админа)
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Проверка: является ли пользователь продавцом
     */
    public function isSeller(): bool
    {
        return $this->role === self::ROLE_SELLER || (bool) $this->is_seller;
    }

    /**
     * Проверка: обычный пользователь
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Проверка роли
     */
    public function hasRole(string $role): bool
    {
        // Супер-админ имеет все роли
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role === $role;
    }

    /**
     * Проверка нескольких ролей (любая из списка)
     */
    public function hasAnyRole(array $roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($this->role, $roles);
    }

    // =========================================================
    // PROTECTION METHODS
    // =========================================================

    /**
     * Можно ли редактировать этого пользователя
     */
    public function canBeEditedBy(?User $editor): bool
    {
        // Супер-админа может редактировать только он сам
        if ($this->isSuperAdmin()) {
            return $editor && $editor->id === $this->id;
        }

        // Админы могут редактировать всех кроме супер-админа
        if ($editor && $editor->isAdmin()) {
            return true;
        }

        // Пользователь может редактировать только себя
        return $editor && $editor->id === $this->id;
    }

    /**
     * Можно ли удалить этого пользователя
     */
    public function canBeDeleted(): bool
    {
        // Супер-админа нельзя удалить никогда
        return ! $this->isSuperAdmin();
    }

    /**
     * Можно ли изменить роль этого пользователя
     */
    public function canChangeRole(?User $editor): bool
    {
        // Роль супер-админа нельзя изменить
        if ($this->isSuperAdmin()) {
            return false;
        }

        // Только супер-админ может назначать роль admin
        if ($editor && ! $editor->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    // =========================================================
    // FILAMENT ACCESS
    // =========================================================

    /**
     * Определяет доступ к панелям Filament
     */
    public function canAccessPanel(Panel $panel): bool
    {
        $panelId = $panel->getId();

        return match ($panelId) {
            'admin' => $this->isAdmin(), // admin и super_admin
            'seller' => $this->isSeller() || $this->isAdmin(), // seller, admin, super_admin
            default => false,
        };
    }

    // =========================================================
    // EXISTING RELATIONSHIPS
    // =========================================================

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }

    /**
     * Компания пользователя (для продавцов)
     */
    public function company(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Company::class);
    }

    /**
     * Компании, на которые подписан пользователь
     */
    public function followedCompanies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_follows')
            ->withTimestamps();
    }

    /**
     * Проверить, подписан ли пользователь на компанию
     */
    public function isFollowing(Company $company): bool
    {
        return $this->followedCompanies()->where('company_id', $company->id)->exists();
    }

    /**
     * Проверить, есть ли у продавца компания
     */
    public function hasCompany(): bool
    {
        return $this->company()->exists();
    }

    // Note: Profile model not yet implemented
    // public function profile()
    // {
    //     return $this->hasOne(Profile::class);
    // }

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

    /**
     * История входов пользователя
     */
    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    /**
     * Адреса доставки пользователя
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Адрес по умолчанию
     */
    public function defaultAddress(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    /**
     * Способы оплаты пользователя
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Способ оплаты по умолчанию
     */
    public function defaultPaymentMethod(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PaymentMethod::class)->where('is_default', true);
    }

    /**
     * Связанные социальные аккаунты
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Получить URL аватара пользователя или дефолтную заглушку
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && \Storage::disk('public')->exists($this->avatar)) {
            return \Storage::url($this->avatar);
        }

        // Новая заглушка
        return asset('storage/logo/no_avatar.png');
    }

    // =========================================================
    // BOOT METHOD - PROTECTION
    // =========================================================

    protected static function boot()
    {
        parent::boot();

        // Защита от удаления супер-админа
        static::deleting(function (User $user) {
            if ($user->isSuperAdmin()) {
                throw new \Exception('Супер-админ не может быть удалён.');
            }
        });

        // Защита от изменения email супер-админа
        static::updating(function (User $user) {
            if ($user->getOriginal('email') === self::SUPER_ADMIN_EMAIL) {
                // Нельзя менять email супер-админа
                if ($user->isDirty('email')) {
                    throw new \Exception('Email супер-админа не может быть изменён.');
                }
                // Нельзя менять роль супер-админа
                if ($user->isDirty('role') && $user->role !== self::ROLE_SUPER_ADMIN) {
                    throw new \Exception('Роль супер-админа не может быть изменена.');
                }
            }
        });
    }
}
