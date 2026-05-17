<?php

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

describe('GET /api/v1/posts', function () {
    it('returns paginated published active posts', function () {
        $user = User::factory()->create();
        Post::factory()->count(3)->for($user, 'creator')->published()->create();
        Post::factory()->for($user, 'creator')->draft()->create();
        Post::factory()->for($user, 'creator')->archived()->create();

        $this->getJson('/api/v1/posts')
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Posts retrieved'])
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'title', 'slug', 'status', 'creator', 'tags']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to'],
            ]);
    });

    it('excludes inactive posts', function () {
        $user = User::factory()->create();
        Post::factory()->for($user, 'creator')->published()->create(['is_active' => false]);

        $this->getJson('/api/v1/posts')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('filters posts by search term in title', function () {
        $user = User::factory()->create();
        Post::factory()->for($user, 'creator')->published()->create(['title' => 'Laravel Testing Guide']);
        Post::factory()->for($user, 'creator')->published()->create(['title' => 'Vue JS Introduction']);

        $this->getJson('/api/v1/posts?search=Laravel')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Laravel Testing Guide');
    });

    it('filters posts by tag slug', function () {
        $user  = User::factory()->create();
        $tag   = Tag::factory()->create(['slug' => 'php']);
        $post  = Post::factory()->for($user, 'creator')->published()->create();
        Post::factory()->for($user, 'creator')->published()->create();

        $post->tags()->attach($tag);

        $this->getJson('/api/v1/posts?tag=php')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post->id);
    });

    it('respects per_page parameter', function () {
        $user = User::factory()->create();
        Post::factory()->count(5)->for($user, 'creator')->published()->create();

        $this->getJson('/api/v1/posts?per_page=2')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonCount(2, 'data');
    });

    it('caps per_page at 50', function () {
        $user = User::factory()->create();
        Post::factory()->count(3)->for($user, 'creator')->published()->create();

        $this->getJson('/api/v1/posts?per_page=100')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 50);
    });
});

describe('GET /api/v1/posts/{slug}', function () {
    it('returns a single published post', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->published()->create();

        $this->getJson("/api/v1/posts/{$post->slug}")
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Post retrieved'])
            ->assertJsonPath('data.slug', $post->slug)
            ->assertJsonStructure(['data' => ['id', 'title', 'slug', 'content', 'creator', 'tags']]);
    });

    it('returns 404 for a draft post', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->draft()->create();

        $this->getJson("/api/v1/posts/{$post->slug}")->assertStatus(404);
    });

    it('returns 404 for an archived post', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->archived()->create();

        $this->getJson("/api/v1/posts/{$post->slug}")->assertStatus(404);
    });

    it('returns 404 for an unknown slug', function () {
        $this->getJson('/api/v1/posts/does-not-exist')->assertStatus(404);
    });
});

describe('POST /api/v1/posts', function () {
    it('creates a post for the authenticated user', function () {
        [$user, $token] = userWithToken();

        $this->withToken($token)
            ->postJson('/api/v1/posts', [
                'title'   => 'My First Post',
                'content' => 'Some content here for my post.',
                'status'  => PostStatus::Published->value,
            ])
            ->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Post created'])
            ->assertJsonPath('data.title', 'My First Post')
            ->assertJsonPath('data.slug', 'my-first-post')
            ->assertJsonPath('data.status', PostStatus::Published->value);

        $this->assertDatabaseHas('posts', [
            'title'         => 'My First Post',
            'created_by_id' => $user->id,
        ]);
    });

    it('auto-generates a unique slug when the base slug is taken', function () {
        $owner = User::factory()->create();
        Post::factory()->for($owner, 'creator')->create(['slug' => 'unique-title']);

        [, $token] = userWithToken();

        $this->withToken($token)
            ->postJson('/api/v1/posts', [
                'title'   => 'Unique Title',
                'content' => 'Some content here.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.slug', 'unique-title-1');
    });

    it('defaults status to draft when not provided', function () {
        [, $token] = userWithToken();

        $this->withToken($token)
            ->postJson('/api/v1/posts', [
                'title'   => 'Draft Post',
                'content' => 'Content goes here.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.status', PostStatus::Draft->value);
    });

    it('returns 401 without authentication', function () {
        $this->postJson('/api/v1/posts', [
            'title'   => 'Unauthenticated',
            'content' => 'Content.',
        ])->assertStatus(401);
    });

    it('returns 422 when required fields are missing', function () {
        [, $token] = userWithToken();

        $this->withToken($token)
            ->postJson('/api/v1/posts', [])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    });
});

describe('PUT /api/v1/posts/{slug}', function () {
    it('updates the authenticated user\'s own post', function () {
        [$user, $token] = userWithToken();
        $post           = Post::factory()->for($user, 'creator')->published()->create();

        $this->withToken($token)
            ->putJson("/api/v1/posts/{$post->slug}", ['title' => 'Updated Title'])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Post updated'])
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Updated Title']);
    });

    it('returns 404 when trying to update another user\'s post', function () {
        $owner = User::factory()->create();
        $post  = Post::factory()->for($owner, 'creator')->published()->create();

        [, $token] = userWithToken();

        $this->withToken($token)
            ->putJson("/api/v1/posts/{$post->slug}", ['title' => 'Hijacked'])
            ->assertStatus(404);
    });

    it('returns 401 without authentication', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->published()->create();

        $this->putJson("/api/v1/posts/{$post->slug}", ['title' => 'No Auth'])
            ->assertStatus(401);
    });
});

describe('DELETE /api/v1/posts/{slug}', function () {
    it('soft-deletes the authenticated user\'s own post', function () {
        [$user, $token] = userWithToken();
        $post           = Post::factory()->for($user, 'creator')->published()->create();

        $this->withToken($token)
            ->deleteJson("/api/v1/posts/{$post->slug}")
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Post deleted']);

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    });

    it('returns 404 when trying to delete another user\'s post', function () {
        $owner = User::factory()->create();
        $post  = Post::factory()->for($owner, 'creator')->published()->create();

        [, $token] = userWithToken();

        $this->withToken($token)
            ->deleteJson("/api/v1/posts/{$post->slug}")
            ->assertStatus(404);
    });

    it('returns 401 without authentication', function () {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'creator')->published()->create();

        $this->deleteJson("/api/v1/posts/{$post->slug}")
            ->assertStatus(401);
    });
});
