<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->recent()
            ->paginate(20);

        return Inertia::render('notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $this->notificationService->getUnreadCount(Auth::user()),
        ]);
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $this->notificationService->markAsRead($notification);

        return back();
    }

    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead(Auth::user());

        return back();
    }
}