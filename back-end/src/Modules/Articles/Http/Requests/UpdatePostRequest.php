<?php

namespace Modules\Articles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['sometimes', 'string', 'max:255'],
            'body'           => ['sometimes', 'string'],
            'excerpt'        => ['nullable', 'string'],
            'featured_image' => ['nullable', 'url'],
            'is_published'   => ['boolean'],
            'is_editors_pick'=> ['boolean'],
            'category_id'    => ['nullable', 'exists:content_categories,id'],
            'tag_ids'        => ['nullable', 'array'],
            'tag_ids.*'      => ['exists:content_tags,id'],
        ];
    }
}