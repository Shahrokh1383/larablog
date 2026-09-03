<?php

use Modules\Marketing\Http\Requests\ReplyToContactMessageRequest;
use Shared\Models\User;

test('ReplyToContactMessageRequest authorizes only admin', function () {
    $admin = Mockery::mock(User::class);
    $admin->shouldReceive('hasRole')->with('admin')->andReturn(true);

    $request = new ReplyToContactMessageRequest();
    $request->setUserResolver(fn () => $admin);
    expect($request->authorize())->toBeTrue();

    $nonAdmin = Mockery::mock(User::class);
    $nonAdmin->shouldReceive('hasRole')->with('admin')->andReturn(false);
    $request->setUserResolver(fn () => $nonAdmin);
    expect($request->authorize())->toBeFalse();
});

test('ReplyToContactMessageRequest defines correct validation rules', function () {
    $request = new ReplyToContactMessageRequest();
    expect($request->rules())->toBe([
        'reply_body' => ['required', 'string', 'max:5000'],
    ]);
});