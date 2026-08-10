<?php

namespace Modules\Marketing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'email'      => $this->email,
            'is_active'  => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}