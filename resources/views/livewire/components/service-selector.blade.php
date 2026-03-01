<div x-data="{ showDropdown: @entangle('showDropdown') }" class="relative">
    <label class="block text-sm font-medium text-gray-700 mb-2">الخدمات</label>
    
    <!-- Selected Services Tags -->
    <div class="mb-2">
        <div class="flex flex-wrap gap-2" wire:ignore>
            @foreach($selectedServices as $service)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border"
                      style="background-color: {{ $service->color }}20; color: {{ $service->color }}; border-color: {{ $service->color }};">
                    {{ $service->name }}
                    <button type="button" wire:click="removeService({{ $service->id }})" 
                            class="ml-2 text-xs hover:opacity-70">
                        ×
                    </button>
                </span>
            @endforeach
        </div>
        @if(count($selectedServices) === 0)
            <p class="text-sm text-gray-500">لم يتم اختيار أي خدمات بعد</p>
        @endif
    </div>
    
    <!-- Search Input -->
    <div class="relative">
        <input type="text" 
               wire:model.live="searchTerm" 
               wire:click="showDropdown = true"
               @click.away="showDropdown = false"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="ابحث عن خدمات...">
        
        <!-- Dropdown -->
        <div x-show="showDropdown" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
            @if($availableServices->count() > 0)
                <div class="py-1">
                    @foreach($availableServices as $service)
                        <button type="button" 
                                wire:click="toggleService({{ $service->id }})"
                                class="w-full text-right px-4 py-2 text-sm hover:bg-gray-100 flex items-center justify-between">
                            <span>{{ $service->name }}</span>
                            @if(in_array($service->id, $selectedServices))
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            @else
                <div class="px-4 py-2 text-sm text-gray-500">
                    @if($searchTerm)
                        لا توجد خدمات مطابقة للبحث
                    @else
                        لا توجد خدمات متاحة
                    @endif
                </div>
            @endif
        </div>
    </div>
    
    <!-- Hidden Input for Form Submission -->
    <div class="hidden">
        @foreach($selectedServices as $serviceId)
            <input type="hidden" name="{{ $name }}[]" value="{{ $serviceId }}">
        @endforeach
    </div>
</div>
