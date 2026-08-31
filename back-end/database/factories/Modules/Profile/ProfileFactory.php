<?php

namespace Database\Factories\Modules\Profile;

use Modules\Profile\Models\Profile;
use Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id'             => User::factory(),
            'avatar'              => null,
            'bio'                 => $this->faker->optional()->sentence(),
            'expertise'           => $this->faker->optional()->words(3, true),
            'years_of_experience' => $this->faker->optional()->numberBetween(0, 50),
            'social_links'        => $this->faker->optional()->randomElements([
                'https://twitter.com/example',
                'https://linkedin.com/in/example',
                'https://github.com/example',
            ], 2),
        ];
    }
}