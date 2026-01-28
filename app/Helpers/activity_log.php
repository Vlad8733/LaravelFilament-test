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

if (! function_exists('mask_email')) {

    function mask_email(?string $email): string
    {
        if (empty($email)) {
            return '••••@••••';
        }

        $parts = explode('@', $email);
        if (count($parts) === 2) {
            $local = $parts[0];
            $domain = $parts[1];
            $masked = substr($local, 0, 2).str_repeat('•', max(strlen($local) - 2, 3));

            return $masked.'@'.$domain;
        }

        return '••••@••••';
    }
}

if (! function_exists('mask_phone')) {

    function mask_phone(?string $phone): string
    {
        if (empty($phone)) {
            return '••••••••••';
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);
        $length = strlen($digits);

        if ($length <= 4) {
            return $phone;
        }

        return str_repeat('•', $length - 4).substr($digits, -4);
    }
}
