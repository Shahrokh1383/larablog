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
            'posts'         => $this->whenLoaded('posts', function () {
                return $this->posts->map(fn($post) => [
                    'id'             => $post->id,
                    'title'          => $post->title,
                    'slug'           => $post->slug,
                    'excerpt'        => $post->excerpt,
                    'featured_image' => $post->featured_image,
                    'reading_time'   => $post->reading_time,
                    'views'          => $post->views,
                    'comments_count' => $post->comments_count ?? 0,
                    'published_at'   => $post->published_at,
                ])->values();
            }),
        ];
    }
}