<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification; // To interact with specific notification instances

class NotificationController extends Controller
{
    /**
     * Display a listing of the authenticated user's notifications.
     * Optionally filter by 'unread' status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->notifications(); // Gets all notifications

        if ($request->query('status') === 'unread') {
            $query = $user->unreadNotifications();
        } elseif ($request->query('status') === 'read') {
            $query = $user->readNotifications();
        }
        
        // You could also add other filters like type if needed

        $notifications = $query->latest()->paginate(15); // Get latest first, paginated

        return response()->json($notifications);
    }

    /**
     * Mark the given notification as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Notifications\DatabaseNotification  $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, DatabaseNotification $notification)
    {
        $user = Auth::user();

        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== get_class($user)) {
            return response()->json(['message' => 'Unauthorized to modify this notification.'], 403);
        }

        if (!$notification->read_at) { // Only mark if it's unread
            $notification->markAsRead();
        }

        return response()->json(['message' => 'Notification marked as read.', 'notification' => $notification]);
    }

    /**
     * Mark all unread notifications for the authenticated user as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead(); // Mark all collection as read

        return response()->json(['message' => 'All unread notifications marked as read.']);
    }

    // Optional: Method to delete a notification
    // public function destroy(Request $request, DatabaseNotification $notification)
    // {
    //     $user = Auth::user();
    //     if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== get_class($user)) {
    //         return response()->json(['message' => 'Unauthorized to delete this notification.'], 403);
    //     }
    //     $notification->delete();
    //     return response()->json(['message' => 'Notification deleted successfully.']);
    // }
}