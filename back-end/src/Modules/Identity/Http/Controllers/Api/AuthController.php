<?php

namespace Modules\Identity\Http\Controllers\Api;

use Modules\Identity\Http\Requests\StoreRegisterRequest;
use Modules\Identity\Http\Requests\StoreLoginRequest;
use Modules\Identity\Http\Resources\UserResource;
use Modules\Identity\Services\AuthService;
use Modules\Identity\DTOs\UserRegisterDTO;
use Modules\Identity\DTOs\UserLoginDTO;
use Modules\Identity\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function register(StoreRegisterRequest $request): JsonResponse
    {
        $dto = new UserRegisterDTO(...$request->validated());
        $result = $this->authService->register($dto);

        return response()->json([
            'user'  => new UserResource($result['user']),
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

    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout(Auth::user());

        return response()->json(['message' => 'Logged out']);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return response()->json(['message' => 'Invalid or expired link'], 403);
        }

        $user = User::findOrFail($request->route('id'));

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        $user->markEmailAsVerified();

        // Auto-login after verification (standard SPA flow)
        Auth::login($user);

        return response()->json([
            'message' => 'Email verified successfully',
            'user'    => new UserResource($user->load('roles')), // Eager load here as well
        ]);
    }
}