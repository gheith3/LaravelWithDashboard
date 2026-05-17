<?php

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'    => fake()->name(),
            'email'   => fake()->safeEmail(),
            'phone'   => fake()->optional(0.6)->phoneNumber(),
            'content' => fake()->paragraph(3),
            'status'  => fake()->randomElement(CommentStatus::cases()),
        ];
    }

    public function accepted(): static
    {
        return $this->state(['status' => CommentStatus::Accepted]);
    }

    public function underReview(): static
    {
        return $this->state(['status' => CommentStatus::Review]);
    }
}
