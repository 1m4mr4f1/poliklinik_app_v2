<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $ownedNotification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if ($ownedNotification->read_at === null) {
            $ownedNotification->markAsRead();
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }
}
