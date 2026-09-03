<?php

use Modules\Marketing\Http\Requests\SendNewsletterRequest;
use Shared\Models\User;

beforeEach(function () {
    $this->admin = Mockery::mock(User::class);
    $this->admin->shouldReceive('hasRole')->with('admin')->andReturn(true);
});

test('SendNewsletterRequest authorizes only admin', function () {
    $request = new SendNewsletterRequest();
    $request->setUserResolver(fn () => $this->admin);
    expect($request->authorize())->toBeTrue();

    $nonAdmin = Mockery::mock(User::class);
    $nonAdmin->shouldReceive('hasRole')->with('admin')->andReturn(false);
    $request->setUserResolver(fn () => $nonAdmin);
    expect($request->authorize())->toBeFalse();
});

test('SendNewsletterRequest rules are correct', function () {
    $request = new SendNewsletterRequest();
    $rules = $request->rules();
    expect($rules)->toHaveKey('send_to_all');
    expect($rules['send_to_all'])->toContain('required', 'boolean');
    expect($rules)->toHaveKey('subscriber_ids');
    expect($rules['subscriber_ids'])->toContain('nullable', 'array');
    expect($rules['subscriber_ids.*'])->toContain('uuid', 'exists:marketing_subscribers,id');
});