<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefundRequest extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'status',
        'type',
        'amount',
        'reason',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(RefundStatusHistory::class)->orderBy('changed_at', 'desc');
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

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'completed' => 'success',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => __('refunds.status_pending_review'),
            'approved' => __('refunds.status_approved'),
            'rejected' => __('refunds.status_rejected'),
            'completed' => __('refunds.status_refunded'),
            'cancelled' => __('refunds.status_cancelled'),
            default => ucfirst($this->status),
        };
    }

    public function addStatusHistory(string $status, ?string $notes = null, ?int $changedBy = null): void
    {
        $this->statusHistory()->create([
            'status' => $status,
            'notes' => $notes,
            'changed_by' => $changedBy,
            'changed_at' => now(),
        ]);
    }
}
