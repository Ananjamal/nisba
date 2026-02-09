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
        ];
    }
}; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- إجمالي الأرباح -->
    <div class="stat-card">
        <div class="stat-icon bg-primary-100">
            <svg class="w-6 h-6 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="stat-content">
            <p class="stat-label">إجمالي الأرباح</p>
            <h3 class="stat-value">{{ number_format($stats->total_contracts_value ?? 0, 2) }} <span class="text-sm font-normal">ريس</span></h3>
            <p class="stat-description">الأرباح المحققة حتى الآن</p>
        </div>
    </div>

    <!-- العمولة المعلقة -->
    <div class="stat-card">
        <div class="stat-icon bg-primary-50">
            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="stat-content">
            <p class="stat-label">العمولة المعلقة</p>
            <h3 class="stat-value">{{ number_format($stats->pending_commissions ?? 0, 2) }} <span class="text-sm font-normal">ريس</span></h3>
            <p class="stat-description">بانتظار الموافقة والصرف</p>
        </div>
    </div>

    <!-- العملاء المشتركين -->
    <div class="stat-card">
        <div class="stat-icon bg-primary-100">
            <svg class="w-6 h-6 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </div>
        <div class="stat-content">
            <p class="stat-label">العملاء المشتركين</p>
            <h3 class="stat-value">{{ number_format($stats->active_clients_count ?? 0) }}</h3>
            <p class="stat-description">عدد العملاء النشطين</p>
        </div>
    </div>

    <!-- إجمالي النقرات -->
    <div class="stat-card">
        <div class="stat-icon bg-primary-50">
            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
        </div>
        <div class="stat-content">
            <p class="stat-label">إجمالي النقرات</p>
            <h3 class="stat-value">{{ number_format($stats->clicks_count ?? 0) }}</h3>
            <p class="stat-description">عدد مرات زيارة الروابط</p>
        </div>
    </div>
</div>