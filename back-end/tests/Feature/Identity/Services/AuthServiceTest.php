<?php

namespace Modules\Identity\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Identity\Actions\CreateUserWithUniqueUsernameAction;
use Modules\Identity\DTOs\UserLoginDTO;
use Modules\Identity\DTOs\UserRegisterDTO;
use Modules\Identity\Models\User;
use Shared\Exceptions\DomainException;

class AuthService
{
    private const DUMMY_HASH = '$2y$12$Eze4Zc.fQXJ0vQ7ZsXvLjO5X6T7m9YzN3z5Z8o2b3o4o5o6o7o8o9o';

    public function __construct(
        protected CreateUserWithUniqueUsernameAction $createUser,
    ) {}

    public function register(UserRegisterDTO $dto): array
    {
        $user = DB::transaction(function () use ($dto) {
            $user = $this->createUser->execute($dto);
            $user->assignRole('user');

            return $user;
        });

        $user->sendEmailVerificationNotification();

        return ['user' => $user->load('roles')];
    }

    public function login(UserLoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();
        $hash = $user?->password ?? self::DUMMY_HASH;

        $passwordValid = Hash::check($dto->password, $hash);
        $isValid = ($user !== null) && $passwordValid;

        if (! $isValid) {
            throw new DomainException('The provided credentials are incorrect.', 422);
        }

        Auth::login($user, $dto->remember);

        return ['user' => $user->load('roles')];
    }

    public function adminLogin(UserLoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();
        $hash = $user?->password ?? self::DUMMY_HASH;

        $passwordValid = Hash::check($dto->password, $hash);
        $isValid = ($user !== null) && $passwordValid;

        if (! $isValid) {
            throw new DomainException('The provided credentials are incorrect.', 422);
        }

        if (! $user->hasRole(config('permissions.admin_roles'))) {
            throw new DomainException('You do not have permission to access the admin panel.', 403);
        }

        $token = $user->createToken('admin-auth-token')->plainTextToken;

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

        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
    }

    public function getAuthenticatedUser(User $user): User
    {
        return $user->load('roles');
    }

    public function verifyEmail(string $id, string $hash): array
    {
        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new DomainException('Invalid or expired verification link.', 404);
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