<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundStatusHistory extends Model
{
    protected $table = 'refund_status_history';

    protected $fillable = [
        'refund_request_id',
        'status',
        'notes',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function refundRequest(): BelongsTo
    {
        return $this->belongsTo(RefundRequest::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
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

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'blue',
            'rejected' => 'red',
            'completed' => 'green',
            default => 'gray',
        };
    }
}
