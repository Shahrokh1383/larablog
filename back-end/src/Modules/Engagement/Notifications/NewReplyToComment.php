<?php

namespace Modules\Engagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Modules\Engagement\Models\Comment;

class NewReplyToComment extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public Comment $reply) {}

    public function via($notifiable): array
    {
        return ['broadcast', 'database'];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $authorName = $this->reply->user?->name ?? $this->reply->name;
        
        return new BroadcastMessage([
            'type'       => 'new_reply',
            'message'    => "{$authorName} replied to your comment.",
            'post_id'    => $this->reply->post_id,
            'comment_id' => $this->reply->parent_id,
            'reply_id'   => $this->reply->id,
            'created_at' => now()->toIso8601String(),
        ]);
    }
    
    public function toDatabase($notifiable): array
    {
        $authorName = $this->reply->user?->name ?? $this->reply->name;
        
        return [
            'type'       => 'new_reply',
            'message'    => "{$authorName} replied to your comment.",
            'post_id'    => $this->reply->post_id,
            'comment_id' => $this->reply->parent_id,
            'reply_id'   => $this->reply->id,
        ];
    }
}