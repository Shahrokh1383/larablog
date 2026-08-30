<?php

namespace Modules\Engagement\Policies;

use Modules\Engagement\Models\Comment;
use Shared\Models\User;

class CommentPolicy
{
    public function create(?User $user): bool
    {
        return true;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id || $user->hasRole('admin');
    }

    public function manage(User $user, Comment $comment): bool
    {
        return $user->hasRole('admin');
    }
}