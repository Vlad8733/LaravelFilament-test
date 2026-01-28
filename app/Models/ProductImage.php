<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image_path', 'alt_text', 'is_primary', 'sort_order'];

    protected $casts = ['is_primary' => 'boolean', 'sort_order' => 'integer'];

    protected static function boot()
    {
        parent::boot();
        static::saving(fn ($img) => $img->is_primary ? static::where('product_id', $img->product_id)->where('id', '!=', $img->id)->update(['is_primary' => false]) : null);
        static::deleted(function ($img) {
            if ($img->is_primary) {
                $first = static::where('product_id', $img->product_id)->orderBy('sort_order')->first();
                if ($first) {
                    $first->update(['is_primary' => true]);
                }
            }
            if ($img->image_path && Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : asset('images/no-image.png');
    }

    public function getFullImagePathAttribute(): string
    {
        return Storage::disk('public')->path($this->image_path);
    }
}
