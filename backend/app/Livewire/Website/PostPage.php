<?php

namespace App\Livewire\Website;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class PostPage extends Component
{
    public Post $post;

    #[Validate('required|string|min:2|max:100')]
    public string $name = '';

    #[Validate('required|email|max:150')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[Validate('required|string|min:10|max:2000')]
    public string $comment = '';

    public bool $submitted = false;

    public function mount(Post $post): void
    {
        abort_unless($post->status === PostStatus::Published && $post->is_active, 404);

        $this->post = $post;
    }

    public function submitComment(): void
    {
        $this->validate();

        Comment::create([
            'post_id' => $this->post->id,
            'name'    => $this->name,
            'email'   => $this->email,
            'phone'   => $this->phone,
            'content' => $this->comment,
            'status'  => CommentStatus::Review,
        ]);

        $this->reset(['name', 'email', 'phone', 'comment']);
        $this->submitted = true;
    }

    public function render()
    {
        $this->post->loadMissing(['tags', 'creator']);

        $comments = $this->post->comments()
            ->where('status', CommentStatus::Accepted)
            ->latest()
            ->get();

        return view('livewire.website.post-page', compact('comments'))
            ->layout('layouts.app', ['title' => $this->post->title]);
    }
}
