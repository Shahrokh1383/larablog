<?php

namespace Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OAuthCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'  => ['required', 'string'],
            'state' => ['sometimes', 'nullable', 'string'],
        ];
    }
}