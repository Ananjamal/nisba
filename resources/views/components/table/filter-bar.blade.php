@props(['statusOptions' => [], 'showDate' => true])

<div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 pt-2">

    <!-- Left Side: Search & Filters -->
    <div class="flex flex-wrap items-center gap-4 flex-1 min-w-0">

        <!-- Search -->
        <div class="relative flex-1 min-w-[200px] max-w-sm group">
            <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="بحث..."
                class="w-full pl-10 pr-10 py-2.5 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 shadow-sm transition-all text-sm font-bold text-gray-900 placeholder:text-gray-400 hover:border-gray-300">
        </div>

        <!-- Status Filter -->
        @if(!empty($statusOptions))
        <div class="relative min-w-[140px] group">
            <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <select wire:model.live="status_filter"
                class="w-full appearance-none pl-9 pr-10 py-2.5 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 cursor-pointer shadow-sm transition-all text-sm font-bold text-gray-700 hover:border-gray-300 hover:text-gray-900">
                <option value="">الحالة</option>
                @foreach($statusOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
        @endif

        <!-- Additional Filters Slot -->
        {{ $slot }}

        <!-- Date Range Picker -->
        @if($showDate)
        <div class="flex items-center bg-white border border-gray-200 rounded-xl px-1 py-0.5 shadow-sm transition-all hover:border-gray-300 focus-within:ring-2 focus-within:ring-primary-500/20 focus-within:border-primary-500">
            <div class="relative">
                <input type="date" wire:model.live="date_from"
                    class="py-2 px-3 bg-transparent border-0 focus:ring-0 text-xs font-bold text-gray-600 w-[115px] cursor-pointer hover:text-gray-900 transition-colors"
                    title="من تاريخ">
            </div>
            <span class="text-gray-200 font-light h-5 w-px bg-gray-200 block"></span>
            <div class="relative">
                <input type="date" wire:model.live="date_to"
                    class="py-2 px-3 bg-transparent border-0 focus:ring-0 text-xs font-bold text-gray-600 w-[115px] cursor-pointer hover:text-gray-900 transition-colors"
                    title="إلى تاريخ">
            </div>
        </div>
        @endif

        <!-- Reset Filters Button -->
        @if((isset($search) && $search) || (isset($status_filter) && $status_filter) || (isset($date_from) && $date_from) || (isset($date_to) && $date_to))
        <button wire:click="$set('search', ''); $set('status_filter', ''); $set('date_from', ''); $set('date_to', '')"
            class="p-2.5 text-rose-500 hover:text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-all shadow-sm border border-rose-100 flex-shrink-0"
            title="مسح الفلاتر">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>
        @endif
    </div>

    <!-- Right Side: Actions (Buttons) -->
    @if(isset($actions))
    <div class="flex items-center gap-2 flex-shrink-0">
        {{ $actions }}
    </div>
    @endif
</div>