<?php

namespace Modules\About\Http\Controllers\Api;

use Modules\About\Models\TeamMember;
use Modules\About\Services\TeamService;
use Modules\About\DTOs\TeamMemberDTO;
use Modules\About\Http\Requests\StoreTeamMemberRequest;
use Modules\About\Http\Requests\UpdateTeamMemberRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminTeamMemberController extends Controller
{
    public function __construct(private TeamService $teamService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $data = $this->teamService->getMembersForAdminData($perPage);

        return response()->json($data);
    }

    public function eligibleUsers(Request $request): JsonResponse
    {
        $search  = $request->input('search');
        $perPage = (int) $request->input('per_page', 500); 
        $paginator = $this->teamService->getEligibleUsers($search, $perPage);

        $mapped = collect($paginator->items())
            ->map(function ($user) {
                return [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'avatar' => $user->avatar,
                    'roles'  => $user->roles->pluck('name')->toArray(),
                ];
            })
            ->values();

        return response()->json([
            'data' => $mapped,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
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