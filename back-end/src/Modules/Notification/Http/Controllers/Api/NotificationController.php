<?php

namespace Modules\Notification\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Notification\Http\Resources\NotificationResource;
use Modules\Notification\Services\NotificationService;

class NotificationController
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // Now fetching recent notifications instead of strictly unread
        $notifications = $this->notificationService->getRecentNotifications($request->user());
        
        return response()->json([
            'data' => NotificationResource::collection($notifications)
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());
        
        return response()->json(['message' => 'Notifications marked as read.']);
    }
}