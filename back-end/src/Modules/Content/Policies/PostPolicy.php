<?php

namespace Modules\Content\Policies;

use Modules\Identity\Models\User;
use Modules\Content\Models\Post;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function view(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('editor')) return true;
        if ($user->hasRole('author')) return $post->user_id === $user->id;
        return false;
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }
}