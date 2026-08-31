<?php

namespace Modules\Authors\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'featured_image'=> $this->featured_image,
            'published_at' => $this->published_at?->toIso8601String(),
            'views' => $this->views ?? 0,
            'reading_time'  => $this->reading_time ?? 0,
        ];
    }
}