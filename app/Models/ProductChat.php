<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'customer_id',
        'seller_id',
        'status',
        'last_message_at',
        'last_message_by',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function lastMessageBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_message_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ProductChatMessage::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(ProductChatMessage::class)->latest()->limit(1);
    }

    public function unreadMessagesForSeller(): int
    {
        return $this->messages()
            ->where('is_seller', false)
            ->where('is_read', false)
            ->count();
    }

    public function unreadMessagesForCustomer(): int
    {
        return $this->messages()
            ->where('is_seller', true)
            ->where('is_read', false)
            ->count();
    }

    public function markMessagesAsRead(bool $isSeller): void
    {
        $this->messages()
            ->where('is_seller', ! $isSeller)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
