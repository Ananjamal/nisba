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

                <!-- Quick Actions -->
                <a href="#" class="btn-secondary !py-2.5 !px-5 group hover:scale-105 transition-transform duration-300">
                    <svg class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('المدفوعات') }}</span>
                </a>

                <a href="#" class="btn-secondary !py-2.5 !px-5 group hover:scale-105 transition-transform duration-300">
                    <svg class="w-4 h-4 group-hover:-rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('الطلبات') }}</span>
                </a>
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
            <div class="gradient-card bg-gradient-to-br from-deep-blue-800 to-deep-blue-900 overflow-hidden relative">
                <div class="absolute top-0 right-0 p-10 opacity-10">
                    <svg class="w-32 h-32 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
                <livewire:affiliate.referral-links />
            </div>

            <!-- المحفظة وسجل الصرف - Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- المحفظة -->
                <div class="modern-card">
                    <livewire:affiliate.wallet />
                </div>

                <!-- سجل الصرف -->
                <div class="modern-card p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="heading-sm">{{ __('سجل الصرف') }}</h3>
                        <span class="badge-primary">{{ __('قريباً') }}</span>
                    </div>
                    <div class="flex flex-col items-center justify-center py-12">
                        <div class="w-20 h-20 bg-deep-blue-100 rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-deep-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-body text-center">{{ __('سجل الصرف قيد التطوير') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>