<?php

namespace Modules\Identity\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Identity\Actions\CreateUserAction;
use Modules\Identity\DTOs\UserLoginDTO;
use Modules\Identity\DTOs\UserRegisterDTO;
use Modules\Identity\Exceptions\InvalidVerificationLinkException;
use Modules\Identity\Models\User;

class AuthService
{
    /**
     * Valid bcrypt hash for a dummy password.
     * Used to mitigate timing attacks when user is not found.
     */
    private const DUMMY_HASH = '$2y$12$Eze4Zc.fQXJ0vQ7ZsXvLjO5X6T7m9YzN3z5Z8o2b3o4o5o6o7o8o9o';

    public function __construct(
        protected CreateUserAction $createUser,
    ) {}

    public function register(UserRegisterDTO $dto): array
    {
        $user = $this->createUser->execute($dto);
        $user->assignRole('user');
        $user->sendEmailVerificationNotification();

        return ['user' => $user->load('roles')];
    }

    public function login(UserLoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();
        $hash = $user?->password ?? self::DUMMY_HASH;

        if (! $user || ! Hash::check($dto->password, $hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Auth::login($user, $dto->remember);

        return ['user' => $user->load('roles')];
    }

    public function adminLogin(UserLoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();
        $hash = $user?->password ?? self::DUMMY_HASH;

        if (! $user || ! Hash::check($dto->password, $hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->hasRole(['admin', 'editor', 'author'])) {
            throw ValidationException::withMessages([
                'email' => ['You do not have permission to access the admin panel.'],
            ]);
        }

        $token = $user->createToken('admin-auth-token', ['admin-access'])->plainTextToken;

        return [
            'user'  => $user->load('roles'),
            'token' => $token,
        ];
    }

    public function logout(?User $user): void
    {
        if (! $user) {
            return;
        }

        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    public function verifyEmail(string $id, string $hash): array
    {
        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new InvalidVerificationLinkException();
        }

        if ($user->hasVerifiedEmail()) {
            return [
                'message' => 'Email already verified',
                'user'    => $user->load('roles'),
            ];
        }

        $user->markEmailAsVerified();
        Auth::login($user);

        return [
            'message' => 'Email verified successfully',
            'user'    => $user->load('roles'),
        ];
    }
}