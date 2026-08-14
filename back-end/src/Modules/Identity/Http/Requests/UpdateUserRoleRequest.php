<?php

namespace Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized by policy
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(config('permissions.roles'))],
        ];
    }
}