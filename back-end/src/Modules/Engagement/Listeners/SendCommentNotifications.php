<?php

namespace Modules\Engagement\Listeners;

use Modules\Engagement\Events\CommentCreated;
use Modules\Engagement\Models\Comment;
use Modules\Engagement\Notifications\NewCommentOnPost;
use Modules\Engagement\Notifications\NewReplyToComment;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Shared\Models\User;

class SendCommentNotifications
{
    public function __construct(
        private PostInfoContract $postInfoService
    ) {}

    public function handle(CommentCreated $event): void
    {
        $comment = $event->comment;
        $postInfo = $this->postInfoService->getPostInfo($comment->post_id);

        // If the post was deleted, abort notification dispatch
        if (!$postInfo) return;

        // 1. Notify Post Author (if registered and not the commenter)
        if ($postInfo->authorId && $postInfo->authorId !== $comment->user_id) {
            $author = User::find($postInfo->authorId);
            if ($author) {
                $author->notify(new NewCommentOnPost($comment, $postInfo->title, $postInfo->slug));
            }
        }

        // 2. Notify Parent Comment Author (if registered and not the replier)
        $parentIdForNotification = $event->originalParentId ?? $comment->parent_id;

        if ($parentIdForNotification) {
            $parentComment = Comment::find($parentIdForNotification);
            if ($parentComment && $parentComment->user_id && $parentComment->user_id !== $comment->user_id) {
                $parentAuthor = User::find($parentComment->user_id);
                if ($parentAuthor) {
                    $parentAuthor->notify(new NewReplyToComment($comment, $postInfo->slug));
                }
            }
        }
    }
}