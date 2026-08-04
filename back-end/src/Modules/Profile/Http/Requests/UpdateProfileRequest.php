<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by middleware
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'avatar'                => ['nullable', 'string', 'max:255'],
            'bio'                   => ['nullable', 'string', 'max:1000'],
            'expertise'             => ['nullable', 'string', 'max:255'],
            'years_of_experience'   => ['nullable', 'integer', 'min:0', 'max:80'],
            'social_links'          => ['nullable', 'array'],
            'social_links.twitter'  => ['nullable', 'string', 'url', 'max:255'],
            'social_links.github'   => ['nullable', 'string', 'url', 'max:255'],
            'social_links.linkedin' => ['nullable', 'string', 'url', 'max:255'],
            'social_links.instagram' => ['nullable', 'string', 'url', 'max:255'],
            'social_links.dribbble' => ['nullable', 'string', 'url', 'max:255'],
        ];
    }
}