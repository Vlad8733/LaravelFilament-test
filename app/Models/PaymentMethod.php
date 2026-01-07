<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PaymentMethod - stores ONLY tokens and masked data
 * 
 * IMPORTANT: This model does NOT store:
 * - Full card numbers
 * - CVV/CVC codes
 * - Full expiry dates in raw form
 * 
 * It stores only:
 * - Payment gateway tokens (for recurring payments)
 * - Last 4 digits (for display purposes)
 * - Card brand (visa, mastercard, etc.)
 * - Expiry month/year (for validation)
 */
class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'provider',
        'token',
        'last_four',
        'brand',
        'holder_name',
        'expiry_month',
        'expiry_year',
        'is_default',
        'is_expired',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_expired' => 'boolean',
    ];

    protected $hidden = [
        'token', // Never expose tokens
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set as default payment method
     */
    public function setAsDefault(): void
    {
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Check if card is expired
     */
    public function checkExpired(): bool
    {
        if (!$this->expiry_month || !$this->expiry_year) {
            return false;
        }

        $expiryDate = \Carbon\Carbon::createFromDate(
            $this->expiry_year,
            $this->expiry_month,
            1
        )->endOfMonth();

        $isExpired = $expiryDate->isPast();

        if ($isExpired !== $this->is_expired) {
            $this->update(['is_expired' => $isExpired]);
        }

        return $isExpired;
    }

    /**
     * Get masked card number for display
     */
    public function getMaskedNumberAttribute(): string
    {
        if (!$this->last_four) {
            return '•••• •••• •••• ••••';
        }

        return "•••• •••• •••• {$this->last_four}";
    }

    /**
     * Get expiry string
     */
    public function getExpiryStringAttribute(): string
    {
        if (!$this->expiry_month || !$this->expiry_year) {
            return '';
        }

        return sprintf('%02d/%s', $this->expiry_month, substr($this->expiry_year, -2));
    }

    /**
     * Get brand icon
     */
    public function getBrandIconAttribute(): string
    {
        return match(strtolower($this->brand ?? '')) {
            'visa' => '💳',
            'mastercard' => '💳',
            'amex', 'american express' => '💳',
            'paypal' => '🅿️',
            default => '💳',
        };
    }

    /**
     * Get brand display name
     */
    public function getBrandDisplayAttribute(): string
    {
        return match(strtolower($this->brand ?? '')) {
            'visa' => 'Visa',
            'mastercard' => 'Mastercard',
            'amex', 'american express' => 'American Express',
            'discover' => 'Discover',
            'paypal' => 'PayPal',
            default => ucfirst($this->brand ?? 'Card'),
        };
    }

    /**
     * Available payment types
     */
    public static function typeOptions(): array
    {
        return [
            'card' => 'Credit/Debit Card',
            'paypal' => 'PayPal',
        ];
    }

    /**
     * Detect card brand from number (first 4 digits only for privacy)
     */
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
