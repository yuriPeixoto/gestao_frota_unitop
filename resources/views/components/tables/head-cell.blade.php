@props([
    'sortable' => false,
    'field' => null, 
    'currentSort' => null, 
    'direction' => null,
    'width' => null,
    'align' => 'left',
])

@php
    $sortableClass = $sortable ? 'cursor-pointer' : '';
    $sortDirection = $field === $currentSort ? $direction : null;
    $alignmentClass = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ][$align] ?? 'text-left';
    
    $styles = $width ? "width: $width;" : '';
@endphp

<th {{ $attributes->merge([
    'class' => "px-6 py-3 text-xs font-medium text-gray-900 uppercase tracking-wider $alignmentClass $sortableClass",
    'style' => $styles
]) }}>
    <div class="flex items-center space-x-1">
        <span>{{ $slot }}</span>
        
        @if($sortable)
            <span class="inline-flex flex-col">
                @if($sortDirection === 'asc')
                    <svg class="w-3 h-3 text-indigo-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                @elseif($sortDirection === 'desc')
                    <svg class="w-3 h-3 text-indigo-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                @else
                    <svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                @endif
            </span>
        @endif
    </div>
</th>