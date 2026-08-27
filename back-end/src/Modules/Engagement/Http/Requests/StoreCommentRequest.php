<?php

namespace Modules\Engagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'post_id'   => ['required', 'uuid', 'exists:content_posts,id'],
            'parent_id' => [
                'nullable',
                'uuid',
                Rule::exists('engagement_comments', 'id')->where(function ($query) {
                    $query->where('post_id', $this->input('post_id'))
                        ->where('is_approved', true);
                }),
            ],
            'body'      => ['required', 'string', 'max:2000'],

            'name'      => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn() => !$this->user()),
            ],
            'email'     => [
                'nullable',
                'email',
                'max:255',
                Rule::requiredIf(fn() => !$this->user()),
            ],
        ];
    }
}