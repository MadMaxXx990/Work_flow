<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at')->paginate(20);
        Notification::where('user_id', Auth::id())->where('is_read', false)->update(['is_read' => true]);
        return view('shared.notifications.index', compact('notifications'));
    }

    public function markRead(int $id)
    {
        Notification::where('notification_id', $id)->where('user_id', Auth::id())->update(['is_read' => true]);
        return back();
    }

    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
