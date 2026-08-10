<?php

namespace Modules\Marketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'name'    => ['nullable', 'string', 'max:255', Rule::requiredIf(fn() => !$this->user())],
            'email'   => ['nullable', 'email', 'max:255', Rule::requiredIf(fn() => !$this->user())],
        ];
    }
}