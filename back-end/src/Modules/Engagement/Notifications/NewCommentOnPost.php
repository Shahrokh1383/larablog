<?php

namespace Modules\Engagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Engagement\Models\Comment;

class NewCommentOnPost extends Notification
{
    use Queueable;

    public function __construct(
        public Comment $comment, 
        public string $postTitle,
        public string $postSlug
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }
    
    public function toDatabase($notifiable): array
    {
        return [
            'type'       => 'new_comment',
            'message'    => "New comment on your post: {$this->postTitle}",
            'is_pending' => !$this->comment->is_approved,
            'post_id'    => $this->comment->post_id,
            'post_slug'  => $this->postSlug,
            'comment_id' => $this->comment->id,
        ];
    }
}