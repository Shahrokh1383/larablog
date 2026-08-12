<?php

namespace Modules\About\Services;

use Modules\About\Models\TeamMember;
use Modules\About\DTOs\TeamMemberDTO;
use Modules\Identity\Services\Contracts\FetchesUsersByRole;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeamService
{
    public function __construct(
        private FetchesUsersByRole $userFetcher,
        private FetchesPublicProfiles $profileFetcher
    ) {}

    public function getActiveMembersData(int $perPage = 8): array
    {
        $members = TeamMember::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return $this->mapMembers($members);
    }

    public function getMembersForAdminData(int $perPage = 10): array
    {
        $members = TeamMember::orderBy('sort_order')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return $this->mapMembers($members);
    }

    private function mapMembers($members): array
    {
        $userIds = $members->pluck('user_id')->toArray();
        $usersMap = $this->userFetcher->getUsersWithRolesMap($userIds);
        $profilesMap = $this->profileFetcher->getPublicProfilesMap($userIds);

        $mapped = collect($members->items())
            ->map(function ($member) use ($usersMap, $profilesMap) {
                return $this->mapSingleMember($member, $usersMap, $profilesMap);
            })
            ->values();

        return [
            'data' => $mapped,
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page'    => $members->lastPage(),
                'per_page'     => $members->perPage(),
                'total'        => $members->total(),
            ],
        ];
    }

    private function mapSingleMember(TeamMember $member, array $usersMap = [], array $profilesMap = []): array
    {
        $user = $usersMap[$member->user_id] ?? null;
        $profile = $profilesMap[$member->user_id] ?? null;

        return [
            'id'         => $member->id,
            'user_id'    => $member->user_id,
            'sort_order' => $member->sort_order,
            'is_active'  => $member->is_active,
            'user'       => $user ? [
                'id'           => $user['id'],
                'name'         => $user['name'],
                'email'        => $user['email'],
                'avatar'       => $profile['avatar'] ?? null,
                'bio'          => $profile['bio'] ?? null,
                'expertise'    => $profile['expertise'] ?? null,
                'roles'        => $user['roles'],
                'social_links' => $profile['social_links'] ?? null,
            ] : null,
            'created_at' => $member->created_at,
            'updated_at' => $member->updated_at,
        ];
    }

    public function getEligibleUsers(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $excludedIds = TeamMember::pluck('user_id')->toArray();

        return $this->userFetcher->getPaginatedUsersWithRoles(
            ['admin', 'editor', 'author'],
            $search,
            $perPage,
            $excludedIds
        );
    }

    public function create(TeamMemberDTO $dto): array
    {
        $member = TeamMember::create([
            'user_id'    => $dto->userId,
            'sort_order' => $dto->sortOrder,
            'is_active'  => $dto->isActive,
        ]);

        $usersMap = $this->userFetcher->getUsersWithRolesMap([$member->user_id]);
        $profilesMap = $this->profileFetcher->getPublicProfilesMap([$member->user_id]);

        return $this->mapSingleMember($member, $usersMap, $profilesMap);
    }

    public function update(TeamMember $member, TeamMemberDTO $dto): array
    {
        $member->update([
            'user_id'    => $dto->userId,
            'sort_order' => $dto->sortOrder,
            'is_active'  => $dto->isActive,
        ]);

        $member->refresh();

        $usersMap = $this->userFetcher->getUsersWithRolesMap([$member->user_id]);
        $profilesMap = $this->profileFetcher->getPublicProfilesMap([$member->user_id]);

        return $this->mapSingleMember($member, $usersMap, $profilesMap);
    }

    public function delete(TeamMember $member): void
    {
        $member->delete();
    }
}