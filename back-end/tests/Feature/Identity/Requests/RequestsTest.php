<?php

use Illuminate\Validation\Rules\In;
use Modules\Identity\Http\Requests\ForgotPasswordRequest;
use Modules\Identity\Http\Requests\IndexUserRequest;
use Modules\Identity\Http\Requests\LoginUserRequest;
use Modules\Identity\Http\Requests\OAuthCallbackRequest;
use Modules\Identity\Http\Requests\RegisterUserRequest;
use Modules\Identity\Http\Requests\ResetPasswordRequest;
use Modules\Identity\Http\Requests\UpdateUserPasswordRequest;
use Modules\Identity\Http\Requests\UpdateUserRoleRequest;

it('ForgotPasswordRequest has correct rules', function () {
    $request = new ForgotPasswordRequest();
    expect($request->authorize())->toBeTrue();
    expect($request->rules())->toBe(['email' => ['required', 'email']]);
});

it('IndexUserRequest has correct rules', function () {
    $request = new IndexUserRequest();
    expect($request->authorize())->toBeTrue();
    expect($request->rules())->toBe([
        'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        'search'   => ['sometimes', 'string', 'max:255'],
    ]);
});

it('LoginUserRequest has correct rules', function () {
    $request = new LoginUserRequest();
    expect($request->authorize())->toBeTrue();
    expect($request->rules())->toBe([
        'email'    => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
        'remember' => ['boolean'],
    ]);
});

it('OAuthCallbackRequest has correct rules', function () {
    $request = new OAuthCallbackRequest();
    expect($request->authorize())->toBeTrue();
    expect($request->rules())->toBe(['code' => ['required', 'string']]);
});

it('RegisterUserRequest has correct rules', function () {
    $request = new RegisterUserRequest();
    expect($request->authorize())->toBeTrue();
    expect($request->rules())->toBe([
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);
});

it('ResetPasswordRequest has correct rules', function () {
    $request = new ResetPasswordRequest();
    expect($request->authorize())->toBeTrue();
    expect($request->rules())->toBe([
        'token'    => ['required', 'string'],
        'email'    => ['required', 'email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);
});

it('UpdateUserPasswordRequest has correct rules', function () {
    $request = new UpdateUserPasswordRequest();
    expect($request->authorize())->toBeTrue();
    expect($request->rules())->toBe(['password' => ['required', 'string', 'min:8', 'confirmed']]);
});

it('UpdateUserRoleRequest has correct rules', function () {
    $request = new UpdateUserRoleRequest();
    expect($request->authorize())->toBeTrue();

    $roleRules = $request->rules()['role'];
    expect($roleRules[0])->toBe('required');
    expect($roleRules[1])->toBe('string');
    expect($roleRules[2])->toBeInstanceOf(In::class);
    expect((string) $roleRules[2])->toBe('in:"admin","editor","author","user"');
});