<?php

namespace Modules\Engagement\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class IndexCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cursor' => ['nullable', 'string', 'max:255', $this->validCursor()],
            'skip'   => ['nullable', 'integer', 'min:0', 'max:1000'],
            'take'   => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * Cursors we hand out are base64-encoded JSON. Reject anything else with a
     * 422 instead of delegating malformed input to paginator internals.
     */
    private function validCursor(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (!is_string($value) || !is_object(json_decode((string) base64_decode($value, true)))) {
                $fail('The :attribute field is malformed.');
            }
        };
    }
}