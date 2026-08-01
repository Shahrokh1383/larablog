<?php

namespace Modules\Identity\Http\Controllers\Api;

use Modules\Identity\Models\User;
use Modules\Identity\Services\UserService;
use Modules\Identity\Http\Resources\UserResource;
use Modules\Identity\Http\Requests\UpdateUserRoleRequest;
use Modules\Identity\Http\Requests\UpdateUserPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getAllUsers(
            $request->input('per_page', 15),
            $request->input('search')
        );
        
        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->updateRole($user, $request->validated('role'));
        
        return response()->json([
            'message' => 'Role updated successfully.',
            'user' => new UserResource($user),
        ]);
    }

    public function updatePassword(UpdateUserPasswordRequest $request, User $user): JsonResponse
    {
        $this->userService->updatePassword($user, $request->validated('password'));
        
        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}