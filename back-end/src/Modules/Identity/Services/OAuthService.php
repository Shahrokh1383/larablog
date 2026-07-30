<?php

namespace Modules\Identity\Services;

use Modules\Identity\DTOs\OAuthCallbackDTO;
use Modules\Identity\Actions\FindOrCreateSocialUserAction;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Two\AbstractProvider;

class OAuthService
{
    public function __construct(
        protected FindOrCreateSocialUserAction $findOrCreate,
    ) {}

    public function redirect(string $provider): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->validateProvider($provider);
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);
        
        // Add ->stateless() to prevent the session dependency in API routes
        return $driver->stateless()->redirect();
    }

    public function callback(OAuthCallbackDTO $dto): array
    {
        $this->validateProvider($dto->provider);

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver($dto->provider);
            $socialUser = $driver->stateless()->user();
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'provider' => ['OAuth callback failed.'],
            ]);
        }

        $user = $this->findOrCreate->execute($socialUser, $dto->provider);

        Auth::login($user);
        $token = $user->createToken('auth-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    protected function validateProvider(string $provider): void
    {
        if (! in_array($provider, ['github', 'facebook', 'google'])) {
            throw ValidationException::withMessages([
                'provider' => ['Unsupported OAuth provider.'],
            ]);
        }
    }
}