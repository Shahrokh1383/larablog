<?php

namespace Modules\Identity\Http\Controllers\Api;

use Modules\Identity\Http\Requests\RegisterUserRequest;
use Modules\Identity\Http\Requests\LoginUserRequest;
use Modules\Identity\Http\Resources\UserResource;
use Modules\Identity\Services\AuthService;
use Modules\Identity\DTOs\UserRegisterDTO;
use Modules\Identity\DTOs\UserLoginDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function register(RegisterUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $dto = new UserRegisterDTO(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );

        $result = $this->authService->register($dto);

        return response()->json([
            'user' => new UserResource($result['user']),
        ], 201);
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        $dto = new UserLoginDTO(...$request->validated());
        $result = $this->authService->login($dto);

        return response()->json([
            'user' => new UserResource($result['user']),
        ]);
    }

    public function adminLogin(LoginUserRequest $request): JsonResponse
    {
        $dto = new UserLoginDTO(...$request->validated());
        $result = $this->authService->adminLogin($dto);

        return response()->json([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Logged out']);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $result = $this->authService->verifyEmail($request);

        return response()->json([
            'message' => $result['message'],
            'user'    => isset($result['user']) ? new UserResource($result['user']) : null,
        ]);
    }
}