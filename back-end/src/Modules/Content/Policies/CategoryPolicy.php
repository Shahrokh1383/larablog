<?php

namespace Modules\Content\Policies;

use Modules\Identity\Models\User;
use Modules\Content\Models\Category;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }
}