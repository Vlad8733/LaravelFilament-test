<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    use HasFactory;

    public const EVENT_ORDER_CREATED = 'order.created';

    public const EVENT_ORDER_STATUS_CHANGED = 'order.status_changed';

    public const EVENT_REFUND_REQUESTED = 'refund.requested';

    public const EVENT_REFUND_STATUS_CHANGED = 'refund.status_changed';

    public const EVENT_TICKET_CREATED = 'ticket.created';

    public const EVENT_TICKET_STATUS_CHANGED = 'ticket.status_changed';

    public const AVAILABLE_EVENTS = [
        self::EVENT_ORDER_CREATED => 'Order Created',
        self::EVENT_ORDER_STATUS_CHANGED => 'Order Status Changed',
        self::EVENT_REFUND_REQUESTED => 'Refund Requested',
        self::EVENT_REFUND_STATUS_CHANGED => 'Refund Status Changed',
        self::EVENT_TICKET_CREATED => 'Ticket Created',
        self::EVENT_TICKET_STATUS_CHANGED => 'Ticket Status Changed',
    ];

    protected $fillable = [
        'name',
        'url',
        'secret',
        'events',
        'is_active',
        'max_retries',
        'timeout_seconds',
        'last_triggered_at',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    public function listensTo(string $event): bool
    {
        return $this->is_active && in_array($event, $this->events ?? []);
    }

    public static function forEvent(string $event): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();
    }

    public function generateSignature(string $payload): ?string
    {
        if (! $this->secret) {
            return null;
        }

        return hash_hmac('sha256', $payload, $this->secret);
    }
}
