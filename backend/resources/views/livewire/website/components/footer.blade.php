<footer class="bg-gray-900 text-gray-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="text-white font-bold text-lg tracking-tight">
                {{ config('app.name') }}
            </a>
            <nav class="flex gap-6 text-sm">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                <a href="{{ route('blog') }}" class="hover:text-white transition-colors">Blog</a>
            </nav>
            <p class="text-sm">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</footer>
