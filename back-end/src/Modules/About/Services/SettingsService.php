<?php

namespace Modules\About\Services;

use Modules\About\Models\SiteSetting;
use Modules\About\DTOs\SiteSettingsDTO;

class SettingsService
{
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
        ]);
        return $settings->fresh();
    }
}