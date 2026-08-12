<?php

namespace Modules\About\Policies;

use Modules\Identity\Models\User;
use Modules\About\Models\TeamMember;

class TeamMemberPolicy
{
    public function before(User $authUser): ?bool
    {
        if ($authUser->hasRole('admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $authUser): bool { return false; }
    public function view(User $authUser, TeamMember $member): bool { return false; }
    public function create(User $authUser): bool { return false; }
    public function update(User $authUser, TeamMember $member): bool { return false; }
    public function delete(User $authUser, TeamMember $member): bool { return false; }
}