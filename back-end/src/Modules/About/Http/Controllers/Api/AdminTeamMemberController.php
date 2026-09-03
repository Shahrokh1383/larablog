<?php

namespace Modules\About\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\About\DTOs\TeamMemberDTO;
use Modules\About\Http\Requests\ListEligibleUsersRequest;
use Modules\About\Http\Requests\ListTeamMembersRequest;
use Modules\About\Http\Requests\StoreTeamMemberRequest;
use Modules\About\Http\Requests\UpdateTeamMemberRequest;
use Modules\About\Models\TeamMember;
use Modules\About\Services\TeamService;

class AdminTeamMemberController extends Controller
{
    public function __construct(private TeamService $teamService) {}

    public function index(ListTeamMembersRequest $request): JsonResponse
    {
        return response()->json(
            $this->teamService->getMembersForAdminData($request->perPage())
        );
    }

    public function eligibleUsers(ListEligibleUsersRequest $request): JsonResponse
    {
        return response()->json(
            $this->teamService->getEligibleUsersData($request->search(), $request->perPage())
        );
    }

    public function store(StoreTeamMemberRequest $request): JsonResponse
    {
        $dto = TeamMemberDTO::fromRequest($request->validated());
        $memberData = $this->teamService->create($dto);

        return response()->json([
            'message' => 'Team member created.',
            'data'    => $memberData,
        ], 201);
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): JsonResponse
    {
        $dto = TeamMemberDTO::fromRequest($request->validated());
        $memberData = $this->teamService->update($teamMember, $dto);

        return response()->json([
            'message' => 'Team member updated.',
            'data'    => $memberData,
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