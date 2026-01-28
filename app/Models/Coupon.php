<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'minimum_amount', 'usage_limit', 'used_count',
        'starts_at', 'expires_at', 'is_active', 'applies_to', 'category_ids', 'product_ids',
    ];

    protected $casts = [
        'value' => 'decimal:2', 'minimum_amount' => 'decimal:2',
        'starts_at' => 'datetime', 'expires_at' => 'datetime',
        'is_active' => 'boolean', 'category_ids' => 'array', 'product_ids' => 'array',
    ];

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4)).'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 4));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $now = Carbon::now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }
        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function appliesTo(Product $p): bool
    {
        if ($this->applies_to === 'all') {
            return true;
        }
        if ($this->applies_to === 'categories' && $this->category_ids) {
            return in_array($p->category_id, $this->category_ids);
        }
        if ($this->applies_to === 'products' && $this->product_ids) {
            return in_array($p->id, $this->product_ids);
        }

        return false;
    }

    public function calculateDiscount(float $amt): float
    {
        return $this->type === 'percentage' ? round($amt * ($this->value / 100), 2) : min($this->value, $amt);
    }

    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
