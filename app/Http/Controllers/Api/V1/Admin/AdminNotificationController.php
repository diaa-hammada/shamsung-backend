<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $adminId = $request->user()->id;

        $notifications = Notification::where('admin_id', $adminId)
            ->latest()
            ->limit(20)
            ->get(['id', 'type', 'title', 'body', 'data', 'is_read', 'created_at']);

        $unreadCount = Notification::where('admin_id', $adminId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'message' => 'Notifications retrieved successfully',
            'data'    => [
                'unread_count'  => $unreadCount,
                'notifications' => $notifications,
            ],
        ], 200);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        Notification::where('admin_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read'], 200);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = Notification::where('admin_id', $request->user()->id)->findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read'], 200);
    }
}
