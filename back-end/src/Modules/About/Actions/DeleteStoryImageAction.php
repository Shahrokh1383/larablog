<?php

namespace Modules\About\Actions;

use Illuminate\Support\Facades\Storage;
use Modules\About\Exceptions\StoryImageDeletionFailedException;

class DeleteStoryImageAction
{
    private const STORAGE_URL_PREFIX = '/storage/';

    private const STORY_IMAGE_DIRECTORY = 'about/story/';

    public function execute(string $url): void
    {
        $path = parse_url($url, PHP_URL_PATH);

        if ($path === null || $path === false || ! str_starts_with($path, self::STORAGE_URL_PREFIX)) {
            throw new StoryImageDeletionFailedException('The provided URL is not a valid storage URL.');
        }

        $relativePath = substr($path, strlen(self::STORAGE_URL_PREFIX));

        if (str_contains($relativePath, '..') || ! str_starts_with($relativePath, self::STORY_IMAGE_DIRECTORY)) {
            throw new StoryImageDeletionFailedException('The provided URL does not point to a story image.');
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($relativePath)) {
            throw new StoryImageDeletionFailedException('The story image file could not be found.');
        }

        if (! $disk->delete($relativePath)) {
            throw new StoryImageDeletionFailedException('The story image file could not be deleted.');
        }
    }
}