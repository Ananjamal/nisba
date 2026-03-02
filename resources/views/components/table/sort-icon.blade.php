@props(['field', 'sortField', 'sortDirection'])

@if($sortField === $field)
<svg class="w-4 h-4 text-primary-600 transition-transform duration-300 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
</svg>
@else
<svg class="w-4 h-4 text-gray-300 group-hover:text-primary-400 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
</svg>
@endif