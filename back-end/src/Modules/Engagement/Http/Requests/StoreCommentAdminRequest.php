<?php

namespace Modules\Engagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => [
                'nullable',
                'uuid',
                Rule::exists('engagement_comments', 'id')->where(
                    fn ($query) => $query->where('post_id', $this->route('post'))
                ),
            ],
        ];
    }
}