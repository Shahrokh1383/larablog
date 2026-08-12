<?php

namespace Modules\Search\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q'    => ['required', 'string', 'min:2', 'max:150'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}