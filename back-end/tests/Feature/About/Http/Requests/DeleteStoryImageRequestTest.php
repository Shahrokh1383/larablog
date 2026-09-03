<?php

use Modules\About\Http\Requests\DeleteStoryImageRequest;

test('authorize returns true', function () {
    $request = new DeleteStoryImageRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules are correct', function () {
    $request = new DeleteStoryImageRequest();
    expect($request->rules())->toBe([
        'url' => ['required', 'string', 'url'],
    ]);
});

test('imageUrl returns validated url', function () {
    $request = new DeleteStoryImageRequest();
    $request->merge(['url' => 'http://example.com/storage/about/story/image.jpg']);
    $request->setContainer(app());
    $request->validateResolved();
    expect($request->imageUrl())->toBe('http://example.com/storage/about/story/image.jpg');
});