@props(['post'])

<article class="group flex flex-col bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">

    {{-- Image --}}
    @if ($post->header_image)
        <div class="aspect-video overflow-hidden bg-gray-100">
            <img src="{{ Storage::temporaryUrl($post->header_image, now()->addHours(1)) }}"
                 alt="{{ $post->title }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        </div>
    @else
        <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
    @endif

    <div class="flex flex-col flex-1 p-5">

        {{-- Tags --}}
        @if ($post->tags->isNotEmpty())
            <div class="flex flex-wrap gap-1.5 mb-3">
                @foreach ($post->tags->take(3) as $tag)
                    <a href="{{ route('tag', $tag->slug) }}"
                       class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Title --}}
        <h3 class="text-base font-semibold text-gray-900 leading-snug line-clamp-2 group-hover:text-gray-600 transition-colors">
            <a href="{{ route('post', $post->slug) }}">{{ $post->title }}</a>
        </h3>

        {{-- Excerpt --}}
        <p class="mt-2 text-sm text-gray-500 line-clamp-3 flex-1 leading-relaxed">
            {{ Str::limit(strip_tags($post->content), 130) }}
        </p>

        {{-- Meta --}}
        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
            <span class="font-medium text-gray-500">{{ $post->creator?->name ?? 'Unknown' }}</span>
            <time datetime="{{ $post->created_at->toIso8601String() }}">
                {{ $post->created_at->format('M j, Y') }}
            </time>
        </div>
    </div>
</article>
