<?php

namespace Modules\Identity\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $profile = DB::table('profiles')
            ->where('user_id', $this->id)
            ->first(['avatar', 'bio']);

        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'avatar'     => $profile?->avatar,
            'bio'        => $profile?->bio,
            'roles'      => $this->roles->pluck('name')->toArray(),
            'expertise' => $profile?->expertise,
            'created_at' => $this->created_at,
        ];
    }
}