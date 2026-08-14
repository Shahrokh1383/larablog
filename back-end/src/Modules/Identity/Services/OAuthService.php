<?php

namespace Modules\Identity\Services;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Modules\Identity\Actions\FindOrCreateSocialUserAction;
use Modules\Identity\DTOs\OAuthCallbackDTO;
use Modules\Identity\Exceptions\OAuthCallbackFailedException;
use Modules\Identity\Exceptions\UnsupportedOAuthProviderException;
use Modules\Identity\Models\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OAuthService
{
    public function __construct(
        protected FindOrCreateSocialUserAction $findOrCreate,
    ) {}

    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver->redirect();
    }

    public function callback(OAuthCallbackDTO $dto): User
    {
        $this->validateProvider($dto->provider);

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver($dto->provider);
            $socialUser = $driver->user();
        } catch (\Exception $e) {
            throw new OAuthCallbackFailedException();
        }

        $user = $this->findOrCreate->execute($socialUser, $dto->provider);

        Auth::login($user);

        return $user;
    }

    protected function validateProvider(string $provider): void
    {
        $allowedProviders = array_keys(array_filter(config('services', []), function ($config) {
            return isset($config['client_id'], $config['client_secret'], $config['redirect']);
        }));

        if (! in_array($provider, $allowedProviders, true)) {
            throw new UnsupportedOAuthProviderException();
        }
    }
}