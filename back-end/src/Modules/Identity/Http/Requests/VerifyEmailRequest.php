<?php

namespace Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'   => ['required', 'string', 'uuid'],
            'hash' => ['required', 'string'],
        ];
    }
}