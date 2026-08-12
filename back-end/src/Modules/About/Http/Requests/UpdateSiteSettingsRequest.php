<?php

namespace Modules\About\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization via Policy in controller
    }

    public function rules(): array
    {
        return [
            'call_us_phone' => 'nullable|string|max:50',
            'call_us_emails' => 'nullable|array',
            'call_us_emails.*' => 'email',
            'visit_address' => 'nullable|string|max:500',
            'social_links' => 'nullable|array',
            'social_links.linkedin' => 'nullable|url',
            'social_links.github' => 'nullable|url',
            'social_links.twitter' => 'nullable|url',
            'social_links.instagram' => 'nullable|url',
            'social_links.dribbble' => 'nullable|url',
            'social_links.youtube' => 'nullable|url',
        ];
    }
}