<?php

namespace Modules\Identity\Http\Controllers\Api;

use Modules\Identity\Http\Requests\StoreRegisterRequest;
use Modules\Identity\Http\Requests\StoreLoginRequest;
use Modules\Identity\Http\Resources\UserResource;
use Modules\Identity\Services\AuthService;
use Modules\Identity\DTOs\UserRegisterDTO;
use Modules\Identity\DTOs\UserLoginDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function register(StoreRegisterRequest $request): JsonResponse
    {
        $dto = new UserRegisterDTO(...$request->validated());
        $result = $this->authService->register($dto);

        return response()->json([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 201);
    }

    public function login(StoreLoginRequest $request): JsonResponse
    {
        $dto = new UserLoginDTO(...$request->validated());
        $result = $this->authService->login($dto);

        return response()->json([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout(auth()->user());

        return response()->json(['message' => 'Logged out']);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        // This can be handled by Laravel's default email verification route if we enable it,
        // but we can also create our own. We'll add a simple verification using signed URLs.
        // I'll implement a standard verify method that fulfills MustVerifyEmail interface.
        if ($request->hasValidSignature()) {
            $user = User::findOrFail($request->id);
            if (! hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
                abort(403);
            }
            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email already verified']);
            }
            $user->markEmailAsVerified();
            return response()->json(['message' => 'Email verified successfully']);
        }
        return response()->json(['message' => 'Invalid or expired link'], 403);
    }
}