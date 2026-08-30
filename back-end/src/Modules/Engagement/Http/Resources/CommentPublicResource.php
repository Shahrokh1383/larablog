<?php

namespace Modules\Engagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'post_id'          => $this->post_id,
            'parent_id'        => $this->parent_id,
            'body'             => $this->body,
            'is_approved'      => $this->is_approved,
            'author'           => $this->user ? [
                'id'     => $this->user->id,
                'name'   => $this->user->name,
                'avatar' => $this->avatar ?? null,
            ] : [
                'name'   => $this->name,
                'avatar' => null,
            ],
            'replies'          => CommentPublicResource::collection($this->whenLoaded('replies')),
            'replies_count'    => $this->replies_count ?? 0,
            'replies_has_more' => $this->replies_has_more ?? false,
            'created_at'       => $this->created_at,
        ];
    }
}