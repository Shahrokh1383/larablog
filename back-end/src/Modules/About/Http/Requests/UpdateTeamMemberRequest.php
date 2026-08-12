<?php

namespace Modules\About\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'    => 'sometimes|required|exists:users,id',
            'sort_order' => 'integer|min:0',
            'is_active'  => 'boolean',
        ];
    }
}