<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'full_name',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected $appends = [
        'address_line1',
        'address_line2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set this address as default, unset others
     */
    public function setAsDefault(): void
    {
        // Unset other defaults
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Accessor for address_line1 (alias for address_line_1)
     */
    public function getAddressLine1Attribute(): ?string
    {
        return $this->attributes['address_line_1'] ?? null;
    }

    /**
     * Accessor for address_line2 (alias for address_line_2)
     */
    public function getAddressLine2Attribute(): ?string
    {
        return $this->attributes['address_line_2'] ?? null;
    }

    /**
     * Get formatted full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->attributes['address_line_1'] ?? null,
            $this->attributes['address_line_2'] ?? null,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get label icon
     */
    public function getLabelIconAttribute(): string
    {
        return match(strtolower($this->label)) {
            'home' => '🏠',
            'work' => '🏢',
            'office' => '🏢',
            default => '📍',
        };
    }

    /**
     * Available label options
     */
    public static function labelOptions(): array
    {
        return [
            'Home' => 'Home',
            'Work' => 'Work',
            'Office' => 'Office',
            'Other' => 'Other',
        ];
    }

    /**
     * Country options (simplified list)
     */
    public static function countryOptions(): array
    {
        return [
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'AT' => 'Austria',
            'CH' => 'Switzerland',
            'PL' => 'Poland',
            'CZ' => 'Czech Republic',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'DK' => 'Denmark',
            'FI' => 'Finland',
            'RU' => 'Russia',
            'UA' => 'Ukraine',
            'LV' => 'Latvia',
            'LT' => 'Lithuania',
            'EE' => 'Estonia',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'NZ' => 'New Zealand',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'CN' => 'China',
            'IN' => 'India',
            'BR' => 'Brazil',
            'MX' => 'Mexico',
        ];
    }
}
