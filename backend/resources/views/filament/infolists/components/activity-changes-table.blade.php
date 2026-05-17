@php
    $record = $getRecord();
    $oldValues = $record->properties['old'] ?? [];
    $newValues = $record->properties['attributes'] ?? [];
    
    // Get all unique keys from both old and new values
    $allKeys = collect(array_keys($oldValues))
        ->merge(array_keys($newValues))
        ->unique()
        ->sort()
        ->values();
    
    // Helper function to format value for display
    $formatValue = function ($value) {
        if (is_null($value)) {
            return '<span class="text-gray-400 italic">null</span>';
        }
        if (is_bool($value)) {
            return $value 
                ? '<span class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-500/10 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-500/30">true</span>'
                : '<span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-500/10 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-600/20 dark:ring-red-500/30">false</span>';
        }
        if (is_array($value)) {
            return '<code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">' . e(json_encode($value, JSON_PRETTY_PRINT)) . '</code>';
        }
        if (is_string($value) && strlen($value) > 100) {
            return '<span class="break-all">' . e($value) . '</span>';
        }
        return e($value);
    };
@endphp

<div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-white/5">
                <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-white/10 w-1/4">
                    Field
                </th>
                <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-white/10 w-[37.5%]">
                    <span class="inline-flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-red-100 dark:bg-red-500/20">
                            <x-heroicon-m-minus class="w-3 h-3 text-red-600 dark:text-red-400" />
                        </span>
                        Old Value
                    </span>
                </th>
                <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-white/10 w-[37.5%]">
                    <span class="inline-flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-green-100 dark:bg-green-500/20">
                            <x-heroicon-m-plus class="w-3 h-3 text-green-600 dark:text-green-400" />
                        </span>
                        New Value
                    </span>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
            @forelse($allKeys as $key)
                @php
                    $oldValue = $oldValues[$key] ?? null;
                    $newValue = $newValues[$key] ?? null;
                    $hasOld = array_key_exists($key, $oldValues);
                    $hasNew = array_key_exists($key, $newValues);
                    $isChanged = $hasOld && $hasNew && $oldValue !== $newValue;
                    $isAdded = !$hasOld && $hasNew;
                    $isRemoved = $hasOld && !$hasNew;
                @endphp
                <tr class="@if($isChanged) bg-amber-50/50 dark:bg-amber-500/5 @elseif($isAdded) bg-green-50/50 dark:bg-green-500/5 @elseif($isRemoved) bg-red-50/50 dark:bg-red-500/5 @endif hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <td class="px-4 py-3 align-top">
                        <div class="flex items-center gap-2">
                            @if($isChanged)
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-amber-100 dark:bg-amber-500/20 flex-shrink-0">
                                    <x-heroicon-m-pencil class="w-3 h-3 text-amber-600 dark:text-amber-400" />
                                </span>
                            @elseif($isAdded)
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-green-100 dark:bg-green-500/20 flex-shrink-0">
                                    <x-heroicon-m-plus class="w-3 h-3 text-green-600 dark:text-green-400" />
                                </span>
                            @elseif($isRemoved)
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-red-100 dark:bg-red-500/20 flex-shrink-0">
                                    <x-heroicon-m-minus class="w-3 h-3 text-red-600 dark:text-red-400" />
                                </span>
                            @else
                                <span class="w-5 h-5 flex-shrink-0"></span>
                            @endif
                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                {{ Str::of($key)->replace('_', ' ')->title() }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 ml-7 font-mono">
                            {{ $key }}
                        </div>
                    </td>
                    <td class="px-4 py-3 align-top font-mono text-xs">
                        @if($hasOld)
                            <div class="@if($isChanged || $isRemoved) text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-1.5 rounded-md @else text-gray-600 dark:text-gray-400 @endif break-words">
                                {!! $formatValue($oldValue) !!}
                            </div>
                        @else
                            <span class="text-gray-300 dark:text-gray-600 italic">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 align-top font-mono text-xs">
                        @if($hasNew)
                            <div class="@if($isChanged || $isAdded) text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-500/10 px-2 py-1.5 rounded-md @else text-gray-600 dark:text-gray-400 @endif break-words">
                                {!! $formatValue($newValue) !!}
                            </div>
                        @else
                            <span class="text-gray-300 dark:text-gray-600 italic">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        No changes recorded
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($allKeys->isNotEmpty())
    <div class="mt-3 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
        <span class="inline-flex items-center gap-1.5">
            <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-amber-100 dark:bg-amber-500/20">
                <x-heroicon-m-pencil class="w-2.5 h-2.5 text-amber-600 dark:text-amber-400" />
            </span>
            Modified
        </span>
        <span class="inline-flex items-center gap-1.5">
            <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-green-100 dark:bg-green-500/20">
                <x-heroicon-m-plus class="w-2.5 h-2.5 text-green-600 dark:text-green-400" />
            </span>
            Added
        </span>
        <span class="inline-flex items-center gap-1.5">
            <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-red-100 dark:bg-red-500/20">
                <x-heroicon-m-minus class="w-2.5 h-2.5 text-red-600 dark:text-red-400" />
            </span>
            Removed
        </span>
    </div>
@endif
