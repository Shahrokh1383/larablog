<?php

use Illuminate\Support\Facades\Password;
use Modules\Identity\Actions\RevokeUserSessionsAction;
use Modules\Identity\DTOs\ResetPasswordDTO;
use Modules\Identity\Models\User;
use Modules\Identity\Services\PasswordResetService;
use Shared\Exceptions\DomainException;

beforeEach(function () {
    $this->revokeSessions = Mockery::mock(RevokeUserSessionsAction::class);
    $this->service = new PasswordResetService($this->revokeSessions);
});

it('sends reset link', function () {
    Password::shouldReceive('sendResetLink')->once()->with(['email' => 'user@example.com'])->andReturn(Password::RESET_LINK_SENT);

    $message = $this->service->sendResetLink('user@example.com');

    expect($message)->toBe('If the email address is registered, a password reset link has been sent.');
});

it('resets password successfully', function () {
    $user = User::factory()->create();
    $dto = new ResetPasswordDTO('valid-token', $user->email, 'newpassword');

    Password::shouldReceive('reset')->once()->with([
        'token' => $dto->token,
        'email' => $dto->email,
        'password' => $dto->password,
        'password_confirmation' => $dto->password,
    ], Mockery::on(function ($callback) use ($user) {
        $callback($user, 'newpassword');
        return true;
    }))->andReturn(Password::PASSWORD_RESET);

    $this->revokeSessions->shouldReceive('execute')->once()->with($user);

    $message = $this->service->reset($dto);

    expect($message)->toBe(__('passwords.reset'));
    expect($user->fresh()->password)->not->toBe('old-password');
});

it('throws DomainException when reset fails', function () {
    $user = User::factory()->create();
    $dto = new ResetPasswordDTO('invalid-token', $user->email, 'newpassword');

    Password::shouldReceive('reset')->once()->andReturn(Password::INVALID_TOKEN);

    $this->expectException(DomainException::class);
    $this->service->reset($dto);
});