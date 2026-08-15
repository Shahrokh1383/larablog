<?php

namespace Modules\Articles\Http\Resources;

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
            'is_editors_pick' => $this->is_editors_pick,
            'published_at'   => $this->published_at,
            'reading_time'   => $this->reading_time,
            'views'          => (int) $this->views,
            'comments_count' => $this->comments_count ?? 0,
            'user'           => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ],
            // Preventing Cross-Module Resource Imports (Article III)
            'category'       => $this->whenLoaded('category', fn($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ]),
            'tags'           => $this->whenLoaded('tags', fn($tags) => $tags->map(fn($t) => [
                'id'   => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
            ])),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}