<?php

namespace Modules\About\Policies;

use Modules\About\Models\TeamMember;
use Shared\Contracts\HasRolesContract;

class TeamMemberPolicy
{
    public function before(HasRolesContract $authUser): ?bool
    {
        if ($authUser->hasRole('admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(HasRolesContract $authUser): bool { return false; }
    public function view(HasRolesContract $authUser, TeamMember $member): bool { return false; }
    public function create(HasRolesContract $authUser): bool { return false; }
    public function update(HasRolesContract $authUser, TeamMember $member): bool { return false; }
    public function delete(HasRolesContract $authUser, TeamMember $member): bool { return false; }
}