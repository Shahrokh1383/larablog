<?php

namespace Modules\Content\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'slug'           => $this->slug,
            'body'           => $this->body,
            'excerpt'        => $this->excerpt,
            'featured_image' => $this->featured_image,
            'is_published'   => $this->is_published,
            'published_at'   => $this->published_at,
            'reading_time'   => $this->reading_time,
            'views'          => (int) $this->views,
            'comments_count' => $this->comments_count ?? 0,
            'user'           => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ],
            'category'       => new CategoryResource($this->whenLoaded('category')),
            'tags'           => TagResource::collection($this->whenLoaded('tags')),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}