<?php

namespace Modules\Content\Policies;

use Modules\Identity\Models\User;
use Modules\Content\Models\Tag;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function view(User $user, Tag $tag): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }
}