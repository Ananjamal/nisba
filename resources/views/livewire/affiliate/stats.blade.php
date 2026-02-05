<?php

use Livewire\Volt\Component;

new class extends Component {
    public function with()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        return [
            'stats' => $user->stats ?? (object)[
                'clicks_count' => 0,
                'active_clients_count' => 0,
                'total_contracts_value' => 0,
                'pending_commissions' => 0
            ],
            'recentClicks' => \App\Models\Click::where('user_id', $user->id)->with('referralLink')->latest()->take(10)->get(),
        ];
    }
}; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Total Clicks -->
    <div class="stat-modern group">
        <div class="flex justify-between items-start mb-8">
            <span class="stat-label-modern">{{ __('إجمالي النقرات') }}</span>
            <div class="stat-icon bg-gradient-to-br from-cyber-600 to-cyber-500">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </div>
        </div>
        <div class="relative z-10">
            <h4 class="stat-value-modern">{{ number_format($stats->clicks_count ?? 0) }}</h4>
            <span class="text-xs font-semibold text-deep-blue-400 mt-2 block">{{ __('تفاعل العملاء مع روابطك') }}</span>
        </div>
    </div>

    <!-- Active Clients -->
    <div class="stat-modern group">
        <div class="flex justify-between items-start mb-8">
            <span class="stat-label-modern">{{ __('العملاء المشتركين') }}</span>
            <div class="stat-icon bg-gradient-to-br from-neon-purple-600 to-neon-purple-500">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>
        <div class="relative z-10">
            <h4 class="stat-value-modern">{{ number_format($stats->active_clients_count ?? 0) }}</h4>
            <span class="text-xs font-semibold text-deep-blue-400 mt-2 block">{{ __('الاشتراكات النشطة حالياً') }}</span>
        </div>
    </div>

    <!-- Total Contracts -->
    <div class="stat-modern group">
        <div class="flex justify-between items-start mb-8">
            <span class="stat-label-modern">{{ __('إجمالي قيمة العقود') }}</span>
            <div class="stat-icon bg-gradient-to-br from-amber-glow-500 to-amber-glow-400">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div class="relative z-10">
            <div class="flex items-baseline gap-2">
                <h4 class="stat-value-modern">{{ number_format($stats->total_contracts_value ?? 0, 0) }}</h4>
                <span class="text-lg font-bold text-deep-blue-400 uppercase">ريال</span>
            </div>
            <span class="text-xs font-semibold text-deep-blue-400 mt-2 block">{{ __('القيمة الصافية للعقود المحققة') }}</span>
        </div>
    </div>
</div>