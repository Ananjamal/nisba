<div>
    <!-- Multi-select Dropdown -->
    <div class="relative" x-data="{ open: false }">
        <select wire:model="selectedServices" wire:change="$wire.dispatch('serviceSelected', { serviceId: $event.target.value })" 
                @click="open = true"
                @focus="open = true"
                @blur="setTimeout(() => open = false, 200)"
                multiple
                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="" disabled>{{ $placeholder }}</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}">{{ $service->name }}</option>
            @endforeach
        </select>
        
        <!-- Dropdown Arrow -->
        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>

    <!-- Selected Items Display Area (Below Dropdown) -->
    @if(!empty($selectedServices))
        <div class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-bold text-blue-800">الخدمات المختارة:</h4>
                <button type="button" wire:click="clearAllServices" class="text-red-500 hover:text-red-700 text-sm">
                    مسح الكل
                </button>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($selectedServices as $serviceId)
                    @php
                        $service = \App\Models\Service::find($serviceId);
                    @endphp
                    @if($service)
                        <div class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-blue-200 rounded-lg shadow-sm">
                            @if($service->color)
                                <div class="w-3 h-3 rounded-full" style="background-color: {{ $service->color }}"></div>
                            @endif
                            <span class="text-sm font-medium text-gray-800">{{ $service->name }}</span>
                            <button type="button" wire:click="removeService({{ $serviceId }})" class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
