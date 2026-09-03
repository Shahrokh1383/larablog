<?php

use Modules\Marketing\Http\Requests\StoreContactMessageRequest;
use Shared\Models\User;

function ruleStrings(array $rules): array
{
    return array_map(fn ($rule) => (string) $rule, $rules);
}

test('StoreContactMessageRequest authorizes always', function () {
    $request = new StoreContactMessageRequest();
    expect($request->authorize())->toBeTrue();
});

test('StoreContactMessageRequest rules when user is guest', function () {
    $request = new StoreContactMessageRequest();
    $request->setUserResolver(fn () => null);

    $rules = $request->rules();
    expect($rules)->toHaveKey('name');
    expect($rules)->toHaveKey('email');

    $nameRules = ruleStrings($rules['name']);
    $emailRules = ruleStrings($rules['email']);
    expect($nameRules)->toContain('required');
    expect($emailRules)->toContain('required');
});

test('StoreContactMessageRequest rules when user has name and email', function () {
    $user = User::factory()->make(); // not saved
    $request = new StoreContactMessageRequest();
    $request->setUserResolver(fn () => $user);

    $rules = $request->rules();
    $nameRules = ruleStrings($rules['name']);
    $emailRules = ruleStrings($rules['email']);
    expect($nameRules)->not->toContain('required');
    expect($emailRules)->not->toContain('required');
});

test('StoreContactMessageRequest rules when user lacks name or email', function () {
    $user = User::factory()->make(['name' => null, 'email' => null]);
    $request = new StoreContactMessageRequest();
    $request->setUserResolver(fn () => $user);

    $rules = $request->rules();
    $nameRules = ruleStrings($rules['name']);
    $emailRules = ruleStrings($rules['email']);
    expect($nameRules)->toContain('required');
    expect($emailRules)->toContain('required');
});