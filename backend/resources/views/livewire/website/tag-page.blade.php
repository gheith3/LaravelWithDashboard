<div class="min-h-screen flex flex-col">
    <livewire:website.components.navbar />

    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
                <a href="{{ route('blog') }}" class="hover:text-gray-700 transition-colors">Blog</a>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-600">{{ $tag->name }}</span>
            </nav>

            {{-- Header --}}
            <div class="mb-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-sm font-medium mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Topic
                </div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $tag->name }}</h1>
                <p class="mt-2 text-gray-500 text-sm">
                    {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}
                </p>
            </div>

            @if ($posts->isEmpty())
                <div class="text-center py-20 text-gray-400">
                    <p class="text-lg font-medium">No published posts in this topic yet.</p>
                    <a href="{{ route('blog') }}"
                       class="mt-4 inline-block text-sm text-gray-600 underline underline-offset-4 hover:text-gray-900 transition-colors">
                        Browse all posts
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($posts as $post)
                        <x-website.post-card :post="$post" />
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </main>

    <livewire:website.components.footer />
</div>
