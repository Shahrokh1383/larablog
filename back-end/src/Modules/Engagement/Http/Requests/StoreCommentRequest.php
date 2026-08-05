<?php

namespace Modules\Engagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isAuthenticated = $this->user() !== null;

        $rules = [
            'post_id'   => ['required', 'uuid', 'exists:content_posts,id'],
            'parent_id' => ['nullable', 'uuid', 'exists:engagement_comments,id'],
            'body'      => ['required', 'string', 'max:2000'],
        ];

        if ($isAuthenticated) {
            $rules['name']  = ['prohibited'];
            $rules['email'] = ['prohibited'];
        } else {
            $rules['name']  = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255'];
        }

        return $rules;
    }
}