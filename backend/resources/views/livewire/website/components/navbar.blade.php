<nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <a href="{{ route('home') }}" class="text-xl font-bold text-gray-900 tracking-tight">
                {{ config('app.name') }}
            </a>

            {{-- Desktop links --}}
            <div class="hidden sm:flex items-center gap-8">
                <a href="{{ route('home') }}"
                   class="text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900' }}">
                    Home
                </a>
                <a href="{{ route('blog') }}"
                   class="text-sm font-medium transition-colors {{ request()->routeIs('blog', 'post', 'tag') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900' }}">
                    Blog
                </a>
            </div>

            {{-- Mobile toggle --}}
            <button wire:click="toggleMobile"
                    class="sm:hidden p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                    aria-label="Toggle menu">
                @if ($mobileOpen)
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                @endif
            </button>
        </div>

        {{-- Mobile menu --}}
        @if ($mobileOpen)
            <div class="sm:hidden border-t border-gray-100 py-3 space-y-1">
                <a href="{{ route('home') }}"
                   wire:click="$set('mobileOpen', false)"
                   class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Home
                </a>
                <a href="{{ route('blog') }}"
                   wire:click="$set('mobileOpen', false)"
                   class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Blog
                </a>
            </div>
        @endif
    </div>
</nav>
