@props(['field', 'sortField', 'sortDirection', 'label'])

<th {{ $attributes->merge(['class' => 'pb-4 font-semibold text-start text-gray-600 cursor-pointer hover:text-blue-600 transition select-none']) }}
    wire:click="sortBy('{{ $field }}')">
    <div class="flex items-center gap-1">
        {{ $label }}
        @if($sortField === $field)
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
        </svg>
        @else
        <svg class="w-4 h-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
        </svg>
        @endif
    </div>
</th>