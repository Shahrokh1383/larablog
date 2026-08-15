<?php

use Modules\Identity\DTOs\OAuthCallbackDTO;
use Modules\Identity\DTOs\ResetPasswordDTO;
use Modules\Identity\DTOs\UserLoginDTO;
use Modules\Identity\DTOs\UserRegisterDTO;

it('creates OAuthCallbackDTO', function () {
    $dto = new OAuthCallbackDTO('google', 'authcode');
    expect($dto->provider)->toBe('google');
    expect($dto->code)->toBe('authcode');
});

it('creates ResetPasswordDTO', function () {
    $dto = new ResetPasswordDTO('token123', 'user@example.com', 'password');
    expect($dto->token)->toBe('token123');
    expect($dto->email)->toBe('user@example.com');
    expect($dto->password)->toBe('password');
});

it('creates UserLoginDTO', function () {
    $dto = new UserLoginDTO('user@example.com', 'password', true);
    expect($dto->email)->toBe('user@example.com');
    expect($dto->password)->toBe('password');
    expect($dto->remember)->toBeTrue();
});

it('creates UserRegisterDTO', function () {
    $dto = new UserRegisterDTO('John', 'john@example.com', 'password');
    expect($dto->name)->toBe('John');
    expect($dto->email)->toBe('john@example.com');
    expect($dto->password)->toBe('password');
});