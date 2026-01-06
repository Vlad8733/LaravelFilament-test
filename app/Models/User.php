<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable;

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
        'parent_user_id',
        'username',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_seller' => 'boolean',
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

    // Note: Profile model not yet implemented
    // public function profile()
    // {
    //     return $this->hasOne(Profile::class);
    // }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_user_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_user_id');
    }

    public function childrenCount(): int
    {
        return (int) $this->children()->count();
    }

    public function canCreateChild(): bool
    {
        return $this->childrenCount() < 2;
    }

    public function isMaster(): bool
    {
        return $this->parent_user_id === null;
    }

    public function isChildOf(User $user): bool
    {
        return $this->parent_user_id !== null && $this->parent_user_id === $user->id;
    }

    public function ownedBy(User $user): bool
    {
        return $this->id === $user->id || $this->parent_user_id === $user->id;
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
