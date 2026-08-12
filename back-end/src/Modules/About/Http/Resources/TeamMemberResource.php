<?php

namespace Modules\About\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Identity\Http\Resources\UserResource;

class TeamMemberResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'display_name' => $this->display_name,
            'position' => $this->position,
            'bio' => $this->bio,
            'photo' => $this->photo,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}