@php
    $record = $getRecord();
    
    // Get all activity logs for the same subject
    $subjectHistory = collect();
    
    if ($record->subject_type && $record->subject_id) {
        $subjectHistory = \Spatie\Activitylog\Models\Activity::query()
            ->where('subject_type', $record->subject_type)
            ->where('subject_id', $record->subject_id)
            ->with(['causer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
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
    
    // Get event color
    $getEventColor = function ($event) {
        return match ($event) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            'restored' => 'warning',
            'login' => 'success',
            'logout' => 'gray',
            default => 'primary',
        };
    };
    
    // Get event icon
    $getEventIcon = function ($event) {
        return match ($event) {
            'created' => 'heroicon-m-plus-circle',
            'updated' => 'heroicon-m-pencil-square',
            'deleted' => 'heroicon-m-trash',
            'restored' => 'heroicon-m-arrow-uturn-left',
            'login' => 'heroicon-m-arrow-right-end-on-rectangle',
            'logout' => 'heroicon-m-arrow-left-start-on-rectangle',
            default => 'heroicon-m-information-circle',
        };
    };
@endphp

@if($subjectHistory->isNotEmpty())
    <div class="space-y-4">
        @foreach($subjectHistory as $activity)
            @php
                $oldValues = $activity->properties['old'] ?? [];
                $newValues = $activity->properties['attributes'] ?? [];
                $allKeys = collect(array_keys($oldValues))
                    ->merge(array_keys($newValues))
                    ->unique()
                    ->sort()
                    ->values();
                $hasChanges = !empty($oldValues) || !empty($newValues);
                $isCurrent = $activity->id === $record->id;
            @endphp
            
            <div class="relative">
                {{-- Timeline connector line --}}
                @if(!$loop->last)
                    <div class="absolute left-5 top-10 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                @endif
                
                <div class="flex gap-4">
                    {{-- Timeline dot --}}
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $isCurrent ? 'ring-2 ring-offset-2 ring-primary-500 dark:ring-offset-gray-900' : '' }} bg-{{ $getEventColor($activity->event) }}-100 dark:bg-{{ $getEventColor($activity->event) }}-500/20">
                            <x-dynamic-component 
                                :component="$getEventIcon($activity->event)" 
                                class="w-5 h-5 text-{{ $getEventColor($activity->event) }}-600 dark:text-{{ $getEventColor($activity->event) }}-400" 
                            />
                        </div>
                    </div>
                    
                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden {{ $isCurrent ? 'ring-1 ring-primary-500 dark:ring-primary-400' : '' }}">
                            {{-- Header --}}
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $getEventColor($activity->event) }}-100 dark:bg-{{ $getEventColor($activity->event) }}-500/20 text-{{ $getEventColor($activity->event) }}-700 dark:text-{{ $getEventColor($activity->event) }}-400">
                                            {{ Str::title($activity->event ?? 'Unknown') }}
                                        </span>
                                        
                                        @if($activity->causer)
                                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                                by <span class="font-medium text-gray-900 dark:text-white">{{ $activity->causer->name ?? 'Unknown' }}</span>
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">by System</span>
                                        @endif
                                        
                                        @if($isCurrent)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 dark:bg-primary-500/20 text-primary-700 dark:text-primary-400">
                                                Current
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                
                                <div class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $activity->description }}
                                </div>
                                
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $activity->created_at->format('F j, Y \a\t g:i:s A') }}
                                </div>
                            </div>
                            
                            {{-- Changes section (collapsible) --}}
                            @if($hasChanges && $allKeys->isNotEmpty())
                                <div x-data="{ open: {{ $isCurrent ? 'true' : 'false' }} }" class="border-t border-gray-200 dark:border-gray-700">
                                    <button 
                                        @click="open = !open" 
                                        type="button"
                                        class="w-full px-4 py-2 flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                    >
                                        <span class="flex items-center gap-2">
                                            <x-heroicon-m-arrow-path class="w-4 h-4" />
                                            Changes ({{ $allKeys->count() }} field{{ $allKeys->count() !== 1 ? 's' : '' }})
                                        </span>
                                        <x-heroicon-m-chevron-down 
                                            class="w-4 h-4 transition-transform" 
                                            ::class="{ 'rotate-180': open }"
                                        />
                                    </button>
                                    
                                    <div x-show="open" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                                        <div class="p-4 overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                                                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-600 w-1/4">
                                                            Field
                                                        </th>
                                                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-600 w-[37.5%]">
                                                            <span class="inline-flex items-center gap-1.5">
                                                                <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-red-100 dark:bg-red-500/20">
                                                                    <x-heroicon-m-minus class="w-2.5 h-2.5 text-red-600 dark:text-red-400" />
                                                                </span>
                                                                Old
                                                            </span>
                                                        </th>
                                                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-600 w-[37.5%]">
                                                            <span class="inline-flex items-center gap-1.5">
                                                                <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-green-100 dark:bg-green-500/20">
                                                                    <x-heroicon-m-plus class="w-2.5 h-2.5 text-green-600 dark:text-green-400" />
                                                                </span>
                                                                New
                                                            </span>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                    @foreach($allKeys as $key)
                                                        @php
                                                            $oldValue = $oldValues[$key] ?? null;
                                                            $newValue = $newValues[$key] ?? null;
                                                            $hasOld = array_key_exists($key, $oldValues);
                                                            $hasNew = array_key_exists($key, $newValues);
                                                            $isChanged = $hasOld && $hasNew && $oldValue !== $newValue;
                                                            $isAdded = !$hasOld && $hasNew;
                                                            $isRemoved = $hasOld && !$hasNew;
                                                        @endphp
                                                        <tr class="@if($isChanged) bg-amber-50/50 dark:bg-amber-500/5 @elseif($isAdded) bg-green-50/50 dark:bg-green-500/5 @elseif($isRemoved) bg-red-50/50 dark:bg-red-500/5 @endif">
                                                            <td class="px-3 py-2 align-top">
                                                                <div class="flex items-center gap-2">
                                                                    @if($isChanged)
                                                                        <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-amber-100 dark:bg-amber-500/20 flex-shrink-0">
                                                                            <x-heroicon-m-pencil class="w-2.5 h-2.5 text-amber-600 dark:text-amber-400" />
                                                                        </span>
                                                                    @elseif($isAdded)
                                                                        <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-green-100 dark:bg-green-500/20 flex-shrink-0">
                                                                            <x-heroicon-m-plus class="w-2.5 h-2.5 text-green-600 dark:text-green-400" />
                                                                        </span>
                                                                    @elseif($isRemoved)
                                                                        <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-red-100 dark:bg-red-500/20 flex-shrink-0">
                                                                            <x-heroicon-m-minus class="w-2.5 h-2.5 text-red-600 dark:text-red-400" />
                                                                        </span>
                                                                    @else
                                                                        <span class="w-4 h-4 flex-shrink-0"></span>
                                                                    @endif
                                                                    <span class="font-medium text-gray-700 dark:text-gray-300">
                                                                        {{ Str::of($key)->replace('_', ' ')->title() }}
                                                                    </span>
                                                                </div>
                                                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 ml-6 font-mono">
                                                                    {{ $key }}
                                                                </div>
                                                            </td>
                                                            <td class="px-3 py-2 align-top font-mono text-xs">
                                                                @if($hasOld)
                                                                    <div class="@if($isChanged || $isRemoved) text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-1 rounded @else text-gray-600 dark:text-gray-400 @endif break-words">
                                                                        {!! $formatValue($oldValue) !!}
                                                                    </div>
                                                                @else
                                                                    <span class="text-gray-300 dark:text-gray-600 italic">—</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-3 py-2 align-top font-mono text-xs">
                                                                @if($hasNew)
                                                                    <div class="@if($isChanged || $isAdded) text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-500/10 px-2 py-1 rounded @else text-gray-600 dark:text-gray-400 @endif break-words">
                                                                        {!! $formatValue($newValue) !!}
                                                                    </div>
                                                                @else
                                                                    <span class="text-gray-300 dark:text-gray-600 italic">—</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    {{-- Legend --}}
    <div class="mt-4 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 pt-4">
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
@else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        <x-heroicon-o-clock class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" />
        <p>No history available for this subject.</p>
    </div>
@endif
