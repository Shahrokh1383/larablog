<?php

namespace Modules\Taxonomy\Policies;

use Shared\Contracts\HasRolesContract;
use Modules\Taxonomy\Models\Tag;

class TagPolicy
{
    public function viewAny(HasRolesContract $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function view(HasRolesContract $user, Tag $tag): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function create(HasRolesContract $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(HasRolesContract $user, Tag $tag): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(HasRolesContract $user, Tag $tag): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }
}