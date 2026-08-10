<?php

namespace Modules\Identity\Services;

use Modules\Identity\DTOs\UserRegisterDTO;
use Modules\Identity\DTOs\UserLoginDTO;
use Modules\Identity\Actions\CreateUserAction;
use Modules\Identity\Actions\AssignRoleAction;
use Modules\Identity\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function __construct(
        protected CreateUserAction $createUser,
        protected AssignRoleAction $assignRole,
    ) {}

    public function register(UserRegisterDTO $dto): array
    {
        $user = $this->createUser->execute($dto);
        $this->assignRole->execute($user, 'user');

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        return ['user' => $user];
    }

    public function login(UserLoginDTO $dto): array
    {
        if (! Auth::attempt([
            'email'    => $dto->email,
            'password' => $dto->password,
        ], $dto->remember)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        // For token-based auth (e.g. admin panel), delete the token
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
            return;
        }

        // For cookie-based SPA auth, log the user out of the session
        Auth::guard('web')->logout();
    }
}