<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $tags = Tag::factory(6)->create();
        $user = User::query()->first();

        $post1 = Post::factory()->published()->create([
            'created_by_id' => $user->id,
            'title'   => 'Getting Started with Laravel',
            'slug'    => 'getting-started-with-laravel',
            'content' => 'Laravel is a web application framework with expressive, elegant syntax. It provides a great starting point for building modern applications.',
        ]);
        $post1->tags()->attach($tags->random(3));
        $post1->comments()->saveMany([
            Comment::factory()->accepted()->make(['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'content' => 'Great introduction! Really helped me understand the basics.']),
            Comment::factory()->accepted()->make(['name' => 'Bob Smith',    'email' => 'bob@example.com',   'content' => 'Very well written. Looking forward to more posts like this.']),
            Comment::factory()->underReview()->make(['name' => 'Carol White', 'email' => 'carol@example.com', 'content' => 'Can you cover service providers in more detail?']),
        ]);

        $post2 = Post::factory()->draft()->create([
            'created_by_id' => $user->id,
            'title'   => 'Understanding Eloquent Relationships',
            'slug'    => 'understanding-eloquent-relationships',
            'content' => 'Eloquent ORM provides beautiful, simple ActiveRecord implementations for working with your database. Each relationship type is explored in depth.',
        ]);
        $post2->tags()->attach($tags->random(2));
        $post2->comments()->saveMany([
            Comment::factory()->accepted()->make(['name' => 'David Lee',  'email' => 'david@example.com', 'content' => 'The many-to-many section was exactly what I needed.']),
            Comment::factory()->underReview()->make(['name' => 'Eva Brown', 'email' => 'eva@example.com',   'content' => 'Would love an example with polymorphic relations.']),
        ]);

        $post3 = Post::factory()->archived()->create([
            'created_by_id' => $user->id,
            'title'   => 'Building REST APIs with Laravel',
            'slug'    => 'building-rest-apis-with-laravel',
            'content' => 'Laravel makes it simple to build robust REST APIs. Learn how to structure your routes, controllers, and resources for clean API development.',
        ]);
        $post3->tags()->attach($tags->random(4));
        $post3->comments()->saveMany([
            Comment::factory()->accepted()->make(['name' => 'Frank Davis',  'email' => 'frank@example.com',  'content' => 'Sanctum vs Passport comparison would be a great follow-up.']),
            Comment::factory()->accepted()->make(['name' => 'Grace Wilson', 'email' => 'grace@example.com',  'content' => 'Solid overview. The resource transformers section was very clear.']),
            Comment::factory()->underReview()->make(['name' => 'Henry Moore', 'email' => 'henry@example.com', 'content' => 'How do you handle API versioning in this setup?']),
            Comment::factory()->underReview()->make(['name' => 'Isla Taylor', 'email' => 'isla@example.com',  'content' => 'Please add rate limiting examples.']),
        ]);
    }
}
