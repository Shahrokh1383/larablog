<?php

namespace Modules\Notification\Services;

use Shared\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Fetch strictly unread notifications for the "Unread Inbox" UI pattern.
     */
    public function getUnreadNotifications(User $user): Collection
    {
        return $user->unreadNotifications()->latest()->take(10)->get();
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(User $user, string $notificationId): void
    {
        // Find the notification specifically belonging to this user
        $notification = $user->unreadNotifications()->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}