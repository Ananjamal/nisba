<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="heading-lg flex items-center gap-3">
                    {{ __('مرحباً,') }} <span class="bg-gradient-to-r from-cyber-600 to-cyber-500 bg-clip-text text-transparent">{{ auth()->user()->name }}</span>
                    <span class="animate-bounce">👋</span>
                </h2>
                <p class="text-body mt-2">{{ __('إليك ملخص أداء نشاطك التسويقي اليوم.') }}</p>
            </div>
            <!-- Action buttons -->
            <div class="flex items-center gap-4 flex-wrap">
                <div class="badge-success !px-5 !py-2.5 shadow-soft animate-pulse-slow">
                    <span class="w-2 h-2 bg-electric-500 rounded-full animate-pulse"></span>
                    <span class="text-sm font-bold">{{ __('متصل الآن') }}</span>
                </div>

            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="space-y-10">

            <!-- Stats Row -->
            <div class="relative">
                <livewire:affiliate.stats />
            </div>

            <!-- إدارة المبيعات (Leads) - Full Width -->
            <div class="modern-card overflow-hidden">
                <livewire:affiliate.leads />
            </div>

            <!-- قسم الروابط - Full Width -->
            <div class="mb-12">
                <livewire:affiliate.referral-links />
            </div>

            <!-- المحفظة وسجل الصرف - Grid -->
            <!-- المحفظة وسجل الصرف -->
            <div class="space-y-8">
                <div class="modern-card">
                    <livewire:affiliate.wallet />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>