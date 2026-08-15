<?php

use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;
use Modules\Identity\Models\User;

it('redirects to provider', function () {
    config(['services.google' => [
        'client_id' => 'id',
        'client_secret' => 'secret',
        'redirect' => 'http://localhost/callback',
    ]]);

    $response = $this->get('/api/oauth/google/redirect');

    $response->assertStatus(302);
    $response->assertRedirectContains('accounts.google.com');
});

it('handles callback', function () {
    config(['services.google' => [
        'client_id' => 'id',
        'client_secret' => 'secret',
        'redirect' => 'http://localhost/callback',
    ]]);

    $user = User::factory()->create(['password' => null]); // OAuth-only user

    $socialUser = new \Laravel\Socialite\Two\User();
    $socialUser->id = '123';
    $socialUser->email = $user->email;
    $socialUser->name = $user->name;

    $providerMock = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
    $providerMock->shouldReceive('stateless')->andReturnSelf();
    $providerMock->shouldReceive('user')->andReturn($socialUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($providerMock);

    $response = $this->get('/api/oauth/google/callback?code=test');

    $response->assertRedirect(config('app.frontend_url') . '/oauth-callback');
    $this->assertAuthenticatedAs($user);
});