<?php

use Modules\Taxonomy\Http\Requests\IndexTagPublicRequest;
use Modules\Taxonomy\Http\Requests\IndexTagRequest;
use Modules\Taxonomy\Http\Requests\StoreTagRequest;
use Modules\Taxonomy\Http\Requests\UpdateTagRequest;

test('IndexTagPublicRequest contract', function () {
    $request = new IndexTagPublicRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['integer', 'min:1', 'max:50'],
        ]);
});

test('IndexTagRequest contract', function () {
    $request = new IndexTagRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
});

test('StoreTagRequest contract', function () {
    $request = new StoreTagRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'name' => ['required', 'string', 'max:255'],
        ]);
});

test('UpdateTagRequest contract', function () {
    $request = new UpdateTagRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'name' => ['required', 'string', 'max:255'],
        ]);
});