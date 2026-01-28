<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\HasStorageFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory, HasSlug, HasStorageFile;

    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'short_description', 'logo', 'banner',
        'email', 'phone', 'website', 'address', 'city', 'country', 'is_verified', 'is_active',
    ];

    protected $casts = ['is_verified' => 'boolean', 'is_active' => 'boolean'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_follows')->withTimestamps();
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->getStorageUrl('logo');
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->getStorageUrl('banner');
    }

    public function getFollowersCountAttribute(): int
    {
        return $this->followers_count ?? $this->followers()->count();
    }

    public function getProductsCountAttribute(): int
    {
        return $this->active_products_count ?? $this->products()->where('is_active', true)->count();
    }

    public function isFollowedBy(?User $u): bool
    {
        return $u ? $this->followers()->where('user_id', $u->id)->exists() : false;
    }

    public function getUrlAttribute(): string
    {
        return route('companies.show', $this->slug);
    }
}
