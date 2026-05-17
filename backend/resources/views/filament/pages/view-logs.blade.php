<x-filament-panels::page>
    {{ $this->form }}

    @if($selectedFile)
        <div class="mt-6">
            {{-- File Info Card --}}
            <div class="p-4 mb-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex flex-wrap gap-4 text-sm">
                    <div>
                        <span class="font-semibold text-gray-600 dark:text-gray-400">File:</span>
                        <span class="ml-1 font-mono text-gray-900 dark:text-gray-100">{{ $selectedFile }}</span>
                    </div>
                    @if($fileSize)
                        <div>
                            <span class="font-semibold text-gray-600 dark:text-gray-400">Size:</span>
                            <span class="ml-1 text-gray-900 dark:text-gray-100">{{ $fileSize }}</span>
                        </div>
                    @endif
                    @if($lastModified)
                        <div>
                            <span class="font-semibold text-gray-600 dark:text-gray-400">Last Modified:</span>
                            <span class="ml-1 text-gray-900 dark:text-gray-100">{{ $lastModified }}</span>
                        </div>
                    @endif
                    @if($tailLines > 0)
                        <div>
                            <span class="font-semibold text-gray-600 dark:text-gray-400">Showing:</span>
                            <span class="ml-1 text-gray-900 dark:text-gray-100">Last {{ $tailLines }} lines</span>
                        </div>
                    @else
                        <div>
                            <span class="font-semibold text-gray-600 dark:text-gray-400">Showing:</span>
                            <span class="ml-1 text-yellow-600 dark:text-yellow-400">Full file (may be slow)</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Log Content --}}
            <div class="relative">
                <div class="absolute top-2 right-2 flex gap-2">
                    <button 
                        type="button"
                        onclick="copyToClipboard()"
                        class="px-3 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
                    >
                        Copy to Clipboard
                    </button>
                </div>

                <textarea 
                    id="log-content"
                    readonly
                    class="w-full h-[600px] p-4 font-mono text-sm text-gray-800 bg-white border border-gray-300 rounded-lg resize-y focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700"
                >{{ $logContent }}</textarea>
            </div>

            {{-- Legend --}}
            <div class="mt-4 flex flex-wrap gap-4 text-xs">
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Error</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Warning</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Info</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Success</span>
                </div>
            </div>
        </div>

        <script>
            function copyToClipboard() {
                const textarea = document.getElementById('log-content');
                textarea.select();
                document.execCommand('copy');
                
                // Show notification
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        type: 'success',
                        message: 'Log content copied to clipboard!'
                    }
                }));
            }

            // Auto refresh functionality
            @if($autoRefresh)
                setInterval(() => {
                    @this.call('refresh');
                }, 30000);
            @endif
        </script>
    @else
        <div class="mt-6 p-8 text-center text-gray-500 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-lg font-medium">Select a log file to view its contents</p>
            <p class="text-sm mt-1">Choose from the dropdown above to view system logs</p>
        </div>
    @endif
</x-filament-panels::page>
