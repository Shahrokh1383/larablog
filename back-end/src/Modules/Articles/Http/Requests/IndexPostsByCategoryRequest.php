<?php
namespace Modules\Taxonomy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowCategoryPostsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sort'     => ['nullable', 'string', 'in:newest,oldest,most_popular'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}