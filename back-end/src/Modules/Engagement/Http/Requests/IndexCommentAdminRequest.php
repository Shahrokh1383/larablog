<?php

namespace Modules\Engagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexCommentAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}