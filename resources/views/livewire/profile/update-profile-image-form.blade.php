<div>
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="section-title">الصورة الشخصية</h3>
        </div>
    </div>

    <div class="flex items-center gap-6">
        <div class="relative" style="width: 120px; height: 120px; overflow: hidden; flex: 0 0 120px; border-radius: 16px;">
            <img src="{{ auth()->user()->profile_image_url }}" alt="{{ auth()->user()->name }}"
                class="border-4 border-white shadow-lg"
                style="display:block; width: 100%; height: 100%; object-fit: cover;">
        </div>

        <div class="flex-1 space-y-3">
            <div>
                <input type="file" wire:model="profileImage" accept="image/*" class="hidden" id="profileImageInput">

                <div class="flex items-center gap-3">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('profileImageInput').click()">
                        اختر صورة جديدة
                    </button>

                    @if($profileImage)
                        <button type="button" class="btn btn-primary" wire:click="save">
                            حفظ الصورة
                        </button>
                    @endif

                    @if(auth()->user()->profile_image)
                        <button type="button" class="btn btn-outline" wire:click="remove">
                            حذف الصورة
                        </button>
                    @endif
                </div>

                @error('profileImage')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror

                @if($profileImage)
                    <div class="mt-3">
                        <p class="text-xs text-secondary mb-2">معاينة</p>
                        <div style="width: 96px; height: 96px; overflow: hidden; border-radius: 12px;">
                            <img src="{{ $profileImage->temporaryUrl() }}" alt="Preview" class="border border-gray-200" style="display:block; width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
