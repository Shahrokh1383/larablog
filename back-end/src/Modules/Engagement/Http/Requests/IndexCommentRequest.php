<?php

namespace Modules\Engagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}