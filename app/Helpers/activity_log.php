<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

if (! function_exists('activity_log')) {
    function activity_log(string $action): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
