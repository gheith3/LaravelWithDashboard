<?php

namespace App\Livewire\Website;

use App\Enums\PostStatus;
use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Home')]
class HomePage extends Component
{
    public function render()
    {
        $posts = Post::query()
            ->where('status', PostStatus::Published)
            ->where('is_active', true)
            ->with(['creator', 'tags'])
            ->latest()
            ->take(6)
            ->get();

        return view('livewire.website.home-page', compact('posts'));
    }
}
