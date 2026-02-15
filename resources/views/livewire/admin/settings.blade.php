<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Setting;

new #[Layout('layouts.admin')] class extends Component {
    use WithFileUploads;
    // General Settings
    public $site_name;
    public $site_description;
    public $support_email;
    public $phone_number;
    public $address;

    // Financial Settings
    public $commission_rate;
    public $min_withdrawal_amount;
    public $currency_symbol;

    // Social Media
    public $facebook_url;
    public $twitter_url;
    public $instagram_url;
    public $linkedin_url;
    public $youtube_url;

    // System & Security
    public $allow_registration;
    public $maintenance_mode;
    public $debug_mode;


    public function mount()
    {
        // General
        $this->site_name = Setting::get('site_name', 'حليف');
        $this->site_description = Setting::get('site_description', '');
        $this->support_email = Setting::get('support_email', '');
        $this->phone_number = Setting::get('phone_number', '');
        $this->address = Setting::get('address', '');

        // Financial
        $this->commission_rate = Setting::get('commission_rate', 10);
        $this->min_withdrawal_amount = Setting::get('min_withdrawal_amount', 50);
        $this->currency_symbol = Setting::get('currency_symbol', 'ر.س');

        // Social
        $this->facebook_url = Setting::get('facebook_url', '');
        $this->twitter_url = Setting::get('twitter_url', '');
        $this->instagram_url = Setting::get('instagram_url', '');
        $this->linkedin_url = Setting::get('linkedin_url', '');
        $this->youtube_url = Setting::get('youtube_url', '');

        // System
        $this->allow_registration = (bool) Setting::get('allow_registration', true);
        $this->maintenance_mode = (bool) Setting::get('maintenance_mode', false);
        $this->debug_mode = (bool) Setting::get('debug_mode', false);
    }

    public function save()
    {
        $this->validate([
            'site_name' => 'required|string|max:255',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'support_email' => 'nullable|email',
            'min_withdrawal_amount' => 'required|numeric|min:0',
        ]);

        // General
        Setting::set('site_name', $this->site_name);
        Setting::set('site_description', $this->site_description);
        Setting::set('support_email', $this->support_email);
        Setting::set('phone_number', $this->phone_number);
        Setting::set('address', $this->address);

        // Financial
        Setting::set('commission_rate', $this->commission_rate);
        Setting::set('min_withdrawal_amount', $this->min_withdrawal_amount);
        Setting::set('currency_symbol', $this->currency_symbol);

        // Social
        Setting::set('facebook_url', $this->facebook_url);
        Setting::set('twitter_url', $this->twitter_url);
        Setting::set('instagram_url', $this->instagram_url);
        Setting::set('linkedin_url', $this->linkedin_url);
        Setting::set('youtube_url', $this->youtube_url);

        // System
        Setting::set('allow_registration', $this->allow_registration);
        Setting::set('maintenance_mode', $this->maintenance_mode);
        Setting::set('debug_mode', $this->debug_mode);

        $this->dispatch('toast', type: 'success', message: 'تم حفظ كافة الإعدادات بنجاح');
    }
}; ?>

