<?php

use Modules\Marketing\Http\Requests\StoreContactMessageRequest;
use Shared\Models\User;

test('StoreContactMessageRequest authorizes always', function () {
    $request = new StoreContactMessageRequest();
    expect($request->authorize())->toBeTrue();
});

test('StoreContactMessageRequest rules when user is guest', function () {
    $request = new StoreContactMessageRequest();
    // Simulate no authenticated user
    $request->setUserResolver(fn () => null);

    $rules = $request->rules();
    expect($rules)->toHaveKey('name');
    expect($rules)->toHaveKey('email');
    expect($rules['name'])->toContain('required');
    expect($rules['email'])->toContain('required');
});

test('StoreContactMessageRequest rules when user has name and email', function () {
    $user = User::factory()->make(); // not saved
    $request = new StoreContactMessageRequest();
    $request->setUserResolver(fn () => $user);

    $rules = $request->rules();
    expect($rules['name'])->not->toContain('required');
    expect($rules['email'])->not->toContain('required');
});

test('StoreContactMessageRequest rules when user lacks name or email', function () {
    $user = User::factory()->make(['name' => null, 'email' => null]);
    $request = new StoreContactMessageRequest();
    $request->setUserResolver(fn () => $user);

    $rules = $request->rules();
    expect($rules['name'])->toContain('required');
    expect($rules['email'])->toContain('required');
});