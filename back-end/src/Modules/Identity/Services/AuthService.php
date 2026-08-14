<?php

namespace Modules\Identity\Services;

use Modules\Identity\DTOs\UserRegisterDTO;
use Modules\Identity\DTOs\UserLoginDTO;
use Modules\Identity\Actions\CreateUserAction;
use Modules\Identity\Models\User;
use Modules\Identity\Exceptions\InvalidVerificationLinkException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
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

        return ['user' => $user->load('roles')];
    }

    /**
     * Admin login (token-based, no session).
     */
    public function adminLogin(UserLoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('admin-auth-token')->plainTextToken;

        return [
            'user'  => $user->load('roles'),
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
            return;
        }

        Auth::guard('web')->logout();
    }

    /**
     * Verify user's email from signed URL.
     *
     * @return array{message: string, user?: User}
     */
    public function verifyEmail(Request $request): array
    {
        if (! $request->hasValidSignature()) {
            throw new InvalidVerificationLinkException();
        }

        $user = User::findOrFail($request->route('id'));

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
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