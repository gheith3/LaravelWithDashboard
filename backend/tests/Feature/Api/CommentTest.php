<?php

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

describe('GET /api/v1/posts/{slug}/comments', function () {
    it('returns only accepted comments for a published post', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->published()->create();

        Comment::factory()->count(2)->for($post)->accepted()->create();
        Comment::factory()->for($post)->underReview()->create();

        $this->getJson("/api/v1/posts/{$post->slug}/comments")
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Comments retrieved'])
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'name', 'content', 'created_at']],
            ]);
    });

    it('returns an empty list when there are no accepted comments', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->published()->create();

        Comment::factory()->count(3)->for($post)->underReview()->create();

        $this->getJson("/api/v1/posts/{$post->slug}/comments")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('returns 404 for a post that does not exist', function () {
        $this->getJson('/api/v1/posts/no-such-post/comments')
            ->assertStatus(404);
    });

    it('returns 404 for a draft post', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->draft()->create();

        $this->getJson("/api/v1/posts/{$post->slug}/comments")
            ->assertStatus(404);
    });
});

describe('POST /api/v1/posts/{slug}/comments', function () {
    it('submits a comment and places it under review', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->published()->create();

        $this->postJson("/api/v1/posts/{$post->slug}/comments", [
            'name'    => 'Guest User',
            'email'   => 'guest@example.com',
            'content' => 'This is a well-written and informative post.',
        ])
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Comment submitted and awaiting moderation',
            ])
            ->assertJsonStructure(['data' => ['id', 'name', 'content', 'created_at']]);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'email'   => 'guest@example.com',
            'status'  => CommentStatus::Review->value,
        ]);
    });

    it('stores an optional phone number', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->published()->create();

        $this->postJson("/api/v1/posts/{$post->slug}/comments", [
            'name'    => 'Guest User',
            'email'   => 'guest@example.com',
            'phone'   => '+1234567890',
            'content' => 'Great post, very informative and helpful.',
        ])->assertStatus(201);

        $this->assertDatabaseHas('comments', ['phone' => '+1234567890']);
    });

    it('returns 422 when required fields are missing', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->published()->create();

        $this->postJson("/api/v1/posts/{$post->slug}/comments", [])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    });

    it('returns 422 when content is too short', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->published()->create();

        $this->postJson("/api/v1/posts/{$post->slug}/comments", [
            'name'    => 'Guest',
            'email'   => 'guest@example.com',
            'content' => 'Too short',
        ])->assertStatus(422);
    });

    it('returns 404 for a post that does not exist', function () {
        $this->postJson('/api/v1/posts/no-such-post/comments', [
            'name'    => 'Guest',
            'email'   => 'guest@example.com',
            'content' => 'This is a well-written and informative post.',
        ])->assertStatus(404);
    });

    it('returns 404 for a draft post', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->draft()->create();

        $this->postJson("/api/v1/posts/{$post->slug}/comments", [
            'name'    => 'Guest',
            'email'   => 'guest@example.com',
            'content' => 'This is a well-written and informative post.',
        ])->assertStatus(404);
    });
});
