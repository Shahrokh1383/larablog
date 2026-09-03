<?php

namespace Modules\Marketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendNewsletterRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasRole('admin'); }

    public function rules(): array
    {
        return [
            'send_to_all' => ['required', 'boolean'],
            'subscriber_ids' => [
                Rule::requiredIf($this->boolean('send_to_all') === false),
                'nullable',
                'array',
            ],
            'subscriber_ids.*' => ['uuid', 'exists:marketing_subscribers,id'],
        ];
    }
}