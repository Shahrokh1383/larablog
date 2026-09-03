<?php

namespace Modules\About\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\About\DTOs\TeamMemberDTO;
use Modules\About\Models\TeamMember;
use Modules\Identity\Services\Contracts\FetchesUsersByRole;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;

class TeamService
{
    public const PUBLIC_TEAM_LIMIT = 8;

    private const ELIGIBLE_ROLES = ['admin', 'editor', 'author'];

    public function __construct(
        private FetchesUsersByRole $userFetcher,
        private FetchesPublicProfiles $profileFetcher,
    ) {}

    public function getActiveMembersData(int $perPage = self::PUBLIC_TEAM_LIMIT): array
    {
        $members = TeamMember::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return $this->mapMembers($members);
    }

    public function getMembersForAdminData(int $perPage): array
    {
        $members = TeamMember::query()
            ->orderBy('sort_order')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return $this->mapMembers($members);
    }

    public function getEligibleUsersData(?string $search, int $perPage): array
    {
        $paginator = $this->getEligibleUsers($search, $perPage);

        $data = collect($paginator->items())
            ->map(fn (array $user) => $this->mapEligibleUser($user))
            ->values()
            ->all();

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ];
    }

    public function create(TeamMemberDTO $dto): array
    {
        $member = TeamMember::create([
            'user_id'    => $dto->userId,
            'sort_order' => $dto->sortOrder ?? 0,
            'is_active'  => $dto->isActive ?? true,
        ]);

        return $this->mapSingleMember(
            $member,
            $this->userFetcher->getUsersWithRolesMap([$member->user_id]),
            $this->profileFetcher->getPublicProfilesMap([$member->user_id]),
        );
    }

    public function update(TeamMember $member, TeamMemberDTO $dto): array
    {
        $attributes = array_filter(
            [
                'user_id'    => $dto->userId,
                'sort_order' => $dto->sortOrder,
                'is_active'  => $dto->isActive,
            ],
            static fn ($value) => $value !== null,
        );

        if ($attributes !== []) {
            $member->update($attributes);
        }

        $member->refresh();

        return $this->mapSingleMember(
            $member,
            $this->userFetcher->getUsersWithRolesMap([$member->user_id]),
            $this->profileFetcher->getPublicProfilesMap([$member->user_id]),
        );
    }

    public function delete(TeamMember $member): void
    {
        $member->delete();
    }

    private function getEligibleUsers(?string $search, int $perPage): LengthAwarePaginator
    {
        $excludedIds = TeamMember::query()->pluck('user_id')->toArray();

        return $this->userFetcher->getPaginatedUsersWithRoles(
            self::ELIGIBLE_ROLES,
            $search,
            $perPage,
            $excludedIds,
        );
    }

    private function mapEligibleUser(array $user): array
    {
        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'email'  => $user['email'],
            'avatar' => $user['avatar'] ?? null,
            'roles'  => $user['roles'] ?? [],
        ];
    }

    private function mapMembers(LengthAwarePaginator $members): array
    {
        $userIds = $members->pluck('user_id')->toArray();
        $usersMap = $this->userFetcher->getUsersWithRolesMap($userIds);
        $profilesMap = $this->profileFetcher->getPublicProfilesMap($userIds);

        $mapped = collect($members->items())
            ->map(fn (TeamMember $member) => $this->mapSingleMember($member, $usersMap, $profilesMap))
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

    private function mapSingleMember(TeamMember $member, array $usersMap, array $profilesMap): array
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
}