<?php

use Modules\Identity\Models\User;
use Modules\Articles\Http\Requests\DeleteImageRequest;
use Modules\Articles\Http\Requests\IndexPostRequest;
use Modules\Articles\Http\Requests\IndexPostsByCategoryRequest;
use Modules\Articles\Http\Requests\IndexPostsByTagRequest;
use Modules\Articles\Http\Requests\StorePostRequest;
use Modules\Articles\Http\Requests\UpdatePostRequest;
use Modules\Articles\Http\Requests\UploadImageRequest;

test('IndexPostRequest contract', function () {
    $request = new IndexPostRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'search'          => ['nullable', 'string', 'max:255'],
            'per_page'        => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page'            => ['sometimes', 'integer', 'min:1'],
            'is_editors_pick' => ['nullable', 'boolean'],
        ]);
});

test('IndexPostsByCategoryRequest contract', function () {
    $request = new IndexPostsByCategoryRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'sort'     => ['nullable', 'string', 'in:newest,oldest,most_popular'],
            'per_page' => ['integer', 'min:1', 'max:50'],
        ]);
});

test('IndexPostsByTagRequest contract', function () {
    $request = new IndexPostsByTagRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'sort'     => ['nullable', 'string', 'in:newest,oldest,most_popular'],
            'per_page' => ['integer', 'min:1', 'max:50'],
        ]);
});

test('StorePostRequest contract', function () {
    $request = new StorePostRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'title'           => ['required', 'string', 'max:255'],
            'body'            => ['required', 'string'],
            'excerpt'         => ['nullable', 'string'],
            'featured_image'  => ['nullable', 'url'],
            'is_published'    => ['sometimes', 'boolean'],
            'is_editors_pick' => ['sometimes', 'boolean'],
            'category_id'     => ['nullable', 'string'],
            'tag_ids'         => ['nullable', 'array', 'distinct'],
            'tag_ids.*'       => ['string'],
        ]);
});

test('UpdatePostRequest contract', function () {
    $request = new UpdatePostRequest();

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'title'          => ['sometimes', 'string', 'max:255'],
            'body'           => ['sometimes', 'string'],
            'excerpt'        => ['nullable', 'string'],
            'featured_image' => ['nullable', 'url'],
            'is_published'   => ['sometimes', 'boolean'],
            'is_editors_pick'=> ['sometimes', 'boolean'],
            'category_id'    => ['nullable', 'string'],
            'tag_ids'        => ['nullable', 'array', 'distinct'],
            'tag_ids.*'      => ['string'],
        ]);
});

test('UploadImageRequest authorizes admin', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $request = new UploadImageRequest();
    $request->setUserResolver(fn () => $user);

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'image' => ['required', 'image', 'max:5120'],
        ]);
});

test('UploadImageRequest denies guest', function () {
    $request = new UploadImageRequest();

    expect($request->authorize())->toBeFalse();
});

test('DeleteImageRequest authorizes admin and denies author', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $adminRequest = new DeleteImageRequest();
    $adminRequest->setUserResolver(fn () => $admin);
    expect($adminRequest->authorize())->toBeTrue();

    $author = User::factory()->create();
    $author->assignRole('author');

    $authorRequest = new DeleteImageRequest();
    $authorRequest->setUserResolver(fn () => $author);
    expect($authorRequest->authorize())->toBeFalse();

    $adminRequest = new DeleteImageRequest();
    expect($adminRequest->authorize())->toBeFalse();
});