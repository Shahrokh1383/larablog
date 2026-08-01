<?php

namespace Modules\Content\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by policy in controller
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'body'           => ['required', 'string'],
            'excerpt'        => ['nullable', 'string'],
            'featured_image' => ['nullable', 'url'],
            'is_published'   => ['boolean'],
            'category_id'    => ['nullable', 'exists:content_categories,id'],
            'tag_ids'        => ['nullable', 'array'],
            'tag_ids.*'      => ['exists:content_tags,id'],
        ];
    }
}