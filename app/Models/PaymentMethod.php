<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use App\Traits\HasDefaultItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use BelongsToUser, HasDefaultItem, HasFactory;

    protected $fillable = ['user_id', 'type', 'provider', 'token', 'last_four', 'brand', 'holder_name', 'expiry_month', 'expiry_year', 'is_default', 'is_expired'];

    protected $casts = ['is_default' => 'boolean', 'is_expired' => 'boolean'];

    protected $hidden = ['token'];

    public function checkExpired(): bool
    {
        if (! $this->expiry_month || ! $this->expiry_year) {
            return false;
        }
        $exp = \Carbon\Carbon::createFromDate($this->expiry_year, $this->expiry_month, 1)->endOfMonth();
        $isExp = $exp->isPast();
        if ($isExp !== $this->is_expired) {
            $this->update(['is_expired' => $isExp]);
        }

        return $isExp;
    }

    public function getMaskedNumberAttribute(): string
    {
        return $this->last_four ? "•••• •••• •••• {$this->last_four}" : '•••• •••• •••• ••••';
    }

    public function getExpiryStringAttribute(): string
    {
        return ($this->expiry_month && $this->expiry_year) ? sprintf('%02d/%s', $this->expiry_month, substr($this->expiry_year, -2)) : '';
    }

    public function getBrandIconAttribute(): string
    {
        return match (strtolower($this->brand ?? '')) {
            'visa', 'mastercard', 'amex', 'american express' => '💳', 'paypal' => '🅿️', default => '💳'
        };
    }

    public function getBrandDisplayAttribute(): string
    {
        return match (strtolower($this->brand ?? '')) {
            'visa' => 'Visa', 'mastercard' => 'Mastercard', 'amex', 'american express' => 'American Express', 'discover' => 'Discover', 'paypal' => 'PayPal', default => ucfirst($this->brand ?? 'Card')
        };
    }

    public static function typeOptions(): array
    {
        return [
            'card' => 'Credit/Debit Card',
            'paypal' => 'PayPal',
        ];
    }

    public static function detectBrand(string $firstFour): string
    {
        $firstDigit = substr($firstFour, 0, 1);
        $firstTwo = substr($firstFour, 0, 2);

        if ($firstDigit === '4') {
            return 'visa';
        }

        if (in_array($firstTwo, ['51', '52', '53', '54', '55'])) {
            return 'mastercard';
        }

        if (in_array($firstTwo, ['34', '37'])) {
            return 'amex';
        }

        if ($firstTwo === '60' || $firstTwo === '65') {
            return 'discover';
        }

        return 'unknown';
    }
}
