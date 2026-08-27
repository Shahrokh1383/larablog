<?php

namespace Modules\Articles\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostPublicResource extends JsonResource
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
            'reading_time'   => $this->reading_time,
            'views'          => $this->views,
            'comments_count' => $this->comments_count ?? 0,
            'is_saved'       => $this->is_saved ?? false,
            'published_at'   => $this->published_at,
            'is_editors_pick'=> $this->is_editors_pick,
            'category'       => $this->when(isset($this->category_detail), $this->category_detail),
            'tags'           => $this->when(isset($this->tags_detail), $this->tags_detail),
            'author'         => $this->when(isset($this->author), $this->author),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}