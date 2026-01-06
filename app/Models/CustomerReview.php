<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReview extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'product_id',
        'delivery_rating',
        'packaging_rating',
        'product_rating',
        'overall_rating',
        'comment',
        'status',
        'moderation_notes',
        'moderated_by',
        'moderated_at',
    ];

    protected $casts = [
        'delivery_rating' => 'integer',
        'packaging_rating' => 'integer',
        'product_rating' => 'integer',
        'overall_rating' => 'decimal:1',
        'moderated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($review) {
            // Автоматически рассчитываем overall_rating
            $review->overall_rating = round(
                ($review->delivery_rating + $review->packaging_rating + $review->product_rating) / 3,
                1
            );
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    // Скоуп для одобренных отзывов
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Скоуп для отзывов пользователя
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
