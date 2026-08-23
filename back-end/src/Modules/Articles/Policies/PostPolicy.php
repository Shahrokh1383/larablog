<?php

namespace Modules\Articles\Policies;

use Shared\Contracts\HasRolesContract;
use Modules\Articles\Models\Post;

class PostPolicy
{
    public function viewAny(HasRolesContract $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function view(HasRolesContract $user, Post $post): bool
    {
        if ($user->hasAnyRole(['admin', 'editor'])) {
            return true;
        }

        if ($user->hasRole('author')) {
            return $post->user_id === $user->id;
        }

        return false;
    }

    public function create(HasRolesContract $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function update(HasRolesContract $user, Post $post): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('editor')) return true;
        if ($user->hasRole('author')) return $post->user_id === $user->id;
        return false;
    }

    public function delete(HasRolesContract $user, Post $post): bool
    {
        return $this->update($user, $post);
    }
}