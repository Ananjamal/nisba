<div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{{ $name }}Selector">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">المدينة</label>
        <select wire:model.live="selectedCity" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">اختر المدينة</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}">{{ $city->name }}</option>
            @endforeach
        </select>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">المنطقة</label>
        <select wire:model.live="selectedArea" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                {{ !$selectedCity ? 'disabled' : '' }}>
            <option value="">اختر المنطقة</option>
            @foreach($areas as $area)
                <option value="{{ $area->id }}">{{ $area->name }}</option>
            @endforeach
        </select>
        @if(!$selectedCity)
            <p class="text-xs text-gray-500 mt-1">يرجى اختيار المدينة أولاً</p>
        @endif
    </div>
</div>
