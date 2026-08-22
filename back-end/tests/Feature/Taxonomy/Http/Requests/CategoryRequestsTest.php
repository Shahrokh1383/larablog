<?php

use Modules\Taxonomy\Http\Requests\IndexCategoryPublicRequest;
use Modules\Taxonomy\Http\Requests\IndexCategoryRequest;
use Modules\Taxonomy\Http\Requests\ShowCategoryPostsRequest;
use Modules\Taxonomy\Http\Requests\StoreCategoryRequest;
use Modules\Taxonomy\Http\Requests\UpdateCategoryRequest;

test('IndexCategoryPublicRequest contract', function () {
    $request = new IndexCategoryPublicRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
});

test('IndexCategoryRequest contract', function () {
    $request = new IndexCategoryRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
});

test('ShowCategoryPostsRequest contract', function () {
    $request = new ShowCategoryPostsRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'sort' => ['nullable', 'string', 'in:newest,oldest,most_popular'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
});

test('StoreCategoryRequest contract', function () {
    $request = new StoreCategoryRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'name' => ['required', 'string', 'max:255'],
        ]);
});

test('UpdateCategoryRequest contract', function () {
    $request = new UpdateCategoryRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'name' => ['required', 'string', 'max:255'],
        ]);
});