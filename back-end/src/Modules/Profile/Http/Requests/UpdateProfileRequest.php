<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'avatar' => [
                'nullable', 
                'url', 
                'max:255', 
                'regex:/^(?!.*\.\.).+$/',
                function (string $attribute, mixed $value, \Closure $fail) use ($userId): void {
                    if (!$value || !$userId) {
                        return;
                    }
                    
                    // Parse URL to avoid APP_URL environment mismatches (http/https/ports)
                    $path = parse_url($value, PHP_URL_PATH);
                    $expectedPrefix = "/storage/profiles/avatars/{$userId}/";
                    
                    if (!$path || !str_starts_with($path, $expectedPrefix)) {
                        $fail('The selected avatar does not belong to your account.');
                    }
                }
            ],
            'bio' => ['nullable', 'string', 'max:1000'],
            'expertise' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:100'],
            'social_links' => ['nullable', 'array'],
            'social_links.*' => ['nullable', 'url', 'max:255', 'regex:/^https?:\/\//i'],
        ];
    }
}