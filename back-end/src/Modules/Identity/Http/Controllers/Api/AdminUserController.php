<?php

namespace Modules\Identity\Http\Controllers\Api;

use Modules\Identity\Models\User;
use Modules\Identity\Services\UserService;
use Modules\Identity\Http\Resources\UserResource;
use Modules\Identity\Http\Requests\UpdateUserRoleRequest;
use Modules\Identity\Http\Requests\UpdateUserPasswordRequest;
use Modules\Identity\Http\Requests\IndexUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminUserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(IndexUserRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->getAllUsers(
            $request->validated('per_page', 15),
            $request->validated('search')
        );

        return UserResource::collection($users);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): JsonResponse
    {
        $this->authorize('updateRole', $user);

        $user = $this->userService->updateRole($user, $request->validated('role'));

        return response()->json([
            'message' => 'Role updated successfully.',
            'user' => new UserResource($user),
        ]);
    }

    public function updatePassword(UpdateUserPasswordRequest $request, User $user): JsonResponse
    {
        $this->authorize('updatePassword', $user);

        $this->userService->updatePassword($user, $request->validated('password'));

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->userService->deleteAccount($user->id);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}