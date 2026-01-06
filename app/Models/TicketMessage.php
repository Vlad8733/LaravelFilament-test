<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_admin_reply',
        'is_read',
    ];

    protected $casts = [
        'is_admin_reply' => 'boolean',
        'is_read' => 'boolean',
    ];

    /**
     * Заявка, к которой относится сообщение
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Автор сообщения
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Вложения к сообщению
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    /**
     * Пометить сообщение как прочитанное
     */
    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update(['is_read' => true]);
        }
    }

    /**
     * Scope для непрочитанных сообщений
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope для сообщений от админа
     */
    public function scopeFromAdmin($query)
    {
        return $query->where('is_admin_reply', true);
    }

    /**
     * Scope для сообщений от пользователя
     */
    public function scopeFromUser($query)
    {
        return $query->where('is_admin_reply', false);
    }
}
