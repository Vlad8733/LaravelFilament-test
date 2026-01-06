<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    // Статические методы для быстрого доступа
    public static function pending()
    {
        return self::where('slug', 'pending')->first();
    }

    public static function processing()
    {
        return self::where('slug', 'processing')->first();
    }

    public static function shipped()
    {
        return self::where('slug', 'shipped')->first();
    }

    public static function delivered()
    {
        return self::where('slug', 'delivered')->first();
    }

    /**
     * Get translated status name
     */
    public function getTranslatedNameAttribute(): string
    {
        $key = 'order.status_'.str_replace('-', '_', $this->slug);
        $translated = __($key);

        // Если перевод не найден, вернуть оригинал
        return $translated === $key ? $this->name : $translated;
    }

    /**
     * Get translated status description
     */
    public function getTranslatedDescriptionAttribute(): string
    {
        $key = 'order.status_'.str_replace('-', '_', $this->slug).'_desc';
        $translated = __($key);

        // Если перевод не найден, вернуть оригинал
        return $translated === $key ? $this->description : $translated;
    }
}
