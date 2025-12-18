<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Показать все уведомления пользователя
     */
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Получить непрочитанные уведомления (для AJAX)
     */
    public function unread()
    {
        $notifications = Auth::user()
            ->unreadNotifications()
            ->take(10)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Пометить уведомление как прочитанное
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        // Редирект на соответствующую страницу
        $data = $notification->data;
        
        if (isset($data['ticket_id'])) {
            return redirect()->route('tickets.show', $data['ticket_id']);
        }

        return redirect()->back();
    }

    /**
     * Пометить все уведомления как прочитанные
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Удалить уведомление
     */
    public function destroy($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Удалить все уведомления
     */
    public function destroyAll()
    {
        Auth::user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted',
        ]);
    }
}
