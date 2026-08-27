<?php

use Modules\Articles\Actions\DeleteImageAction;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('deletes valid post image', function () {
    Storage::disk('public')->put('posts/images/example.jpg', 'content');
    $url = Storage::disk('public')->url('posts/images/example.jpg');

    $result = app(DeleteImageAction::class)->execute($url);

    expect($result)->toBeTrue()
        ->and(Storage::disk('public')->exists('posts/images/example.jpg'))->toBeFalse();
});

it('returns false when file does not exist', function () {
    $url = 'http://localhost/storage/posts/images/missing.jpg';

    $result = app(DeleteImageAction::class)->execute($url);

    expect($result)->toBeFalse();
});

it('returns false for invalid path', function () {
    $result = app(DeleteImageAction::class)->execute('http://localhost/storage/other/file.jpg');

    expect($result)->toBeFalse();
});