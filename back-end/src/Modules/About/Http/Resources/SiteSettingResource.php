<?php

namespace Modules\About\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SiteSettingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'call_us_phone' => $this->call_us_phone,
            'call_us_emails' => $this->call_us_emails,
            'visit_address' => $this->visit_address,
            'social_links' => $this->social_links,
            'story_image' => $this->story_image,
        ];
    }
}