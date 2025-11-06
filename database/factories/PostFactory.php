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
            // pastikan SELALU ada user agar FK tidak null
            'user_id'      => User::factory(),
            'title'        => $this->faker->unique()->sentence(4),
            'content'      => $this->faker->paragraphs(3, true),
            'is_draft'     => false,
            'published_at' => now()->subMinutes(rand(1, 2880)), // <= now() → Active
        ];
    }

    /** Draft: is_draft = true, published_at boleh null */
    public function draft(): self
    {
        return $this->state(fn () => [
            'is_draft'     => true,
            'published_at' => null,
        ]);
    }

    /** Scheduled: is_draft = false, published_at > now() */
    public function scheduled(): self
    {
        return $this->state(fn () => [
            'is_draft'     => false,
            'published_at' => now()->addDay(),
        ]);
    }
}
