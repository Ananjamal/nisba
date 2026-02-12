@props(['field', 'sortField', 'sortDirection', 'label'])

<th {{ $attributes->merge(['class' => 'group pb-4 font-semibold text-start text-gray-600 cursor-pointer hover:text-blue-600 transition select-none']) }}
    wire:click="sortBy('{{ $field }}')">
    <div class="flex items-center gap-1">
        {{ $label }}
        @if($sortField === $field)
        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
        </svg>
        @else
        <svg class="w-4 h-4 text-gray-400 group-hover:text-black transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
        </svg>
        @endif
    </div>
</th>