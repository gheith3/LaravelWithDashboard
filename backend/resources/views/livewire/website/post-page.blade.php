<div class="min-h-screen flex flex-col">
    <livewire:website.components.navbar />

    <main class="flex-1">
        <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            {{-- Header --}}
            <header class="mb-8">
                @if ($post->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach ($post->tags as $tag)
                            <a href="{{ route('tag', $tag->slug) }}"
                               class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                {{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 leading-tight tracking-tight">
                    {{ $post->title }}
                </h1>

                <div class="mt-4 flex items-center gap-3 text-sm text-gray-500">
                    <span class="font-medium">{{ $post->creator?->name ?? 'Unknown' }}</span>
                    <span class="text-gray-300">·</span>
                    <time datetime="{{ $post->created_at->toIso8601String() }}">
                        {{ $post->created_at->format('F j, Y') }}
                    </time>
                </div>
            </header>

            {{-- Header image --}}
            @if ($post->header_image)
                <div class="mb-8 rounded-xl overflow-hidden">
                    <img src="{{ Storage::temporaryUrl($post->header_image, now()->addHours(1)) }}"
                         alt="{{ $post->title }}"
                         class="w-full aspect-video object-cover">
                </div>
            @endif

            {{-- Content --}}
            <div class="post-content text-gray-700 leading-relaxed">
                {!! $post->content !!}
            </div>

            {{-- Back link --}}
            <div class="mt-12 pt-8 border-t border-gray-200">
                <a href="{{ route('blog') }}"
                   class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to blog
                </a>
            </div>
        </article>

        {{-- Comments --}}
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
            <div class="border-t border-gray-200 pt-12">

                <h2 class="text-xl font-bold text-gray-900 mb-8">
                    Comments
                    @if ($comments->isNotEmpty())
                        <span class="text-sm font-normal text-gray-400 ml-1">({{ $comments->count() }})</span>
                    @endif
                </h2>

                {{-- Comment list --}}
                @if ($comments->isEmpty())
                    <p class="text-sm text-gray-400 mb-10">No comments yet. Be the first to share your thoughts!</p>
                @else
                    <div class="space-y-6 mb-12">
                        @foreach ($comments as $comment)
                            <div class="flex gap-4">
                                <div class="shrink-0 w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-sm font-bold text-gray-600">
                                    {{ strtoupper(mb_substr($comment->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-semibold text-gray-900">{{ $comment->name }}</span>
                                        <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 leading-relaxed">{{ $comment->content }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Comment form --}}
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-5">Leave a comment</h3>

                    @if ($submitted)
                        <div class="flex items-start gap-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg p-4">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Your comment has been submitted and is awaiting moderation. Thank you!
                        </div>
                    @else
                        <form wire:submit="submitComment" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="block text-xs font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="name" wire:model="name" autocomplete="name"
                                           class="w-full px-3 py-2 text-sm rounded-lg border bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition
                                               {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }}">
                                    @error('name')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="email" class="block text-xs font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                    <input type="email" id="email" wire:model="email" autocomplete="email"
                                           class="w-full px-3 py-2 text-sm rounded-lg border bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition
                                               {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }}">
                                    @error('email')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="phone" class="block text-xs font-medium text-gray-700 mb-1">
                                    Phone <span class="text-gray-400 font-normal">(optional)</span>
                                </label>
                                <input type="tel" id="phone" wire:model="phone" autocomplete="tel"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition">
                            </div>

                            <div>
                                <label for="comment" class="block text-xs font-medium text-gray-700 mb-1">Comment <span class="text-red-500">*</span></label>
                                <textarea id="comment" wire:model="comment" rows="4"
                                          class="w-full px-3 py-2 text-sm rounded-lg border bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition resize-y
                                              {{ $errors->has('comment') ? 'border-red-400' : 'border-gray-300' }}"></textarea>
                                @error('comment')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit"
                                    class="px-5 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="submitComment">Submit comment</span>
                                <span wire:loading wire:target="submitComment">Submitting…</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <livewire:website.components.footer />
</div>
