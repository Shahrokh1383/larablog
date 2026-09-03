<?php

namespace Modules\About\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamMemberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'sometimes',
                'required',
                'exists:users,id',
                Rule::unique('about_team_members', 'user_id')
                    ->ignore($this->route('team_member')),
            ],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active'  => ['sometimes', 'boolean'],
        ];
    }
}