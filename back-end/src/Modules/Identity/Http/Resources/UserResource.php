<?php

namespace Modules\Identity\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'avatar'     => $this->avatar,
            'bio'        => $this->bio,
            'roles'      => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->toArray(), []),
            'created_at' => $this->created_at,
        ];
    }
}