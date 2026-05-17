<div class="min-h-screen flex flex-col">
    <livewire:website.components.navbar />

    <main class="flex-1">
        {{-- Hero --}}
        <section class="bg-gray-50 border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 tracking-tight">
                    Welcome to {{ config('app.name') }}
                </h1>
                <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto leading-relaxed">
                    Discover our latest articles, stories, and insights.
                </p>
                <a href="{{ route('blog') }}"
                   class="mt-8 inline-flex items-center gap-2 bg-gray-900 text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-gray-700 transition-colors">
                    Browse all posts
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </section>

        {{-- Latest posts --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-2xl font-bold text-gray-900">Latest Posts</h2>
                <a href="{{ route('blog') }}"
                   class="text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
                    View all →
                </a>
            </div>

            @if ($posts->isEmpty())
                <div class="text-center py-20 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-lg">No posts published yet.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($posts as $post)
                        <x-website.post-card :post="$post" />
                    @endforeach
                </div>
            @endif
        </section>
    </main>

    <livewire:website.components.footer />
</div>
