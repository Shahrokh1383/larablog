<?php

namespace Modules\Marketing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'subject'    => $this->subject,
            'message'    => $this->message,
            'is_read'    => $this->is_read,
            'author'     => $this->user ? [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ] : [
                'name'  => $this->name,
                'email' => $this->email,
            ],
            'created_at' => $this->created_at,
        ];
    }
}