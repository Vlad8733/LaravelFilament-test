<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'subject', 'description', 'status', 'priority', 'assigned_to', 'last_reply_at'];

    protected $casts = ['last_reply_at' => 'datetime'];

    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_URGENT = 'urgent';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(TicketMessage::class)->latestOfMany();
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(TicketMessage::class)->latestOfMany();
    }

    public function unreadMessagesForAdmin()
    {
        return $this->messages()->where('is_admin_reply', false)->where('is_read', false);
    }

    public function unreadMessagesForUser()
    {
        return $this->messages()->where('is_admin_reply', true)->where('is_read', false);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'warning', self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_RESOLVED => 'success', self::STATUS_CLOSED => 'gray', default => 'gray',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'gray', self::PRIORITY_MEDIUM => 'info',
            self::PRIORITY_HIGH => 'warning', self::PRIORITY_URGENT => 'danger', default => 'gray',
        };
    }

    public function scopeStatus($q, $s)
    {
        return $q->where('status', $s);
    }

    public function scopeOpen($q)
    {
        return $q->where('status', self::STATUS_OPEN);
    }

    public function scopeForUser($q, $uid)
    {
        return $q->where('user_id', $uid);
    }
}
