<?php

namespace Modules\Identity\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Modules\Identity\DTOs\UserRegisterDTO;
use Modules\Identity\Models\SocialAccount;
use Modules\Identity\Models\User;

class FindOrCreateSocialUserAction
{
    public function __construct(
        protected CreateUserWithUniqueUsernameAction $createUser,
    ) {}

    public function execute(SocialiteUser $socialUser, string $provider): User
    {
        $socialId = $socialUser->getId();

        // 1. Try to find an existing social account for this provider + provider_id
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialId)
            ->first();

        if ($socialAccount) {
            return $socialAccount->user;
        }

        // 2. Try to find a user with the same email
        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            // If the user was originally created via OAuth (password is null),
            // we can safely link this new provider.
            if (is_null($existingUser->password)) {
                return DB::transaction(function () use ($existingUser, $provider, $socialId) {
                    $this->linkSocialAccount($existingUser, $provider, $socialId);

                    return $existingUser;
                });
            }

            // Otherwise, the account has a password; require manual linking.
            throw ValidationException::withMessages([
                'email' => ['This email is already registered. Please log in with your password and link your social account from your profile settings.'],
            ]);
        }

        // 3. Create a new OAuth-only user
        $name = $socialUser->getName()
            ?? $socialUser->getNickname()
            ?? $socialUser->getEmail();

        $createDto = new UserRegisterDTO(
            name: $name,
            email: $socialUser->getEmail(),
            password: null,
        );

        return DB::transaction(function () use ($createDto, $provider, $socialId) {
            $user = $this->createUser->execute($createDto);
            $user->assignRole('user');
            $user->email_verified_at = now();
            $user->save();

            $this->linkSocialAccount($user, $provider, $socialId);

            return $user;
        });
    }

    private function linkSocialAccount(User $user, string $provider, string $providerId): void
    {
        SocialAccount::create([
            'user_id'     => $user->id,
            'provider'    => $provider,
            'provider_id' => $providerId,
        ]);
    }
}