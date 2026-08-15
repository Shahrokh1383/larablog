<?php

use Illuminate\Support\Facades\Password;
use Modules\Identity\Models\User;

it('sends reset link', function () {
    Password::shouldReceive('sendResetLink')->once()->andReturn(Password::RESET_LINK_SENT);

    $response = $this->postJson('/api/forgot-password', [
        'email' => 'user@example.com',
    ]);

    $response->assertOk()->assertJson(['message' => 'If the email address is registered, a password reset link has been sent.']);
});

it('resets password', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    Password::shouldReceive('reset')->once()->andReturn(Password::PASSWORD_RESET);

    $response = $this->postJson('/api/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $response->assertOk()->assertJson(['message' => __('passwords.reset')]);
});