<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    <!-- Selected Services Display -->
    @if($displayMode === 'tags')
        <div class="border border-gray-300 rounded-md p-2 min-h-[42px]">
            <div class="flex flex-wrap gap-2">
                @foreach($selectedServicesData as $service)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                        {{ $service->name }}
                        <button type="button" wire:click="removeService({{ $service->id }})" class="mr-2 hover:text-blue-600">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </span>
                @endforeach
                
                @if(count($selectedServicesData) < $maxSelection)
                    <button type="button" @click="open = !open" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    @endif

    <!-- Search Input -->
    @if($displayMode === 'dropdown')
        <div class="relative">
            <input 
                type="text" 
                wire:model.live="searchTerm" 
                @click="open = true"
                @focus="open = true"
                placeholder="{{ $placeholder }} ({{ count($selectedServices) }}/{{ $maxSelection }})"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @if(count($selectedServices) >= $maxSelection) disabled @endif
            >
            
            @if(count($selectedServices) >= $maxSelection)
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            @endif
        </div>
    @endif

    <!-- Category Filter -->
    @if($displayMode === 'cards' || $categories->count() > 0)
        <div class="mb-3">
            <select wire:model.live="selectedCategory" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">جميع الفئات</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <!-- Dropdown -->
    @if($displayMode === 'dropdown' && $open && $filteredServices->count() > 0)
        <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
            @foreach($filteredServices as $service)
                <div 
                    wire:click="selectService({{ $service->id }})"
                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            @if($service->color)
                                <div class="w-3 h-3 rounded-full ml-2" style="background-color: {{ $service->color }}"></div>
                            @endif
                            <div>
                                <div class="font-medium text-gray-900">{{ $service->name }}</div>
                                @if($service->description)
                                    <div class="text-sm text-gray-500">{{ Str::limit($service->description, 50) }}</div>
                                @endif
                            </div>
                        </div>
                        @if($service->category)
                            <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">{{ $service->category }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Cards Display -->
    @if($displayMode === 'cards')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($filteredServices as $service)
                <div 
                    wire:click="selectService({{ $service->id }})"
                    class="border border-gray-200 rounded-lg p-3 hover:border-blue-500 hover:shadow-md transition cursor-pointer {{ in_array($service->id, $selectedServices) ? 'border-blue-500 bg-blue-50' : '' }}"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            @if($service->color)
                                <div class="w-4 h-4 rounded-full ml-2" style="background-color: {{ $service->color }}"></div>
                            @endif
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $service->name }}</h4>
                                @if($service->description)
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($service->description, 80) }}</p>
                                @endif
                            </div>
                        </div>
                        @if(in_array($service->id, $selectedServices))
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                    </div>
                    @if($service->category)
                        <div class="mt-2">
                            <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">{{ $service->category }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Selected Services Summary -->
    @if($selectedServicesData->count() > 0 && $displayMode !== 'tags')
        <div class="mt-3 p-3 bg-gray-50 rounded-md">
            <div class="text-sm font-medium text-gray-700 mb-2">الخدمات المختارة:</div>
            <div class="flex flex-wrap gap-2">
                @foreach($selectedServicesData as $service)
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $service->name }}
                        <button type="button" wire:click="removeService({{ $service->id }})" class="mr-1 hover:text-blue-600">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- No Results -->
    @if($filteredServices->count() === 0 && $searchTerm)
        <div class="text-center py-4 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p>لا توجد خدمات مطابقة للبحث</p>
        </div>
    @endif

    <!-- Selection Limit Message -->
    @if(count($selectedServices) >= $maxSelection)
        <div class="mt-2 text-sm text-amber-600 bg-amber-50 p-2 rounded">
            تم الوصول إلى الحد الأقصى لعدد الخدمات ({{ $maxSelection }})
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                @this.closeDropdown();
            }
        });
    });
</script>
@endpush
