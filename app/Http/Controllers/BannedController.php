<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use Illuminate\Http\Request;

class BannedController extends Controller
{
    public function show(Request $request)
    {
        $ban = $request->session()->get('ban');

        if (! $ban) {

            $userId = $request->user()?->id;
            $ip = $request->ip();
            $fingerprint = $request->cookie('device_fingerprint') ?? $request->header('X-Device-Fingerprint');

            $ban = Ban::checkAllBans($userId, $ip, $fingerprint);
        }

        if (! $ban) {
            return redirect('/');
        }

        return view('banned', [
            'ban' => $ban,
            'reason' => Ban::REASONS[$ban->reason] ?? $ban->reason,
            'type' => Ban::TYPES[$ban->type] ?? $ban->type,
        ]);
    }
}
