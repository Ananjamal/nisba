<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">كيف تخطط للترويج؟</label>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($availablePlans as $plan)
            <div class="relative">
                <input type="radio" 
                       name="{{ $name }}" 
                       value="{{ $plan->id }}" 
                       wire:model="selectedPlan"
                       wire:click="selectPlan({{ $plan->id }})"
                       class="peer sr-only"
                       id="{{ $name }}_{{ $plan->id }}">
                
                <label for="{{ $name }}_{{ $plan->id }}" 
                       class="block p-4 border-2 rounded-lg cursor-pointer transition-all
                              peer-checked:border-blue-500 peer-checked:bg-blue-50
                              hover:border-gray-300 hover:bg-gray-50">
                    <div class="flex items-center mb-2">
                        <span class="text-2xl mr-2">{{ $plan->icon }}</span>
                        <h3 class="font-semibold text-gray-900">{{ $plan->name }}</h3>
                    </div>
                    
                    @if($plan->description)
                        <p class="text-sm text-gray-600">{{ $plan->description }}</p>
                    @endif
                    
                    <div class="mt-3 flex items-center justify-between">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border"
                              style="background-color: {{ $plan->color }}20; color: {{ $plan->color }}; border-color: {{ $plan->color }};">
                            مختار
                        </span>
                        
                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-blue-500 
                                    peer-checked:bg-blue-500 flex items-center justify-center">
                            <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                        </div>
                    </div>
                </label>
            </div>
        @endforeach
    </div>
    
    @if($selectedPlan && $selectedPlan)
        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-center">
                <span class="text-xl mr-2">{{ $selectedPlan->icon }}</span>
                <div>
                    <p class="font-medium text-blue-900">تم اختيار: {{ $selectedPlan->name }}</p>
                    @if($selectedPlan->description)
                        <p class="text-sm text-blue-700">{{ $selectedPlan->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
    
    @error($name)
        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
    @enderror
</div>
