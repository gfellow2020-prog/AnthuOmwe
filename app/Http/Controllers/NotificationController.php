<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * GET /notifications
     */
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * POST /notifications/{notification}/read
     */
    public function markRead(Request $request, DatabaseNotification $notification): JsonResponse
    {
        abort_unless(
            $notification->notifiable_id === $request->user()->id,
            403
        );

        $notification->markAsRead();

        return response()->json(['read' => true]);
    }

    /**
     * POST /notifications/read-all
     */
    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * DELETE /notifications/{notification}
     */
    public function destroy(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(
            $notification->notifiable_id === $request->user()->id,
            403
        );

        $notification->delete();

        return back()->with('success', 'Notification dismissed.');
    }
}
