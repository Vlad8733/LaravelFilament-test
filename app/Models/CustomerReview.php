<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use App\Traits\HasStatusLabels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReview extends Model
{
    use BelongsToUser, HasFactory, HasStatusLabels;

    protected $fillable = [
        'order_id', 'user_id', 'product_id', 'delivery_rating', 'packaging_rating', 'product_rating',
        'overall_rating', 'comment', 'status', 'moderation_notes', 'moderated_by', 'moderated_at',
    ];

    protected $casts = [
        'delivery_rating' => 'integer', 'packaging_rating' => 'integer', 'product_rating' => 'integer',
        'overall_rating' => 'decimal:1', 'moderated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saving(fn ($r) => $r->overall_rating = round(($r->delivery_rating + $r->packaging_rating + $r->product_rating) / 3, 1));
    }

    protected function statusLabels(): array
    {
        return ['pending' => __('Pending Review'), 'approved' => __('Approved'), 'rejected' => __('Rejected')];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function scopeApproved($q)
    {
        return $q->where('status', 'approved');
    }
}
