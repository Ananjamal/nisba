<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-6" x-data="{ show: false }">
    <div class="alert alert-error">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <div class="flex-1">
            <p class="font-bold">{{ __('منطقة الخطر') }}</p>
            <p class="text-sm mt-1">{{ __('بمجرد حذف حسابك، سيتم مسح جميع البيانات بشكل دائم. لا يمكن التراجع عن هذا الإجراء.') }}</p>
        </div>
        <button @click="show = true" class="btn btn-sm bg-white text-red-600 hover:bg-red-50 border-none">
            {{ __('حذف الحساب') }}
        </button>
    </div>

    <!-- Delete Account Modal -->
    <template x-teleport="body">
        <div x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 overflow-y-auto px-4 z-50" style="display: none;">
            <div class="flex items-center justify-center min-h-screen">
                <div @click="show = false" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

                <div class="bg-white rounded-2xl shadow-xl relative w-full max-w-md"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    <div class="p-6">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 text-red-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('هل أنت متأكد من حذف حسابك؟') }}</h3>
                            <p class="text-sm text-gray-500 mt-2">
                                {{ __('الرجاء إدخال كلمة المرور لتأكيد العملية. سيتم حذف جميع البيانات والاشتراكات بشكل نهائي.') }}
                            </p>
                        </div>

                        <form wire:submit="deleteUser" class="space-y-4">
                            <div class="form-group">
                                <label class="form-label sr-only">{{ __('كلمة المرور') }}</label>
                                <input type="password" wire:model="password" class="form-input" placeholder="{{ __('أدخل كلمة المرور للتأكيد') }}">
                                @error('password') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <button type="button" @click="show = false" class="btn btn-outline flex-1">
                                    {{ __('إلغاء') }}
                                </button>
                                <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white flex-1 justify-center border-none">
                                    {{ __('تأكيد الحذف') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
</section>