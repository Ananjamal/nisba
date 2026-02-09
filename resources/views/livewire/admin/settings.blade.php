<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Setting;

new #[Layout('layouts.admin')] class extends Component {
    public $site_name;
    public $commission_rate;

    public function mount()
    {
        $this->site_name = Setting::get('site_name', 'حليف');
        $this->commission_rate = Setting::get('commission_rate', 10);
    }

    public function save()
    {
        $this->validate([
            'site_name' => 'required|string|max:255',
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        Setting::set('site_name', $this->site_name);
        Setting::set('commission_rate', $this->commission_rate);

        $this->dispatch('toast', type: 'success', message: 'تم حفظ الإعدادات بنجاح');
    }
}; ?>

<div class="space-y-8">
    <div class="flex items-center justify-between bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">{{ __('إعدادات النظام') }}</h2>
            <p class="text-gray-500 font-medium mt-1">{{ __('تحكم في إعدادات الموقع والعمولات') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- General Settings -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ __('الإعدادات العامة') }}</h3>
            </div>

            <form wire:submit="save" class="space-y-6">
                <div>
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('اسم الموقع') }}</label>
                        <input type="text" wire:model="site_name" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 font-bold text-gray-900 placeholder-gray-400 p-3">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('نسبة العمولة الافتراضية (%)') }}</label>
                        <input type="number" wire:model="commission_rate" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 font-bold text-gray-900 placeholder-gray-400 p-3">
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full btn btn-primary">
                            <span>{{ __('حفظ التغييرات') }}</span>
                        </button>
                    </div>
            </form>
        </div>

        <!-- Security Settings -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ __('الأمان والحماية') }}</h3>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-gray-600 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">{{ __('التسجيل متاح') }}</h4>
                            <p class="text-xs text-gray-500">{{ __('السماح للمستخدمين الجدد بالتسجيل') }}</p>
                        </div>
                    </div>
                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="toggle" id="toggle" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer checked:right-0 checked:border-blue-500" />
                        <label for="toggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer checked:bg-blue-500"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>