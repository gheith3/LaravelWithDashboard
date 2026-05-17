<div class="min-h-screen flex flex-col">
    <livewire:website.components.navbar />

    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            {{-- Header --}}
            <div class="mb-10">
                <h1 class="text-3xl font-bold text-gray-900">
                    @if ($activeTag)
                        Posts tagged "{{ $activeTag->name }}"
                    @else
                        Blog
                    @endif
                </h1>
                @if ($activeTag)
                    <button wire:click="filterByTag('{{ $activeTag->slug }}')"
                            class="mt-2 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Clear filter
                    </button>
                @endif
            </div>

            <div class="flex flex-col lg:flex-row gap-12">

                {{-- Posts --}}
                <div class="flex-1 min-w-0">
                    {{-- Search --}}
                    <div class="relative mb-8">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search"
                               wire:model.live.debounce.400ms="search"
                               placeholder="Search posts…"
                               class="w-full pl-10 pr-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition">
                    </div>

                    {{-- Grid --}}
                    @if ($posts->isEmpty())
                        <div class="text-center py-20 text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="font-medium">No posts found.</p>
                            @if ($search)
                                <p class="mt-1 text-sm">Try a different search term.</p>
                            @endif
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                            @foreach ($posts as $post)
                                <x-website.post-card :post="$post" />
                            @endforeach
                        </div>

                        <div class="mt-10">
                            {{ $posts->links() }}
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-56 shrink-0">
                    <div class="sticky top-24">
                        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Topics</h2>
                        @if ($tags->isEmpty())
                            <p class="text-sm text-gray-400">No topics yet.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach ($tags as $tagItem)
                                    <button wire:click="filterByTag('{{ $tagItem->slug }}')"
                                            class="inline-flex items-center gap-1 text-sm px-3 py-1.5 rounded-full border transition-colors
                                                {{ $tag === $tagItem->slug
                                                    ? 'bg-gray-900 text-white border-gray-900'
                                                    : 'bg-white text-gray-600 border-gray-300 hover:border-gray-700 hover:text-gray-900' }}">
                                        {{ $tagItem->name }}
                                        <span class="text-xs opacity-60">{{ $tagItem->posts_count }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <livewire:website.components.footer />
</div>
