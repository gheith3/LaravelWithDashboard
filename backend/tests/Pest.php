<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/**
 * Create a user and return their JWT access token alongside the model.
 *
 * @return array{0: User, 1: string}
 */
function userWithToken(array $attributes = []): array
{
    $user  = User::factory()->create($attributes);
    $token = auth('api')->login($user);

    return [$user, $token];
}
