<?php

namespace Modules\Notification\Services;

use Shared\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Fetch recent notifications (both read and unread) for the UI dropdown.
     * This ensures the UI never appears completely empty.
     */
    public function getRecentNotifications(User $user): Collection
    {
        return $user->notifications()->latest()->take(10)->get();
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}