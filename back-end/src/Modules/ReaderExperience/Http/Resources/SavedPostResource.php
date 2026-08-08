<?php

namespace Modules\ReaderExperience\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavedPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'saved_at'   => $this->saved_at,
            'post'       => [
                'id'             => $this->post_info->id ?? null,
                'title'          => $this->post_info->title ?? null,
                'slug'           => $this->post_info->slug ?? null,
                'featured_image' => $this->post_info->featured_image ?? null,
                'reading_time'   => $this->post_info->reading_time ?? null,
            ],
        ];
    }
}