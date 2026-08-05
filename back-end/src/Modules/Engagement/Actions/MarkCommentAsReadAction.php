<?php

namespace Modules\Engagement\Actions;

use Modules\Engagement\Models\Comment;

class MarkCommentAsReadAction
{
    public function execute(Comment $comment): void
    {
        if ($comment->read_at === null) {
            $comment->update(['read_at' => now()]);
        }
    }
}