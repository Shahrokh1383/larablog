<?php

namespace Modules\Taxonomy\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'posts_count'   => $this->whenNotNull($this->posts_count),
            'authors_count' => $this->whenNotNull($this->authors_count),
        ];
    }
}