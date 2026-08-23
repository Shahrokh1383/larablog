<?php

namespace Modules\Articles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Articles\Models\Post;

class DeleteImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('deleteImage', Post::class);
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'string'],
        ];
    }
}