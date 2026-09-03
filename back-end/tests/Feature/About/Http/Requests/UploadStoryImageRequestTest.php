<?php

use Modules\About\Http\Requests\UploadStoryImageRequest;

test('authorize returns true', function () {
    $request = new UploadStoryImageRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules are correct', function () {
    $request = new UploadStoryImageRequest();
    expect($request->rules())->toBe([
        'story_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
    ]);
});