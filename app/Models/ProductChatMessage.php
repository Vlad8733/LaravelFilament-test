<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_chat_id',
        'user_id',
        'message',
        'is_seller',
        'is_read',
        'attachment_path',
        'attachment_name',
        'attachment_type',
    ];

    protected $casts = [
        'is_seller' => 'boolean',
        'is_read' => 'boolean',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(ProductChat::class, 'product_chat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path ? asset('storage/'.$this->attachment_path) : null;
    }

    public function isImage(): bool
    {
        return $this->attachment_type && str_starts_with($this->attachment_type, 'image/');
    }
}
