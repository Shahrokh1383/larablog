<?php

namespace Modules\Engagement\Services;

use Modules\Engagement\Models\Comment;
use Modules\Engagement\Actions\BuildCommentTreeAction;
use Illuminate\Support\Collection;

class CommentPublicService
{
    public function __construct(
        private BuildCommentTreeAction $buildTreeAction,
    ) {}

    public function getCommentsForPost(string $postId): Collection
    {
        $comments = Comment::where('post_id', $postId)
            ->approved()
            ->with('user')
            ->latest()
            ->get();

        return $this->buildTreeAction->execute($comments);
    }
}