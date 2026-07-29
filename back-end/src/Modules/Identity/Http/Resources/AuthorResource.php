<?php

namespace Modules\Identity\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => $this->resource->id,
            'username' => $this->resource->username,
            'name'     => $this->resource->name,
            'avatar'   => $this->resource->avatar,
            'bio'      => $this->resource->bio,
        ];
    }
}