<?php

namespace Modules\Profile\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'user_id'             => $this->user_id,
            'name'                => $this->user->name ?? null,
            'username'            => $this->user->username ?? null,
            'avatar'              => $this->avatar,
            'bio'                 => $this->bio,
            'expertise'           => $this->expertise,
            'years_of_experience' => $this->years_of_experience,
            'social_links'        => $this->social_links ?? [],
            'posts_count'         => $this->posts_count ?? 0,
            'total_views'         => $this->total_views ?? 0,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}