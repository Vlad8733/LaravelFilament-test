<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',      // <- добавлено: путь к файлу в storage (если есть колонка)
        'is_seller',
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
}