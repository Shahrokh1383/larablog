<?php

namespace Database\Factories\Modules\About;

use Modules\About\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteSettingFactory extends Factory
{
    protected $model = SiteSetting::class;

    public function definition(): array
    {
        return [
            'call_us_phone' => $this->faker->phoneNumber(),
            'call_us_emails' => [$this->faker->safeEmail()],
            'visit_address' => $this->faker->address(),
            'social_links' => [
                'linkedin' => 'https://linkedin.com/in/' . $this->faker->userName(),
                'github' => 'https://github.com/' . $this->faker->userName(),
            ],
            'story_image' => null,
        ];
    }
}