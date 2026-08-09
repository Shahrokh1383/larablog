<?php

namespace Modules\ReaderExperience\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'body'       => $this->body,
            'created_at' => $this->created_at,
            'post'       => [
                'slug'  => $this->post_slug,
                'title' => $this->post_title,
            ],
        ];
    }
}