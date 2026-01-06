<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_name',
        'variant_name',
        'quantity',
        'price',
        'product_price',
        'total',
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'total' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get display name including variant info
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->variant_name) {
            return $this->product_name.' ('.$this->variant_name.')';
        }

        return $this->product_name;
    }
}
