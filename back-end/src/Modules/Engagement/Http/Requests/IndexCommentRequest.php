<?php

namespace Modules\Engagement\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Pagination\Cursor;
use Modules\Engagement\Services\CommentPublicService;

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

    private function validCursor(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value)) {
                $fail('The :attribute field must be a string.');
                return;
            }

            $cursor = Cursor::fromEncoded($value);

            if ($cursor === null) {
                $fail('The :attribute field is not a valid cursor.');
                return;
            }
            foreach (CommentPublicService::ORDER_COLUMNS as $column) {
                $cursorValue = $cursor->parameter($column);

                if ($cursorValue === null || ! is_scalar($cursorValue)) {
                    $fail('The :attribute field is not a valid cursor.');
                    return;
                }
            }
        };
    }
}