<?php

namespace Modules\Taxonomy\Policies;

use Shared\Contracts\HasRolesContract;
use Modules\Taxonomy\Models\Category;

class CategoryPolicy
{
    public function viewAny(HasRolesContract $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function view(HasRolesContract $user, Category $category): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function create(HasRolesContract $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(HasRolesContract $user, Category $category): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(HasRolesContract $user, Category $category): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }
}