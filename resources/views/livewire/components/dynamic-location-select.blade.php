<div class="space-y-4">
    <!-- City Selection -->
    <div>
        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $cityLabel }} @if($required) <span class="text-red-500">*</span> @endif
        </label>
        <select 
            id="city" 
            wire:model.live="selectedCity" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $required ? 'required' : '' }}"
            {{ $required ? 'required' : '' }}
        >
            <option value="">اختر {{ $cityLabel }}</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}">{{ $city->name }}</option>
            @endforeach
        </select>
        @error('selectedCity')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Area Selection -->
    <div>
        <label for="area" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $areaLabel }} @if($required) <span class="text-red-500">*</span> @endif
        </label>
        <select 
            id="area" 
            wire:model.live="selectedArea" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $required ? 'required' : '' }}"
            {{ $required ? 'required' : '' }}
            {{ !$selectedCity ? 'disabled' : '' }}
        >
            <option value="">اختر {{ $areaLabel }}</option>
            @foreach($areas as $area)
                <option value="{{ $area->id }}">{{ $area->name }}</option>
            @endforeach
        </select>
        @error('selectedArea')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Location Summary -->
    @if($selectedCity && $selectedArea)
        <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
            <p class="text-sm text-blue-800">
                <strong>الموقع المحدد:</strong> 
                {{ $getCityName() }} - {{ $getAreaName() }}
            </p>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        // Add smooth transitions for area dropdown
        Livewire.hook('message.processed', (message, component) => {
            if (component.name === 'livewire.components.dynamic-location-select') {
                const areaSelect = document.getElementById('area');
                if (areaSelect) {
                    // Add fade effect when areas are loading
                    if (component.selectedCity && component.areas.length === 0) {
                        areaSelect.style.opacity = '0.5';
                    } else {
                        areaSelect.style.opacity = '1';
                    }
                }
            }
        });
    });
</script>
@endpush
