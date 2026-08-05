<?php

namespace Modules\Engagement\Actions;

use Illuminate\Support\Collection;
use Modules\Engagement\Models\Comment;

class BuildCommentTreeAction
{
    public function execute(Collection $comments): Collection
    {
        $grouped = $comments->groupBy('parent_id');

        $comments->each(function (Comment $comment) use ($grouped) {
            $comment->replies_tree = $grouped->get($comment->id, collect());
        });

        return $comments->whereNull('parent_id')->values();
    }
}