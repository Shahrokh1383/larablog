<?php

use Modules\Marketing\Http\Requests\StoreSubscriberRequest;

test('StoreSubscriberRequest authorizes always', function () {
    $request = new StoreSubscriberRequest();
    expect($request->authorize())->toBeTrue();
});

test('StoreSubscriberRequest defines correct validation rules', function () {
    $request = new StoreSubscriberRequest();
    $rules = $request->rules();

    expect($rules)->toBe([
        'email' => ['required', 'email', 'max:255'],
    ]);
});