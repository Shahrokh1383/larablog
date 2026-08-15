<?php

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Modules\Identity\Actions\FindOrCreateSocialUserAction;
use Modules\Identity\DTOs\OAuthCallbackDTO;
use Modules\Identity\Models\User;
use Modules\Identity\Services\OAuthService;
use Shared\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\RedirectResponse;

beforeEach(function () {
    $this->findOrCreate = Mockery::mock(FindOrCreateSocialUserAction::class);
    $this->oauthService = new OAuthService($this->findOrCreate);
});

it('redirects to provider', function () {
    config(['services.google' => [
        'client_id' => 'id',
        'client_secret' => 'secret',
        'redirect' => 'http://localhost/callback',
    ]]);

    $providerMock = Mockery::mock(AbstractProvider::class);
    $providerMock->shouldReceive('stateless')->andReturnSelf();
    $providerMock->shouldReceive('redirect')->andReturn(new RedirectResponse('https://accounts.google.com'));

    Socialite::shouldReceive('driver')->with('google')->andReturn($providerMock);

    $response = $this->oauthService->redirect('google');

    expect($response)->toBeInstanceOf(RedirectResponse::class);
});

it('throws DomainException for unsupported provider', function () {
    config(['services' => []]);

    $this->expectException(DomainException::class);
    $this->oauthService->redirect('unknown');
});

it('calls back and logs in user', function () {
    config(['services.google' => [
        'client_id' => 'id',
        'client_secret' => 'secret',
        'redirect' => 'http://localhost/callback',
    ]]);

    $socialUser = Mockery::mock(SocialiteUser::class);
    $providerMock = Mockery::mock(AbstractProvider::class);
    $providerMock->shouldReceive('stateless')->andReturnSelf();
    $providerMock->shouldReceive('user')->andReturn($socialUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($providerMock);

    $user = User::factory()->create();
    $this->findOrCreate->shouldReceive('execute')->once()->with($socialUser, 'google')->andReturn($user);

    $dto = new OAuthCallbackDTO('google', 'auth-code');
    $result = $this->oauthService->callback($dto);

    expect($result->id)->toBe($user->id);
    expect(Auth::user()->id)->toBe($user->id);
});

it('throws DomainException when socialite fails', function () {
    config(['services.google' => [
        'client_id' => 'id',
        'client_secret' => 'secret',
        'redirect' => 'http://localhost/callback',
    ]]);

    $providerMock = Mockery::mock(AbstractProvider::class);
    $providerMock->shouldReceive('stateless')->andReturnSelf();
    $providerMock->shouldReceive('user')->andThrow(new \Exception('OAuth error'));

    Socialite::shouldReceive('driver')->with('google')->andReturn($providerMock);

    $dto = new OAuthCallbackDTO('google', 'auth-code');
    $this->expectException(DomainException::class);
    $this->oauthService->callback($dto);
});