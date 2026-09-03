<?php

namespace Modules\About\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListEligibleUsersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function search(): ?string
    {
        return $this->validated('search');
    }

    public function perPage(int $default = 100): int
    {
        // Query parameters arrive as strings; validated() preserves them.
        return (int) $this->validated('per_page', $default);
    }
}