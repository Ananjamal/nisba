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

    public $modalTitle = '';
    public $modalType = '';
    public $showModal = false;

    public function openModal($type, $title)
    {
        $this->modalType = $type;
        $this->modalTitle = $title;
        $this->showModal = true;
        $this->dispatch('open-modal', 'stats-details');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->dispatch('close-modal', 'stats-details');
    }

    public function with()
    {
        return [
            'totalLeads' => Lead::count(),
            'pendingLeads' => Lead::where('status', Lead::STATUS_NEW)->count(),
            'soldLeads' => Lead::where('status', Lead::STATUS_SOLD)->count(),
            'totalUsers' => User::where('role', 'affiliate')->count(),
            'recentLeads' => Lead::latest()->take(5)->get(),
            'pendingPayouts' => WithdrawalRequest::where('status', 'pending')->count(),
            'leadsByStatus' => $leadsByStatus = Lead::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'leadsByStatusProcessed' => collect($leadsByStatus)->map(function ($count) use ($leadsByStatus) {
                $maxCount = collect($leadsByStatus)->max() ?: 1;
                return [
                    'count' => $count,
                    'percentage' => ($count / $maxCount) * 100
                ];
            }),
            'ranksByCount' => User::where('role', 'affiliate')
                ->selectRaw('`rank`, count(*) as count')
                ->groupBy('rank')
                ->pluck('count', 'rank'),
            'topSectors' => Lead::selectRaw('sector, count(*) as count')
                ->whereNotNull('sector')
                ->groupBy('sector')
                ->orderByDesc('count')
                ->take(5)
                ->get(),
            'modalData' => $this->getModalData(),
        ];
    }

    private function getModalData()
    {
        if (!$this->showModal) return collect();

        return match ($this->modalType) {
            'total_leads' => Lead::latest()->take(10)->get(),
            'pending_leads' => Lead::where('status', Lead::STATUS_NEW)->latest()->take(10)->get(),
            'sold_leads' => Lead::where('status', Lead::STATUS_SOLD)->latest()->take(10)->get(),
            'total_users' => User::where('role', 'affiliate')->latest()->take(10)->get(),
            default => collect(),
        };
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
            <h2 class="text-2xl font-bold text-primary-900">نظرة عامة على النظام</h2>
            <p class="text-sm text-primary-500 mt-1">ملخص شامل لأداء المنصة</p>
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
        @can('view sales widget')
        <div wire:click="openModal('total_leads', 'إجمالي العملاء')"
            wire:loading.class="opacity-70"
            class="group relative bg-white p-6 rounded-[2rem] shadow-luxury border border-slate-100 hover:shadow-luxury-lg hover:-translate-y-2 transition-all duration-500 cursor-pointer overflow-hidden">
            <!-- Accent Line -->
            <div class="absolute top-0 right-0 w-32 h-1 bg-gradient-to-l from-primary-500 to-primary-300 rounded-bl-full opacity-50 group-hover:w-full transition-all duration-700"></div>

            <div class="relative flex items-center justify-between mb-6">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="p-2 bg-slate-50 rounded-xl group-hover:bg-primary-500 group-hover:text-white transition-colors duration-300">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>

            <div class="relative">
                <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest mb-1">إجمالي العملاء</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="stat-value text-3xl font-black text-slate-800">{{ number_format($totalLeads) }}</h3>
                    <span class="text-[10px] font-bold text-primary-500 bg-primary-50 px-2 py-0.5 rounded-lg">+ المجموع</span>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></div>
                    <p class="text-[11px] font-bold text-slate-500">جميع العملاء المسجلين</p>
                </div>
            </div>
        </div>

        <!-- Pending Leads -->
        <div wire:click="openModal('pending_leads', 'بانتظار المراجعة')"
            wire:loading.class="opacity-70"
            class="group relative bg-white p-6 rounded-[2rem] shadow-luxury border border-slate-100 hover:shadow-luxury-lg hover:-translate-y-2 transition-all duration-500 cursor-pointer overflow-hidden">
            <!-- Accent Line -->
            <div class="absolute top-0 right-0 w-32 h-1 bg-gradient-to-l from-warning-500 to-warning-300 rounded-bl-full opacity-50 group-hover:w-full transition-all duration-700"></div>

            <div class="relative flex items-center justify-between mb-6">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-warning-50 to-warning-100 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-8 h-8 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="p-2 bg-slate-50 rounded-xl group-hover:bg-warning-500 group-hover:text-white transition-colors duration-300">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>

            <div class="relative">
                <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest mb-1">بانتظار المراجعة</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="stat-value text-3xl font-black text-slate-800">{{ number_format($pendingLeads) }}</h3>
                    <span class="text-[10px] font-bold text-warning-500 bg-warning-50 px-2 py-0.5 rounded-lg">عاجل</span>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-warning-500 animate-ping"></div>
                    <p class="text-[11px] font-bold text-slate-500">يتطلب اتخاذ إجراء</p>
                </div>
            </div>
        </div>

        <!-- Sold Leads -->
        <div wire:click="openModal('sold_leads', 'عمليات بيع ناجحة')"
            wire:loading.class="opacity-70"
            class="group relative bg-white p-6 rounded-[2rem] shadow-luxury border border-slate-100 hover:shadow-luxury-lg hover:-translate-y-2 transition-all duration-500 cursor-pointer overflow-hidden">
            <!-- Accent Line -->
            <div class="absolute top-0 right-0 w-32 h-1 bg-gradient-to-l from-success-500 to-success-300 rounded-bl-full opacity-50 group-hover:w-full transition-all duration-700"></div>

            <div class="relative flex items-center justify-between mb-6">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-success-50 to-success-100 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-8 h-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="p-2 bg-slate-50 rounded-xl group-hover:bg-success-500 group-hover:text-white transition-colors duration-300">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>

            <div class="relative">
                <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest mb-1">عمليات بيع ناجحة</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="stat-value text-3xl font-black text-slate-800">{{ number_format($soldLeads) }}</h3>
                    <span class="text-[10px] font-bold text-success-500 bg-success-50 px-2 py-0.5 rounded-lg">مكتمل</span>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-success-500"></div>
                    <p class="text-[11px] font-bold text-slate-500">تم إتمامها بنجاح</p>
                </div>
            </div>
        </div>
        @endcan

        <!-- Total Affiliates -->
        @can('view marketers widget')
        <div wire:click="openModal('total_users', 'إجمالي المسوقين')"
            wire:loading.class="opacity-70"
            class="group relative bg-white p-6 rounded-[2rem] shadow-luxury border border-slate-100 hover:shadow-luxury-lg hover:-translate-y-2 transition-all duration-500 cursor-pointer overflow-hidden">
            <!-- Accent Line -->
            <div class="absolute top-0 right-0 w-32 h-1 bg-gradient-to-l from-accent-500 to-accent-300 rounded-bl-full opacity-50 group-hover:w-full transition-all duration-700"></div>

            <div class="relative flex items-center justify-between mb-6">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-accent-50 to-accent-100 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-8 h-8 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="p-2 bg-slate-50 rounded-xl group-hover:bg-accent-500 group-hover:text-white transition-colors duration-300">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>

            <div class="relative">
                <p class="text-[12px] font-bold text-slate-400 uppercase tracking-widest mb-1">إجمالي المسوقين</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="stat-value text-3xl font-black text-slate-800">{{ number_format($totalUsers) }}</h3>
                    <span class="text-[10px] font-bold text-accent-500 bg-accent-50 px-2 py-0.5 rounded-lg">شركاء</span>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-accent-500"></div>
                    <p class="text-[11px] font-bold text-slate-500">شركاء نشطون</p>
                </div>
            </div>
        </div>
        @endcan
    </div>

    <!-- Performance Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        @can('view sales widget')
        <livewire:components.dashboard-chart
            chartId="sales-trend"
            type="sales"
            period="month"
            title="اتجاهات المبيعات"
            :key="'sales-chart-'.now()->timestamp" />
        @endcan

        @can('view performance widget')
        <livewire:components.dashboard-chart
            chartId="revenue-performance"
            type="revenue"
            period="month"
            title="أداء الإيرادات"
            :key="'revenue-chart-'.now()->timestamp" />
        @endcan
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- New Widget: Leads by Status -->
        @can('view sales widget')
        <div class="card card-hover">
            <div class="card-header">
                <h3 class="text-lg font-bold text-primary-900">حالات العملاء</h3>
                <p class="text-sm text-primary-500 mt-0.5">توزيع العملاء حسب المحطات</p>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    @foreach($leadsByStatusProcessed as $status => $data)
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-primary-700">{{ \App\Models\Lead::statusLabel($status) }}</span>
                            <span class="text-sm font-bold text-primary-900">{{ $data['count'] }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full transition-all duration-500 {{ str_replace('bg-', 'bg-', \App\Models\Lead::statusBadgeClass($status)) }}" style="width: {{ $data['percentage'] }}%"></div>
                        </div>
                    </div>
                    @endforeach

                    @if($leadsByStatusProcessed->isEmpty())
                    <p class="text-center text-sm text-primary-400 py-4">لا توجد بيانات متاحة</p>
                    @endif
                </div>
            </div>
        </div>
        @endcan

        <!-- New Widget: Marketers by Rank -->
        @can('view marketers widget')
        <div class="card card-hover">
            <div class="card-header flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-primary-900">رتب المسوقين</h3>
                    <p class="text-sm text-primary-500 mt-0.5">توزيع الشركاء حسب الرتبة</p>
                </div>
                <a href="{{ route('admin.marketers.ranks') }}" class="text-xs font-bold text-primary-600 hover:underline">إدارة الرتب</a>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl bg-orange-50 border border-orange-100 text-center">
                        <div class="text-2xl mb-1">🥉</div>
                        <div class="text-xl font-black text-orange-700 leading-tight">{{ $ranksByCount['bronze'] ?? 0 }}</div>
                        <div class="text-[10px] font-bold text-orange-600 uppercase tracking-wider mt-1">برونزي</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 text-center">
                        <div class="text-2xl mb-1">🥈</div>
                        <div class="text-xl font-black text-gray-700 leading-tight">{{ $ranksByCount['silver'] ?? 0 }}</div>
                        <div class="text-[10px] font-bold text-gray-600 uppercase tracking-wider mt-1">فضي</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-yellow-50 border border-yellow-100 text-center">
                        <div class="text-2xl mb-1">🥇</div>
                        <div class="text-xl font-black text-yellow-700 leading-tight">{{ $ranksByCount['gold'] ?? 0 }}</div>
                        <div class="text-[10px] font-bold text-yellow-600 uppercase tracking-wider mt-1">ذهبي</div>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    @php
                    $rankLabels = ['bronze' => 'برونزي', 'silver' => 'فضي', 'gold' => 'ذهبي'];
                    $rankColors = ['bronze' => 'bg-orange-500', 'silver' => 'bg-gray-500', 'gold' => 'bg-yellow-500'];
                    $totalMarketers = array_sum($ranksByCount->toArray()) ?: 1;
                    @endphp

                    @foreach(['gold', 'silver', 'bronze'] as $rank)
                    @php $count = $ranksByCount[$rank] ?? 0; @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-xs font-bold text-corporate-700">{{ $rankLabels[$rank] }}</span>
                            <span class="text-xs font-black text-corporate-900">{{ round(($count / $totalMarketers) * 100) }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="{{ $rankColors[$rank] }} h-2 rounded-full transition-all duration-700" style="width: {{ ($count / $totalMarketers) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endcan

        <!-- New Widget: Top Sectors -->
        @can('view sectors widget')
        <div class="card card-hover">
            <div class="card-header">
                <h3 class="text-lg font-bold text-primary-900">أبزر القطاعات</h3>
                <p class="text-sm text-primary-500 mt-0.5">أكثر القطاعات نشاطاً</p>
            </div>
            <div class="card-body">
                <div class="space-y-3">
                    @foreach($topSectors as $sectorData)
                    <div class="flex items-center justify-between p-3 bg-primary-50 rounded-xl hover:bg-primary-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm font-bold text-primary-900">
                                {{ $loop->iteration }}
                            </div>
                            <span class="font-semibold text-primary-900">{{ $sectorData->sector }}</span>
                        </div>
                        <span class="badge badge-primary">{{ $sectorData->count }} عميل</span>
                    </div>
                    @endforeach

                    @if($topSectors->isEmpty())
                    <p class="text-center text-sm text-primary-400 py-4">لا توجد بيانات متاحة</p>
                    @endif
                </div>
            </div>
        </div>
        @endcan

        <!-- Recent Leads -->
        @can('view recent leads widget')
        <div class="card card-hover">
            <div class="card-header">
                <h3 class="text-lg font-bold text-primary-900">العملاء المضافون مؤخراً</h3>
                <p class="text-sm text-primary-500 mt-0.5">آخر 5 عملاء في النظام</p>
            </div>
            <div class="card-body">
                <div class="space-y-3">
                    @forelse($recentLeads as $lead)
                    <div class="flex items-center justify-between p-4 bg-primary-50 rounded-xl hover:bg-primary-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary-100 border-2 border-primary-200 flex items-center justify-center">
                                <span class="font-bold text-primary-900 text-sm">{{ mb_substr($lead->client_name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-primary-900 text-sm">{{ $lead->client_name }}</p>
                                <p class="text-xs text-primary-500">بواسطة: {{ $lead->user->name }}</p>
                            </div>
                        </div>
                        <span class="badge badge-neutral text-2xs">{{ $lead->created_at->diffForHumans() }}</span>
                    </div>
                    @empty
                    <div class="text-center py-8 text-primary-500">
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
        @endcan

        <!-- API Status -->
        <div class="card card-hover">
            <div class="card-header">
                <h3 class="text-lg font-bold text-primary-900">حالة التكامل مع الأنظمة</h3>
                <p class="text-sm text-primary-500 mt-0.5">مراقبة اتصال API</p>
            </div>
            <div class="card-body space-y-4">
                <!-- Qoyod Status -->
                <div class="flex items-center justify-between p-4 bg-success-50 rounded-xl border border-success-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm">
                            <img src="{{asset('images/systems/qoyod.png')}}" class="h-5 grayscale opacity-70" alt="Qoyod">
                        </div>
                        <div>
                            <p class="font-semibold text-primary-900 text-sm">منصة قيود</p>
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
                            <p class="font-semibold text-primary-900 text-sm">منصة دفترة</p>
                            <p class="text-xs text-primary-700 font-medium">متصل - يعمل بكفاءة</p>
                        </div>
                    </div>
                    <div class="w-2.5 h-2.5 bg-primary-500 rounded-full animate-pulse-subtle"></div>
                </div>

                <!-- Sync Stats -->
                <div class="p-4 bg-primary-50 rounded-xl border border-primary-200">
                    <h4 class="text-xs font-bold text-primary-700 uppercase tracking-wider mb-3">إحصائيات المزامنة</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-primary-500 mb-1">آخر مزامنة</p>
                            <p class="text-sm font-bold text-primary-900">{{ now()->format('H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-primary-500 mb-1">معدل النجاح</p>
                            <p class="text-sm font-bold text-success-600">100%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Premium Details Modal -->
        <!-- Premium Details Modal (Light Theme Optimized) -->
        <x-modal name="stats-details" :show="$showModal" maxWidth="lg">

            <div dir="rtl"
                class="relative overflow-hidden rounded-2xl
bg-white border border-gray-200
shadow-[0_10px_40px_rgba(0,0,0,0.08)]
max-w-lg mx-auto">

                <!-- Light Glow -->
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-primary-100 rounded-full blur-3xl opacity-40"></div>
                <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-indigo-100 rounded-full blur-3xl opacity-40"></div>


                <!-- Header -->
                <div class="relative px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-primary-50 to-indigo-50">

                    <div class="flex items-center justify-between">

                        <div class="flex items-center gap-3">

                            <div class="w-10 h-10 rounded-xl
                            bg-primary-500
                            text-white flex items-center justify-center
                            shadow-sm">

                                <svg class="w-5 h-5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>

                            </div>

                            <div>

                                <h3 class="text-base font-bold text-gray-800">
                                    {{ $modalTitle }}
                                </h3>

                                <div class="flex items-center gap-1 mt-1">
                                    <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></div>
                                    <span class="text-xs text-gray-400 font-medium">
                                        بيانات حديثة
                                    </span>
                                </div>

                            </div>

                        </div>


                        <button wire:click="closeModal"
                            class="w-8 h-8 rounded-lg hover:bg-red-50
                           flex items-center justify-center transition">

                            <svg class="w-4 h-4 text-gray-400 hover:text-red-500"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>

                        </button>

                    </div>

                </div>



                <!-- Body -->
                <div class="relative px-5 py-4 space-y-3 max-h-[45vh] overflow-y-auto">

                    @forelse($modalData as $item)

                    <div class="p-3 rounded-xl
                    bg-gray-50 border border-gray-100
                    hover:bg-white hover:border-primary-200
                    hover:shadow-sm
                    transition-all duration-200">

                        <div class="flex items-center justify-between">


                            <div class="flex items-center gap-3">

                                <div class="w-9 h-9 rounded-lg
                                bg-primary-100 text-primary-600
                                font-bold text-sm
                                flex items-center justify-center">

                                    {{ mb_substr(
                            $modalType === 'total_users'
                            ? $item->name
                            : $item->client_name ,0,1) }}

                                </div>


                                <div>

                                    <div class="text-sm font-semibold text-gray-700">

                                        {{ $modalType === 'total_users'
                            ? $item->name
                            : $item->client_name }}

                                    </div>


                                    <div class="text-xs text-gray-400">

                                        @if($modalType === 'total_users')

                                        {{ $item->created_at->format('Y-m-d') }}

                                        @else

                                        {{ $item->user->name ?? 'غير معروف' }}

                                        @endif

                                    </div>

                                </div>

                            </div>



                            <!-- Status -->
                            @if($modalType === 'total_users')

                            <div class="px-3 py-1 rounded-lg
                            bg-primary-50 text-primary-600
                            text-xs font-bold">

                                {{ $item->rank ?? 'Bronze' }}

                            </div>

                            @else
                            <div class="px-3 py-1 rounded-lg text-xs font-bold border {{ \App\Models\Lead::statusBadgeClass($item->status) }}">
                                {{ \App\Models\Lead::statusLabel($item->status) }}
                            </div>
                            @endif


                        </div>

                    </div>

                    @empty

                    <div class="text-center py-10 text-gray-400 text-sm">
                        لا توجد بيانات
                    </div>

                    @endforelse

                </div>



                <!-- Footer -->
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">

                    @php
                    $route = match($modalType) {
                    'total_users' => route('admin.affiliates'),
                    'pending_leads' => route('admin.leads', ['status' => \App\Models\Lead::STATUS_NEW]),
                    'sold_leads' => route('admin.leads', ['status' => \App\Models\Lead::STATUS_SOLD]),
                    default => route('admin.leads'),
                    };
                    @endphp


                    <a href="{{ $route }}"
                        class="w-full py-3 rounded-xl
                  bg-primary-500 hover:bg-primary-600
                  text-white text-sm font-bold
                  flex items-center justify-center gap-2
                  shadow-sm hover:shadow-md
                  transition">

                        عرض التفاصيل

                        <svg class="w-4 h-4"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>

                    </a>

                </div>


            </div>

        </x-modal>

    </div>
</div>