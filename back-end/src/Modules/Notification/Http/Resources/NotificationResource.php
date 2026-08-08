<?php

namespace Modules\Notification\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->data['type'] ?? null,
            'message'    => $this->data['message'] ?? null,
            'post_id'    => $this->data['post_id'] ?? null,
            'comment_id' => $this->data['comment_id'] ?? null,
            'reply_id'   => $this->data['reply_id'] ?? null,
            'read_at'    => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}