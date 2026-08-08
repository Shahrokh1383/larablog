<?php

namespace Modules\Notification\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\DatabaseNotification;
use Modules\Notification\Events\UserNotificationBroadcast;
use Shared\Models\User;

class BroadcastNotificationListener
{
    public function handle(NotificationSent $event): void
    {
        // Strictly bridge only database notifications sent to Users
        if ($event->channel !== 'database' || !($event->notifiable instanceof User)) {
            return;
        }

        $dbNotification = DatabaseNotification::find($event->notification->id);
        
        if (!$dbNotification) {
            return;
        }

        $payload = [
            'id'         => (string) $dbNotification->id,
            'type'       => $dbNotification->data['type'] ?? null,
            'message'    => $dbNotification->data['message'] ?? null,
            'post_id'    => $dbNotification->data['post_id'] ?? null,
            'comment_id' => $dbNotification->data['comment_id'] ?? null,
            'reply_id'   => $dbNotification->data['reply_id'] ?? null,
            'read_at'    => $dbNotification->read_at?->toIso8601String(),
            'created_at' => $dbNotification->created_at->toIso8601String(),
        ];

        UserNotificationBroadcast::dispatch($event->notifiable->id, $payload);
    }
}