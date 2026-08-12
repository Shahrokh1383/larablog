<?php

namespace Modules\About\Services;

use Modules\About\Models\TeamMember;
use Modules\About\DTOs\TeamMemberDTO;
use Modules\Identity\Services\Contracts\FetchesUsersByRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TeamService
{
    public function __construct(
        private FetchesUsersByRole $userFetcher,
    ) {}

    public function getActiveMembers(int $perPage = 8): LengthAwarePaginator
    {
        return TeamMember::with('user')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    public function getMembersForAdmin(int $perPage = 10): LengthAwarePaginator
    {
        return TeamMember::with('user')
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    public function getEligibleUsers(): Collection
    {
        return $this->userFetcher->getUsersWithRoles(['admin', 'editor', 'author']);
    }

    public function create(TeamMemberDTO $dto): TeamMember
    {
        return TeamMember::create([
            'user_id' => $dto->userId,
            'display_name' => $dto->displayName,
            'position' => $dto->position,
            'bio' => $dto->bio,
            'photo' => $dto->photo,
            'sort_order' => $dto->sortOrder,
            'is_active' => $dto->isActive,
        ]);
    }

    public function update(TeamMember $member, TeamMemberDTO $dto): TeamMember
    {
        $member->update([
            'user_id' => $dto->userId,
            'display_name' => $dto->displayName,
            'position' => $dto->position,
            'bio' => $dto->bio,
            'photo' => $dto->photo,
            'sort_order' => $dto->sortOrder,
            'is_active' => $dto->isActive,
        ]);
        return $member->fresh();
    }

    public function delete(TeamMember $member): void
    {
        $member->delete();
    }
}