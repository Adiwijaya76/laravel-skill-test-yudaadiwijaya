<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'title'        => $this->faker->unique()->sentence(4),
            'content'      => $this->faker->paragraphs(3, true),
            'is_draft'     => false,
            'published_at' => now()->subMinutes(rand(1, 1440)), // default Active
        ];
    }

    public function draft(): self
    {
        return $this->state(fn () => [
            'is_draft'     => true,
            'published_at' => null,
        ]);
    }

    public function scheduled(): self
    {
        return $this->state(fn () => [
            'is_draft'     => false,
            'published_at' => now()->addDay(),
        ]);
    }
}
