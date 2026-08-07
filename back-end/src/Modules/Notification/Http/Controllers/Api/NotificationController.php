<?php

namespace Modules\Notification\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Notification\Services\NotificationService;

class NotificationController
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->getUnreadNotifications($request->user());
        
        return response()->json([
            'data' => $notifications
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());
        
        return response()->json(['message' => 'Notifications marked as read.']);
    }
}