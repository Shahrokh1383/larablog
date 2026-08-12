<?php

namespace Modules\About\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class TeamMemberResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $this->user;

        // Build user data without crossing module boundaries
        $userData = null;
        if ($user) {
            $profile = DB::table('profiles')->where('user_id', $user->id)->first();
            $roles = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_type', 'Modules\\Identity\\Models\\User')
                ->where('model_has_roles.model_id', $user->id)
                ->pluck('roles.name')
                ->toArray();

            $userData = [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'avatar'    => $profile?->avatar ?? null,
                'bio'       => $profile?->bio ?? null,
                'expertise' => $profile?->expertise ?? null,
                'roles'     => $roles,
            ];
        }

        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'sort_order' => $this->sort_order,
            'is_active'  => $this->is_active,
            'user'       => $userData,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}