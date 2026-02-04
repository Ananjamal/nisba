<x-app-layout>
    <x-slot name="header">
        <div class="space-y-4 px-2">
            <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] border border-slate-100/50 shadow-sm">{{ __('إدارة الحساب') }}</span>
            <h2 class="section-title !text-3xl !mb-0">
                {{ __('الملف الشخصي') }}
            </h2>
            <p class="section-subtitle !text-sm">{{ __('تحكم في بياناتك الشخصية وإعدادات الأمان الخاصة بك.') }}</p>
        </div>
    </x-slot>

    <div class="py-12 space-y-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
            <div class="premium-card p-10 !bg-white">
                <div class="max-w-2xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="premium-card p-10 !bg-white">
                <div class="max-w-2xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="premium-card p-10 !bg-white border-rose-50/50">
                <div class="max-w-2xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>