<?php

use Modules\Articles\Actions\UploadImageAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uploads image and returns URL', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->image('post.jpg');

    $url = app(UploadImageAction::class)->execute($file);

    expect($url)->toBeString()
        ->and($url)->toContain('/storage/posts/images/');
    Storage::disk('public')->assertExists('posts/images/'.$file->hashName());
});