<div class="max-w-7xl mx-auto space-y-8 pb-12">
    <!-- Header -->
    <div class="flex items-end justify-between bg-white p-6 rounded-3xl shadow-sm border border-gray-200">
        <div>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">{{ __('إعدادات النظام') }}</h2>
            <p class="text-gray-500 font-bold mt-2">{{ __('تحكم شامل في كافة خصائص ومميزات المنصة') }}</p>
        </div>
        <button wire:click="save" wire:loading.attr="disabled" class="btn btn-primary px-8 py-4 shadow-xl shadow-primary-500/20 flex items-center gap-3 hover:scale-105 active:scale-95 transition-all duration-300 bg-gradient-to-l from-primary-600 to-primary-500 border-0">
            <span wire:loading.remove class="font-black text-lg">حفظ التغييرات</span>
            <span wire:loading class="flex items-center gap-2 font-bold">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                جاري الحفظ...
            </span>
        </button>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

        <!-- General Settings Card -->
        <div class="bg-white rounded-[2rem] border border-gray-200 shadow-sm overflow-hidden group hover:shadow-2xl hover:shadow-primary-500/10 hover:-translate-y-1 transition-all duration-500 md:col-span-2 relative">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-primary-500 via-blue-500 to-primary-500"></div>
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-b from-gray-50/50 to-white">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary-50 rounded-2xl border border-primary-100 flex items-center justify-center text-primary-600 shadow-sm group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900 text-xl">الإعدادات العامة</h3>
                        <p class="text-xs font-bold text-gray-400 mt-1">المعلومات الأساسية للمنصة</p>
                    </div>
                </div>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-3 col-span-2">
                    <label class="text-xs font-black text-primary-600 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                        اسم الموقع
                    </label>
                    <input type="text" wire:model="site_name" class="w-full rounded-2xl border-2 border-gray-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 font-black text-lg transition-all bg-gray-50/30 focus:bg-white placeholder-gray-300 p-4">
                </div>

                <div class="space-y-3 col-span-2">
                    <label class="text-xs font-black text-primary-600 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                        وصف الموقع
                    </label>
                    <textarea wire:model="site_description" rows="3" class="w-full rounded-2xl border-2 border-gray-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 font-bold transition-all bg-gray-50/30 focus:bg-white resize-none p-4 placeholder-gray-300"></textarea>
                </div>

                <div class="space-y-3">
                    <label class="text-xs font-black text-gray-500 uppercase tracking-wider">البريد الإلكتروني للدعم</label>
                    <div class="relative group/input">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-primary-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <input type="email" wire:model="support_email" class="w-full rounded-2xl border-2 border-gray-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 font-bold transition-all bg-gray-50/30 focus:bg-white pl-12 p-4" dir="ltr">
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-xs font-black text-gray-500 uppercase tracking-wider">رقم الهاتف</label>
                    <div class="relative group/input">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-primary-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <input type="text" wire:model="phone_number" class="w-full rounded-2xl border-2 border-gray-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 font-bold transition-all bg-gray-50/30 focus:bg-white pl-12 p-4" dir="ltr">
                    </div>
                </div>

                <div class="space-y-3 col-span-2">
                    <label class="text-xs font-black text-gray-500 uppercase tracking-wider">العنوان</label>
                    <input type="text" wire:model="address" class="w-full rounded-2xl border-2 border-gray-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 font-bold transition-all bg-gray-50/30 focus:bg-white p-4">
                </div>
            </div>
        </div>

        <!-- Financial Settings Card -->
        <div class="bg-white rounded-[2rem] border border-gray-200 shadow-sm overflow-hidden group hover:shadow-2xl hover:shadow-emerald-500/10 hover:-translate-y-1 transition-all duration-500 relative">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-b from-emerald-50/30 to-white">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm group-hover:rotate-12 transition-transform duration-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900 text-xl">المالية</h3>
                        <p class="text-xs font-bold text-gray-400 mt-1">العمولات والسحب</p>
                    </div>
                </div>
            </div>
            <div class="p-8 space-y-6">
                <div class="space-y-3">
                    <label class="text-xs font-black text-emerald-600 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        نسبة العمولة الافتراضية
                    </label>
                    <div class="relative">
                        <input type="number" wire:model="commission_rate" class="w-full rounded-2xl border-2 border-emerald-100 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 font-black text-2xl transition-all pl-12 bg-emerald-50/10 focus:bg-white text-emerald-700 p-4">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-emerald-600">
                            <span class="font-black text-lg">%</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-xs font-black text-emerald-600 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        الحد الأدنى للسحب
                    </label>
                    <div class="relative">
                        <input type="number" wire:model="min_withdrawal_amount" class="w-full rounded-2xl border-2 border-emerald-100 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 font-black text-2xl transition-all pl-16 bg-emerald-50/10 focus:bg-white text-emerald-700 p-4">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-emerald-600">
                            <span class="font-black text-sm">{{ $currency_symbol }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-xs font-black text-gray-500 uppercase tracking-wider">رمز العملة</label>
                    <input type="text" wire:model="currency_symbol" class="w-full rounded-2xl border-2 border-gray-100 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 font-bold transition-all bg-gray-50/30 focus:bg-white text-center p-4">
                </div>
            </div>
        </div>

        <!-- System & Security Card -->
        <div class="bg-white rounded-[2rem] border border-gray-200 shadow-sm overflow-hidden group hover:shadow-2xl hover:shadow-rose-500/10 hover:-translate-y-1 transition-all duration-500 relative">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-rose-500 to-pink-500"></div>
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-b from-rose-50/30 to-white">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-rose-50 rounded-2xl border border-rose-100 flex items-center justify-center text-rose-600 shadow-sm group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900 text-xl">الأمان</h3>
                        <p class="text-xs font-bold text-gray-400 mt-1">حماية النظام والصيانة</p>
                    </div>
                </div>
            </div>
            <div class="p-8 space-y-4">
                <div class="flex items-center justify-between p-5 rounded-3xl bg-gray-50 border-2 border-gray-100 transition-all hover:border-primary-200 hover:bg-white hover:shadow-lg group/item">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 group-hover/item:text-primary-600 group-hover/item:border-primary-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-black text-gray-900 text-sm">التسجيل متاح</h4>
                            <p class="text-[10px] font-bold text-gray-400 group-hover/item:text-primary-500 transition-colors">السماح للمستخدمين الجدد</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="allow_registration" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-100 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-5 rounded-3xl bg-gray-50 border-2 border-gray-100 transition-all hover:border-rose-200 hover:bg-white hover:shadow-lg group/item">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 group-hover/item:text-rose-600 group-hover/item:border-rose-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-black text-gray-900 text-sm">وضع الصيانة</h4>
                            <p class="text-[10px] font-bold text-gray-400 group-hover/item:text-rose-500 transition-colors">تعطيل الموقع مؤقتاً</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="maintenance_mode" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-rose-100 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-5 rounded-3xl bg-gray-50 border-2 border-gray-100 transition-all hover:border-indigo-200 hover:bg-white hover:shadow-lg group/item">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 group-hover/item:text-indigo-600 group-hover/item:border-indigo-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-black text-gray-900 text-sm">Debug Mode</h4>
                            <p class="text-[10px] font-bold text-gray-400 group-hover/item:text-indigo-500 transition-colors">للمطورين فقط</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="debug_mode" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-100 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Social Media Card -->
        <div class="bg-white rounded-[2rem] border border-gray-200 shadow-sm overflow-hidden group hover:shadow-2xl hover:shadow-indigo-500/10 hover:-translate-y-1 transition-all duration-300 md:col-span-2 lg:col-span-1 relative">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-b from-indigo-50/30 to-white">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-50 rounded-2xl border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900 text-xl">التواصل الاجتماعي</h3>
                        <p class="text-xs font-bold text-gray-400 mt-1">روابط المنصات الخارجية</p>
                    </div>
                </div>
            </div>
            <div class="p-8 space-y-5">
                <div class="relative group/input">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"></path>
                        </svg>
                    </div>
                    <input type="url" wire:model="facebook_url" class="w-full rounded-2xl border-2 border-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 font-bold transition-all pr-12 hover:bg-blue-50/10 focus:bg-white placeholder-gray-300 p-4" placeholder="Facebook URL" dir="ltr">
                </div>

                <div class="relative group/input">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-sky-500 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"></path>
                        </svg>
                    </div>
                    <input type="url" wire:model="twitter_url" class="w-full rounded-2xl border-2 border-gray-100 focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 font-bold transition-all pr-12 hover:bg-sky-50/10 focus:bg-white placeholder-gray-300 p-4" placeholder="Twitter URL" dir="ltr">
                </div>

                <div class="relative group/input">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-pink-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                        </svg>
                    </div>
                    <input type="url" wire:model="instagram_url" class="w-full rounded-2xl border-2 border-gray-100 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 font-bold transition-all pr-12 hover:bg-pink-50/10 focus:bg-white placeholder-gray-300 p-4" placeholder="Instagram URL" dir="ltr">
                </div>

                <div class="relative group/input">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-red-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
                        </svg>
                    </div>
                    <input type="url" wire:model="youtube_url" class="w-full rounded-2xl border-2 border-gray-100 focus:border-red-500 focus:ring-4 focus:ring-red-500/10 font-bold transition-all pr-12 hover:bg-red-50/10 focus:bg-white placeholder-gray-300 p-4" placeholder="YouTube URL" dir="ltr">
                </div>
            </div>
        </div>

</div>
</div>

</form>
</div>