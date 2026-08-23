<?php

namespace Modules\Articles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'body'            => ['required', 'string'],
            'excerpt'         => ['nullable', 'string'],
            'featured_image'  => ['nullable', 'url'],
            'is_published'    => ['sometimes', 'boolean'],
            'is_editors_pick' => ['sometimes', 'boolean'],
            'category_id'     => ['nullable', 'string'],
            'tag_ids'         => ['nullable', 'array'],
            'tag_ids.*'       => ['string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $categoryId = $this->input('category_id');

            if ($categoryId !== null) {
                $exists = app(CategoryPublicServiceInterface::class)
                    ->categoryIdsExist([$categoryId]);

                if (! $exists) {
                    $validator->errors()->add('category_id', 'The selected category is invalid.');
                }
            }

            $tagIds = $this->input('tag_ids', []);

            if (! empty($tagIds)) {
                $exists = app(TagPublicServiceInterface::class)
                    ->tagIdsExist($tagIds);

                if (! $exists) {
                    $validator->errors()->add('tag_ids', 'One or more selected tags are invalid.');
                }
            }
        });
    }
}