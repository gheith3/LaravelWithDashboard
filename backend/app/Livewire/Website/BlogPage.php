<?php

namespace App\Livewire\Website;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Blog')]
class BlogPage extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $tag = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTag(): void
    {
        $this->resetPage();
    }

    public function filterByTag(string $slug): void
    {
        $this->tag = $this->tag === $slug ? '' : $slug;
        $this->resetPage();
    }

    public function render()
    {
        $query = Post::query()
            ->where('status', PostStatus::Published)
            ->where('is_active', true)
            ->with(['creator', 'tags'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('content', 'like', "%{$this->search}%");
            });
        }

        if ($this->tag) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $this->tag)->where('is_active', true));
        }

        $posts = $query->paginate(9);

        $activeTag = $this->tag
            ? Tag::where('slug', $this->tag)->where('is_active', true)->first()
            : null;

        $tags = Tag::query()
            ->where('is_active', true)
            ->whereHas('posts', fn ($q) => $q->where('status', PostStatus::Published)->where('is_active', true))
            ->withCount(['posts' => fn ($q) => $q->where('status', PostStatus::Published)->where('is_active', true)])
            ->orderByDesc('posts_count')
            ->take(20)
            ->get();

        return view('livewire.website.blog-page', compact('posts', 'tags', 'activeTag'));
    }
}
