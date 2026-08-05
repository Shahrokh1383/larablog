<?php

namespace Modules\Engagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'post_id'    => $this->post_id,
            'parent_id'  => $this->parent_id,
            'body'       => $this->body,
            'author'     => $this->user ? [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ] : [
                'name' => $this->name,
            ],
            'replies'    => CommentPublicResource::collection($this->whenLoaded('replies_tree')),
            'created_at' => $this->created_at,
        ];
    }
}