<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'shipping_address',
        'subtotal',
        'discount_amount',
        'total',
        'payment_method',
        'payment_status',
        'order_status',
        'coupon_code',
        'notes',
        'order_status_id',
        'tracking_number',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-'.date('Ymd').'-'.strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('changed_at', 'desc');
    }

    public function updateStatus($statusId, $notes = null, $changedBy = null)
    {
        $oldStatusId = $this->order_status_id;

        $this->update(['order_status_id' => $statusId]);

        OrderStatusHistory::create([
            'order_id' => $this->id,
            'order_status_id' => $statusId,
            'changed_by' => $changedBy ?? auth()->id(),
            'notes' => $notes,
            'changed_at' => now(),
        ]);

        // Отправка уведомлений при смене статуса
        if ($oldStatusId !== $statusId) {
            // Отправка in-app уведомления пользователю (если есть user_id)
            if ($this->user_id && $this->user) {
                try {
                    $this->user->notify(new \App\Notifications\OrderStatusChanged($this));
                } catch (\Exception $e) {
                    \Log::warning('Failed to send in-app order notification: '.$e->getMessage());
                }
            }
            
            // Отправка email уведомления
            try {
                \Illuminate\Support\Facades\Notification::route('mail', $this->customer_email)
                    ->notify(new \App\Notifications\OrderStatusChanged($this));
            } catch (\Exception $e) {
                \Log::warning('Failed to send order email notification: '.$e->getMessage());
            }
        }

        return $this;
    }

    public function getStatusColorAttribute()
    {
        return match ($this->order_status) {
            'pending' => 'yellow',
            'processing' => 'blue',
            'shipped' => 'indigo',
            'delivered' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function refundRequest(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RefundRequest::class);
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CustomerReview::class);
    }

    public function canBeReviewed(): bool
    {
        return $this->status && $this->status->slug === 'delivered';
    }
}
