<?php
namespace Modules\Content\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'posts_count'    => $this->whenNotNull($this->posts_count),
            'total_views'    => $this->whenNotNull($this->posts_sum_views), // For popular tags
        ];
    }
}