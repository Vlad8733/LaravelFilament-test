<?php

namespace App\Http\Middleware;

use App\Models\Ban;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBan
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('banned') || $request->is('logout') || $request->is('api/fingerprint')) {
            return $next($request);
        }

        if ($request->is('admin/*') || $request->is('seller/*')) {
            $u = $request->user();
            if ($u && in_array($u->role, ['super_admin', 'admin'])) {
                return $next($request);
            }
        }

        $uid = $request->user()?->id;
        $ip = $request->ip();
        $fp = $request->cookie('device_fingerprint') ?? $request->header('X-Device-Fingerprint');

        $ban = Ban::checkAllBans($uid, $ip, $fp);
        if (! $ban) {
            return $next($request);
        }

        $ban->logAccessAttempt($uid, $ip, $fp, $request->userAgent(), $request->fullUrl());

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'banned',
                'message' => $ban->public_message ?? 'Your access has been restricted.',
                'reason' => Ban::REASONS[$ban->reason] ?? $ban->reason,
                'expires_at' => $ban->expires_at?->toIso8601String(),
                'is_permanent' => $ban->isPermanent(),
            ], 403);
        }

        return redirect()->route('banned')->with('ban', $ban);
    }
}
