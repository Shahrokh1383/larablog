<?php

namespace Database\Factories\Modules\Engagement;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Engagement\Models\Comment;
use Modules\Articles\Models\Post;
use Shared\Models\User;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'post_id'     => Post::factory(),
            'user_id'     => null,
            'parent_id'   => null,
            'name'        => null,
            'email'       => null,
            'body'        => $this->faker->paragraph(),
            'is_approved' => false,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }

    public function byUser(?User $user = null): static
    {
        return $this->state(function (array $attributes) use ($user) {
            if ($user === null) {
                $user = User::factory()->create();
            }
            return [
                'user_id' => $user->id,
                'name' => null,
                'email' => null,
            ];
        });
    }

    public function asGuest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'name'    => $this->faker->name(),
            'email'   => $this->faker->safeEmail(),
        ]);
    }

    public function replyTo(Comment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'post_id'   => $parent->post_id,
        ]);
    }
}