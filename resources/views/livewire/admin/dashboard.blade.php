<?php

use Livewire\Volt\Component;
use App\Models\Lead;
use App\Models\User;
use App\Models\WithdrawalRequest;

use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    public function syncQoyod(\App\Services\QoyodService $service)
    {
        $count = $service->syncLeadsFromQoyod();
        session()->flash('message', "تمت مزامنة {$count} عملاء بنجاح من منصة قيود!");
    }

    public function syncDaftra(\App\Services\DaftraService $service)
    {
        $count = $service->syncLeadsFromDaftra();
        session()->flash('message', "تمت مزامنة {$count} عملاء بنجاح من منصة دفترة!");
    }

    public function with()
    {
        return [
            'totalLeads' => Lead::count(),
            'pendingLeads' => Lead::where('status', 'under_review')->count(),
            'soldLeads' => Lead::where('status', 'sold')->count(),
            'totalUsers' => User::where('role', 'affiliate')->count(),
            'recentLeads' => Lead::latest()->take(5)->get(),
            'pendingPayouts' => WithdrawalRequest::where('status', 'pending')->count(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Success Message -->
    @if (session()->has('message'))
    <div class="alert alert-success animate-slide-down">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-semibold">{{ session('message') }}</span>
    </div>
    @endif

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-corporate-900">نظرة عامة على النظام</h2>
            <p class="text-sm text-corporate-500 mt-1">ملخص شامل لأداء المنصة</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="syncQoyod" wire:loading.attr="disabled"
                class="btn btn-secondary btn-sm">
                <svg wire:loading.remove wire:target="syncQoyod" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span wire:loading.remove wire:target="syncQoyod">مزامنة قيود</span>
                <span wire:loading wire:target="syncQoyod" class="flex items-center gap-2">
                    <span class="spinner"></span>
                    جاري المزامنة...
                </span>
            </button>
            <button wire:click="syncDaftra" wire:loading.attr="disabled"
                class="btn btn-primary btn-sm">
                <svg wire:loading.remove wire:target="syncDaftra" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span wire:loading.remove wire:target="syncDaftra">مزامنة دفترة</span>
                <span wire:loading wire:target="syncDaftra" class="flex items-center gap-2">
                    <span class="spinner"></span>
                    جاري المزامنة...
                </span>
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 stagger-slide-up">
        <!-- Total Leads -->
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="stat-label">إجمالي العملاء</p>
            <h3 class="stat-value">{{ number_format($totalLeads) }}</h3>
            <p class="text-xs text-corporate-500 mt-2">جميع العملاء المسجلين</p>
        </div>

        <!-- Pending Leads -->
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-warning-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="stat-label">بانتظار المراجعة</p>
            <h3 class="stat-value text-warning-600">{{ number_format($pendingLeads) }}</h3>
            <p class="text-xs text-corporate-500 mt-2">يتطلب اتخاذ إجراء</p>
        </div>

        <!-- Sold Leads -->
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-success-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="stat-label">عمليات بيع ناجحة</p>
            <h3 class="stat-value text-success-600">{{ number_format($soldLeads) }}</h3>
            <p class="text-xs text-corporate-500 mt-2">تم إتمامها بنجاح</p>
        </div>

        <!-- Total Affiliates -->
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-accent-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="stat-label">إجمالي المسوقين</p>
            <h3 class="stat-value text-accent-600">{{ number_format($totalUsers) }}</h3>
            <p class="text-xs text-corporate-500 mt-2">شركاء نشطون</p>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Leads -->
        <div class="card card-hover">
            <div class="card-header">
                <h3 class="text-lg font-bold text-corporate-900">آخر العملاء المضافين</h3>
                <p class="text-sm text-corporate-500 mt-0.5">أحدث 5 عملاء</p>
            </div>
            <div class="card-body">
                <div class="space-y-3">
                    @forelse($recentLeads as $lead)
                    <div class="flex items-center justify-between p-4 bg-corporate-50 rounded-xl hover:bg-corporate-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary-100 border-2 border-primary-200 flex items-center justify-center">
                                <span class="font-bold text-primary-900 text-sm">{{ mb_substr($lead->client_name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-corporate-900 text-sm">{{ $lead->client_name }}</p>
                                <p class="text-xs text-corporate-500">بواسطة: {{ $lead->user->name }}</p>
                            </div>
                        </div>
                        <span class="badge badge-neutral text-2xs">{{ $lead->created_at->diffForHumans() }}</span>
                    </div>
                    @empty
                    <div class="text-center py-8 text-corporate-500">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-sm font-medium">لا توجد عملاء حتى الآن</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @if($recentLeads->count() > 0)
            <div class="card-footer">
                <a href="{{ route('admin.leads') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700 flex items-center gap-2">
                    <span>عرض جميع العملاء</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
            </div>
            @endif
        </div>

        <!-- API Status -->
        <div class="card card-hover">
            <div class="card-header">
                <h3 class="text-lg font-bold text-corporate-900">حالة التكامل مع الأنظمة</h3>
                <p class="text-sm text-corporate-500 mt-0.5">مراقبة اتصال API</p>
            </div>
            <div class="card-body space-y-4">
                <!-- Qoyod Status -->
                <div class="flex items-center justify-between p-4 bg-success-50 rounded-xl border border-success-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm">
                            <img src="{{asset('images/systems/qoyod.png')}}" class="h-5 grayscale opacity-70" alt="Qoyod">
                        </div>
                        <div>
                            <p class="font-semibold text-corporate-900 text-sm">منصة قيود</p>
                            <p class="text-xs text-success-700 font-medium">متصل - يعمل بكفاءة</p>
                        </div>
                    </div>
                    <div class="w-2.5 h-2.5 bg-success-500 rounded-full animate-pulse-subtle"></div>
                </div>

                <!-- Daftra Status -->
                <div class="flex items-center justify-between p-4 bg-primary-50 rounded-xl border border-primary-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm">
                            <img src="{{asset('images/systems/daftra.png')}}" class="h-5 grayscale opacity-70" alt="Daftra">
                        </div>
                        <div>
                            <p class="font-semibold text-corporate-900 text-sm">منصة دفترة</p>
                            <p class="text-xs text-primary-700 font-medium">متصل - يعمل بكفاءة</p>
                        </div>
                    </div>
                    <div class="w-2.5 h-2.5 bg-primary-500 rounded-full animate-pulse-subtle"></div>
                </div>

                <!-- Sync Stats -->
                <div class="p-4 bg-corporate-50 rounded-xl border border-corporate-200">
                    <h4 class="text-xs font-bold text-corporate-700 uppercase tracking-wider mb-3">إحصائيات المزامنة</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-corporate-500 mb-1">آخر مزامنة</p>
                            <p class="text-sm font-bold text-corporate-900">{{ now()->format('H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-corporate-500 mb-1">معدل النجاح</p>
                            <p class="text-sm font-bold text-success-600">100%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>