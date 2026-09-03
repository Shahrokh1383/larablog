<?php

namespace Modules\About\Services;

use Illuminate\Http\UploadedFile;
use Modules\About\Actions\DeleteStoryImageAction;
use Modules\About\Actions\UploadStoryImageAction;
use Modules\About\DTOs\SiteSettingsDTO;
use Modules\About\Models\SiteSetting;

class SettingsService
{
    public function __construct(
        private UploadStoryImageAction $uploadStoryImageAction,
        private DeleteStoryImageAction $deleteStoryImageAction,
    ) {}

    public function getSettings(): SiteSetting
    {
        return SiteSetting::singleton();
    }

    public function updateSettings(SiteSettingsDTO $dto): SiteSetting
    {
        $settings = SiteSetting::singleton();
        $settings->update([
            'call_us_phone' => $dto->callUsPhone,
            'call_us_emails' => $dto->callUsEmails,
            'visit_address' => $dto->visitAddress,
            'social_links' => $dto->socialLinks,
            'story_image' => $dto->storyImage,
        ]);

        return $settings->fresh();
    }

    public function uploadStoryImage(UploadedFile $file): string
    {
        return $this->uploadStoryImageAction->execute($file);
    }

    public function deleteStoryImage(string $url): void
    {
        $deletedPath = $this->deleteStoryImageAction->execute($url);

        $settings = $this->getSettings();

        if ($this->urlPath($settings->story_image) === $deletedPath) {
            $settings->update(['story_image' => null]);
        }
    }

    private function urlPath(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) ? $path : null;
    }
}