<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUser
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($q, $uid = null)
    {
        return $q->where('user_id', $uid ?? auth()->id());
    }

    public function scopeForCurrentUser($q)
    {
        return $q->where('user_id', auth()->id());
    }

    public function belongsToUser($uid = null): bool
    {
        return $this->user_id === ($uid ?? auth()->id());
    }
}
