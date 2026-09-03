<?php

namespace Modules\About\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListTeamMembersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function perPage(int $default = 10): int
    {
        // Query parameters arrive as strings; validated() preserves them.
        return (int) $this->validated('per_page', $default);
    }
}