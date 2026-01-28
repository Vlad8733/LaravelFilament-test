<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['user_id', 'company_id', 'name', 'slug', 'description', 'long_description', 'price', 'sale_price', 'category_id', 'stock_quantity', 'sku', 'is_featured', 'is_active', 'weight', 'attributes'];

    protected $casts = ['price' => 'decimal:2', 'sale_price' => 'decimal:2', 'weight' => 'decimal:2', 'is_featured' => 'boolean', 'is_active' => 'boolean', 'attributes' => 'array'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($p) => empty($p->sku) ? $p->sku = 'SKU-'.strtoupper(Str::random(8)) : null);
        static::saved(fn ($p) => ! $p->hasPrimaryImage() && $p->images()->count() > 0 ? $p->images()->first()->update(['is_primary' => true]) : null);
    }

    public static function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = $base = Str::slug($name);
        $i = 1;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CustomerReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', 'approved');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('id');
    }

    public function activityLogs()
    {
        return $this->morphMany(\App\Models\ActivityLog::class, 'subject');
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function comparisons(): HasMany
    {
        return $this->hasMany(ProductComparison::class);
    }

    public function belongsToUser(User $u): bool
    {
        return $this->user_id === $u->id;
    }

    public function scopeBySeller($q, User $u)
    {
        return $q->where('user_id', $u->id);
    }

    public function getCurrentPrice()
    {
        return $this->sale_price ?: $this->price;
    }

    public function getDiscountPercentage()
    {
        return (! $this->sale_price || ! $this->price) ? 0 : round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    public function getAverageRatingAttribute(): ?float
    {
        $avg = $this->attributes['approved_reviews_avg_overall_rating'] ?? $this->approvedReviews()->avg('overall_rating');

        return $avg ? round($avg, 1) : null;
    }

    public function getReviewsCountAttribute(): int
    {
        return isset($this->attributes['approved_reviews_count']) ? (int) $this->attributes['approved_reviews_count'] : $this->approvedReviews()->count();
    }

    public function getPrimaryImage()
    {
        if ($this->relationLoaded('images')) {
            return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
        }

        return $this->images()->where('is_primary', true)->first() ?? $this->images()->orderBy('sort_order')->first();
    }

    public function getPrimaryImageUrlAttribute()
    {
        $img = $this->getPrimaryImage();

        return $img ? asset('storage/'.$img->image_path) : asset('images/placeholder.png');
    }

    public function getImageGallery()
    {
        return $this->images()->orderBy('sort_order')->get();
    }

    public function hasPrimaryImage(): bool
    {
        return $this->relationLoaded('images') ? $this->images->contains('is_primary', true) : $this->images()->where('is_primary', true)->exists();
    }

    public function isInStock()
    {
        return $this->stock_quantity > 0;
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeFeatured($q)
    {
        return $q->where('is_featured', true);
    }

    public function scopeInStock($q)
    {
        return $q->where('stock_quantity', '>', 0);
    }
}
