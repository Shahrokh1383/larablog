<?php

namespace Modules\About\Http\Controllers\Api;

use Modules\About\Models\TeamMember;
use Modules\About\Services\TeamService;
use Modules\About\DTOs\TeamMemberDTO;
use Modules\About\Http\Requests\StoreTeamMemberRequest;
use Modules\About\Http\Requests\UpdateTeamMemberRequest;
use Modules\About\Http\Resources\TeamMemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class AdminTeamMemberController extends Controller
{
    public function __construct(private TeamService $teamService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $members = $this->teamService->getMembersForAdmin($perPage);

        return response()->json([
            'data' => TeamMemberResource::collection($members->items()),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
        ]);
    }

    public function eligibleUsers(): JsonResponse
    {
        $users = $this->teamService->getEligibleUsers();

        return response()->json([
            'data' => \Modules\Identity\Http\Resources\UserResource::collection($users),
        ]);
    }

    public function store(StoreTeamMemberRequest $request): JsonResponse
    {
        $dto = TeamMemberDTO::fromRequest($request->validated());
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('team-photos', 'public');
            $dto = new TeamMemberDTO(
                userId: $dto->userId,
                displayName: $dto->displayName,
                position: $dto->position,
                bio: $dto->bio,
                photo: $path,
                sortOrder: $dto->sortOrder,
                isActive: $dto->isActive,
            );
        }

        $member = $this->teamService->create($dto);

        return response()->json([
            'message' => 'Team member created.',
            'data' => new TeamMemberResource($member->load('user')),
        ], 201);
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): JsonResponse
    {
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('team-photos', 'public');
        }

        $dto = TeamMemberDTO::fromRequest($data);
        $member = $this->teamService->update($teamMember, $dto);

        return response()->json([
            'message' => 'Team member updated.',
            'data' => new TeamMemberResource($member->load('user')),
        ]);
    }

    public function destroy(TeamMember $teamMember): JsonResponse
    {
        $this->teamService->delete($teamMember);

        return response()->json([
            'message' => 'Team member deleted.',
        ]);
    }
}