<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $language = in_array(
            $request->header('language'),
            ['ar', 'en'],
            true
        )
            ? $request->header('language')
            : 'ar';

        $notifications = $user->appNotifications()
            ->latest()
            ->paginate(
                $request->integer('per_page', 20)
            );

        $notifications->getCollection()->transform(
            function (AppNotification $notification) use ($language) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,

                    'title' => $language === 'ar'
                        ? $notification->title_ar
                        : $notification->title_en,

                    'body' => $language === 'ar'
                        ? $notification->body_ar
                        : $notification->body_en,

                    'data' => $notification->data,

                    'is_read' => $notification->read_at !== null,

                    'created_at' => $notification->created_at,
                ];
            }
        );

        return response()->json([
            'status' => true,
            'unread_count' => $user->appNotifications()
                ->whereNull('read_at')
                ->count(),
            'data' => $notifications,
        ]);
    }

    public function markAsRead(
        Request $request,
        AppNotification $notification
    ): JsonResponse {
        $user = $request->user();

        if (
            $notification->notifiable_type !== $user->getMorphClass() ||
            (int) $notification->notifiable_id !== (int) $user->getKey()
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$notification->read_at) {
            $notification->update([
                'read_at' => now(),
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    public function markAllAsRead(
        Request $request
    ): JsonResponse {
        $request->user()
            ->appNotifications()
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return response()->json([
            'status' => true,
            'message' => 'All notifications marked as read',
        ]);
    }
}
