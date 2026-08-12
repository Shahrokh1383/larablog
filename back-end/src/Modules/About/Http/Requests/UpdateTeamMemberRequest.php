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
            'user_id' => 'sometimes|required|exists:users,id',
            'display_name' => 'nullable|string|max:255',
            'position' => 'sometimes|required|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ];
    }
}