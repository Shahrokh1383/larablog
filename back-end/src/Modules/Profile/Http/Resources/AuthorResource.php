<?php

namespace Modules\Profile\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->user->name ?? null,
            'username'            => $this->user->username ?? null,
            'avatar'              => $this->avatar,
            'bio'                 => $this->bio,
            'expertise'           => $this->expertise,
            'social_links'        => $this->social_links ?? [],
            'posts_count'         => $this->posts_count ?? 0,
            'total_views'         => $this->total_views ?? 0,
        ];
    }
}