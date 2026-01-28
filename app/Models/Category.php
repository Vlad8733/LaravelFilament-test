<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\HasStorageFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasSlug, HasStorageFile;

    protected $fillable = ['name', 'slug', 'description', 'image', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->getStorageUrl('image');
    }
}
