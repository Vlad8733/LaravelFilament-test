<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        return view('notifications.index', ['notifications' => Auth::user()->notifications()->paginate(15)]);
    }

    public function unread()
    {
        $u = Auth::user();
        $notifs = $u->unreadNotifications()->latest()->take(10)->get()->map(fn ($n) => [
            'id' => $n->id, 'type' => $n->type, 'data' => $n->data,
            'created_at' => $n->created_at->toISOString(), 'created_at_human' => $n->created_at->diffForHumans(),
        ]);

        return response()->json(['notifications' => $notifs, 'count' => $u->unreadNotifications()->count()]);
    }

    public function count()
    {
        return response()->json(['count' => Auth::user()->unreadNotifications()->count()]);
    }

    public function markAsRead($id)
    {
        $n = Auth::user()->notifications()->where('id', $id)->first();
        if ($n) {
            $n->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Auth::user()->notifications()->where('id', $id)->delete();

        return response()->json(['success' => true]);
    }

    public function destroyAll()
    {
        Auth::user()->notifications()->delete();

        return response()->json(['success' => true]);
    }
}
