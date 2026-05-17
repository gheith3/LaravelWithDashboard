<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6, false);

        return [
            'title'         => rtrim($title, '.'),
            'slug'          => Str::slug($title),
            'content'       => fake()->paragraphs(4, true),
            'status'        => fake()->randomElement(PostStatus::cases()),
            'is_active'     => true,
            'created_by_id' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => PostStatus::Published]);
    }

    public function draft(): static
    {
        return $this->state(['status' => PostStatus::Draft]);
    }

    public function archived(): static
    {
        return $this->state(['status' => PostStatus::Archived]);
    }
}
