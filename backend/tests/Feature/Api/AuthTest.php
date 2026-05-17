<?php

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Str;

describe('POST /api/v1/auth/register', function () {
    it('creates a user and returns a token pair', function () {
        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Registration successful'])
            ->assertJsonStructure([
                'data' => ['access_token', 'refresh_token', 'token_type', 'expires_in', 'user'],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertDatabaseCount('refresh_tokens', 1);
    });

    it('fails validation when required fields are missing', function () {
        $this->postJson('/api/v1/auth/register', [])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    });

    it('fails when email is already taken', function () {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Jane',
            'email'                 => 'taken@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])->assertStatus(422);
    });

    it('fails when passwords do not match', function () {
        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Jane',
            'email'                 => 'jane@example.com',
            'password'              => 'password',
            'password_confirmation' => 'different',
        ])->assertStatus(422);
    });
});

describe('POST /api/v1/auth/login', function () {
    it('returns a token pair on valid credentials', function () {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Login successful'])
            ->assertJsonStructure([
                'data' => ['access_token', 'refresh_token', 'token_type', 'expires_in', 'user'],
            ]);

        $this->assertDatabaseCount('refresh_tokens', 1);
    });

    it('updates last_login_at after successful login', function () {
        $user = User::factory()->create(['last_login_at' => null]);

        $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        expect($user->fresh()->last_login_at)->not->toBeNull();
    });

    it('returns 401 for wrong password', function () {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(401)->assertJson(['success' => false]);
    });

    it('returns 401 for inactive users', function () {
        $user = User::factory()->create(['is_active' => false]);

        $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertStatus(401);
    });

    it('returns 422 when email is not provided', function () {
        $this->postJson('/api/v1/auth/login', ['password' => 'password'])
            ->assertStatus(422);
    });
});

describe('POST /api/v1/auth/refresh', function () {
    it('returns a new token pair and rotates the refresh token', function () {
        $user = User::factory()->create();

        $loginRes        = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);
        $oldRefreshToken = $loginRes->json('data.refresh_token');

        $refreshRes = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $oldRefreshToken,
        ]);

        $refreshRes->assertOk()
            ->assertJson(['success' => true, 'message' => 'Token refreshed'])
            ->assertJsonStructure(['data' => ['access_token', 'refresh_token']]);

        $newRefreshToken = $refreshRes->json('data.refresh_token');
        expect($newRefreshToken)->not->toBe($oldRefreshToken);

        // Old token must be invalidated after rotation
        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => $oldRefreshToken])
            ->assertStatus(401);
    });

    it('returns 401 for a token not in the database', function () {
        $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => Str::random(64),
        ])->assertStatus(401)->assertJson(['success' => false]);
    });

    it('returns 401 for an expired refresh token', function () {
        $user  = User::factory()->create();
        $plain = Str::random(64);

        RefreshToken::create([
            'user_id'    => $user->id,
            'token'      => hash('sha256', $plain),
            'expires_at' => now()->subDay(),
        ]);

        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => $plain])
            ->assertStatus(401);
    });

    it('returns 422 when refresh_token is shorter than 64 characters', function () {
        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => 'too-short'])
            ->assertStatus(422);
    });

    it('returns 422 when refresh_token is missing', function () {
        $this->postJson('/api/v1/auth/refresh', [])
            ->assertStatus(422);
    });
});

describe('POST /api/v1/auth/logout', function () {
    it('logs out and removes the refresh token from the database', function () {
        [$user, $token] = userWithToken();
        $plain          = Str::random(64);

        RefreshToken::create([
            'user_id'    => $user->id,
            'token'      => hash('sha256', $plain),
            'expires_at' => now()->addDays(30),
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout', ['refresh_token' => $plain])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Logged out successfully']);

        $this->assertDatabaseCount('refresh_tokens', 0);
    });

    it('returns 401 without a valid JWT', function () {
        $this->postJson('/api/v1/auth/logout')
            ->assertStatus(401);
    });
});

describe('GET /api/v1/auth/me', function () {
    it('returns the authenticated user profile', function () {
        [$user, $token] = userWithToken();

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'User profile retrieved'])
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    });

    it('returns 401 without authentication', function () {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    });
});
