<?php

namespace Modules\About\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteStoryImageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'url'],
        ];
    }

    public function imageUrl(): string
    {
        return $this->validated('url');
    }
}