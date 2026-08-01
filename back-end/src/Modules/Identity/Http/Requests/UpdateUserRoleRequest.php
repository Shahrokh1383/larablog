<?php

namespace Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized by route middleware and policy
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'in:admin,editor,author,user'],
        ];
    }
}