<?php

namespace Modules\Notification\Services;

use Shared\Models\User;

class NotificationService
{
    /**
     * Fetch unread notifications for the user.
     */
    public function getUnreadNotifications(User $user)
    {
        return $user->unreadNotifications()->get();
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}