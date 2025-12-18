<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_seller',
        'parent_user_id', // allow filling parent when needed
        'username',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_seller' => 'boolean',
    ];

    public function isSeller(): bool
    {
        return (bool) $this->is_seller;
    }

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Parent (master) account, nullable.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_user_id');
    }

    /**
     * Child accounts of this user (only valid for master users).
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_user_id');
    }

    /**
     * Возвращает количество дочерних аккаунтов.
     */
    public function childrenCount(): int
    {
        // always query DB to get up-to-date number (prevents stale relation)
        return (int) $this->children()->count();
    }

    /**
     * Можно ли создать ещё дочерний аккаунт (максимум 2).
     */
    public function canCreateChild(): bool
    {
        return $this->childrenCount() < 2;
    }

    /**
     * Является ли пользователь мастером (не является дочерним).
     */
    public function isMaster(): bool
    {
        return $this->parent_user_id === null;
    }

    /**
     * Является ли этот пользователь дочерним по отношению к переданному юзеру.
     */
    public function isChildOf(User $user): bool
    {
        return $this->parent_user_id !== null && $this->parent_user_id === $user->id;
    }

    /**
     * Является ли переданный юзер владельцем/мастером для этого аккаунта.
     */
    public function ownedBy(User $user): bool
    {
        return $this->id === $user->id || $this->parent_user_id === $user->id;
    }
}