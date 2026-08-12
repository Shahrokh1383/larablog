<?php

namespace Modules\About\Http\Controllers\Api;

use Modules\About\Models\TeamMember;
use Modules\About\Services\TeamService;
use Modules\About\DTOs\TeamMemberDTO;
use Modules\About\Http\Requests\StoreTeamMemberRequest;
use Modules\About\Http\Requests\UpdateTeamMemberRequest;
use Modules\About\Http\Resources\TeamMemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

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
                'last_page'    => $members->lastPage(),
                'per_page'     => $members->perPage(),
                'total'        => $members->total(),
            ],
        ]);
    }

    public function eligibleUsers(Request $request): JsonResponse
    {
        $search  = $request->input('search');
        $perPage = (int) $request->input('per_page', 15);
        $users   = $this->teamService->getEligibleUsers($search, $perPage);

        $data = $users->map(function ($user) {
            $profile = DB::table('profiles')->where('user_id', $user->id)->first();
            $roles = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_type', 'Modules\\Identity\\Models\\User')
                ->where('model_has_roles.model_id', $user->id)
                ->pluck('roles.name')
                ->toArray();

            return [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $profile?->avatar ?? null,
                'roles'  => $roles,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    public function store(StoreTeamMemberRequest $request): JsonResponse
    {
        $dto    = TeamMemberDTO::fromRequest($request->validated());
        $member = $this->teamService->create($dto);

        return response()->json([
            'message' => 'Team member created.',
            'data'    => new TeamMemberResource($member->load('user')),
        ], 201);
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): JsonResponse
    {
        $dto    = TeamMemberDTO::fromRequest($request->validated());
        $member = $this->teamService->update($teamMember, $dto);

        return response()->json([
            'message' => 'Team member updated.',
            'data'    => new TeamMemberResource($member),
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