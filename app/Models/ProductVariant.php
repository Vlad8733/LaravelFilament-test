<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'sku', 'price', 'sale_price', 'stock_quantity', 'attributes', 'is_default'];

    protected $casts = ['price' => 'decimal:2', 'sale_price' => 'decimal:2', 'stock_quantity' => 'integer', 'attributes' => 'array', 'is_default' => 'boolean'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getDisplayPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }
}
