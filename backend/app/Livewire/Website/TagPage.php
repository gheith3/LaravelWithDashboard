<?php

namespace App\Livewire\Website;

use App\Enums\PostStatus;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TagPage extends Component
{
    use WithPagination;

    public Tag $tag;

    public function mount(Tag $tag): void
    {
        abort_unless($tag->is_active, 404);

        $this->tag = $tag;
    }

    public function render()
    {
        $posts = $this->tag->posts()
            ->where('status', PostStatus::Published)
            ->where('is_active', true)
            ->with(['creator', 'tags'])
            ->latest()
            ->paginate(9);

        return view('livewire.website.tag-page', compact('posts'))
            ->layout('layouts.app', ['title' => $this->tag->name]);
    }
}
