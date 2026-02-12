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
        <!-- Sales Widgets -->
        @can('view sales widget')
        <a href="{{ route('admin.leads') }}" class="block">
            <div class="stat-card hover-lift transition-all duration-300 hover:shadow-lg group cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center group-hover:bg-primary-600 transition-colors duration-300">
                        <svg class="w-6 h-6 text-primary-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="bg-primary-50 rounded-full p-1 group-hover:bg-primary-100 transition-colors">
                        <svg class="w-4 h-4 text-primary-400 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
                <p class="stat-label group-hover:text-primary-700 transition-colors">إجمالي العملاء</p>
                <h3 class="stat-value group-hover:text-primary-700 transition-colors">{{ number_format($totalLeads) }}</h3>
                <p class="text-xs text-primary-500 mt-2">جميع العملاء المسجلين</p>
            </div>
        </a>

        <a href="{{ route('admin.leads', ['status' => 'under_review']) }}" class="block">
            <div class="stat-card hover-lift transition-all duration-300 hover:shadow-lg group cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-warning-100 flex items-center justify-center group-hover:bg-warning-500 transition-colors duration-300">
                        <svg class="w-6 h-6 text-warning-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="bg-warning-50 rounded-full p-1 group-hover:bg-warning-100 transition-colors">
                        <svg class="w-4 h-4 text-warning-400 group-hover:text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
                <p class="stat-label group-hover:text-warning-700 transition-colors">بانتظار المراجعة</p>
                <h3 class="stat-value text-warning-600 group-hover:text-warning-700 transition-colors">{{ number_format($pendingLeads) }}</h3>
                <p class="text-xs text-primary-500 mt-2">يتطلب اتخاذ إجراء</p>
            </div>
        </a>

        <a href="{{ route('admin.leads', ['status' => 'sold']) }}" class="block">
            <div class="stat-card hover-lift transition-all duration-300 hover:shadow-lg group cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-success-100 flex items-center justify-center group-hover:bg-success-600 transition-colors duration-300">
                        <svg class="w-6 h-6 text-success-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="bg-success-50 rounded-full p-1 group-hover:bg-success-100 transition-colors">
                        <svg class="w-4 h-4 text-success-400 group-hover:text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
                <p class="stat-label group-hover:text-success-700 transition-colors">عمليات بيع ناجحة</p>
                <h3 class="stat-value text-success-600 group-hover:text-success-700 transition-colors">{{ number_format($soldLeads) }}</h3>
                <p class="text-xs text-primary-500 mt-2">تم إتمامها بنجاح</p>
            </div>
        </a>
        @endcan

        <!-- Total Affiliates -->
        @can('view marketers widget')
        <a href="{{ route('admin.affiliates') }}" class="block">
            <div class="stat-card hover-lift transition-all duration-300 hover:shadow-lg group cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-accent-100 flex items-center justify-center group-hover:bg-accent-600 transition-colors duration-300">
                        <svg class="w-6 h-6 text-accent-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="bg-accent-50 rounded-full p-1 group-hover:bg-accent-100 transition-colors">
                        <svg class="w-4 h-4 text-accent-400 group-hover:text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
                <p class="stat-label group-hover:text-accent-700 transition-colors">إجمالي المسوقين</p>
                <h3 class="stat-value text-accent-600 group-hover:text-accent-700 transition-colors">{{ number_format($totalUsers) }}</h3>
                <p class="text-xs text-primary-500 mt-2">شركاء نشطون</p>
            </div>
        </a>
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
                    @php
                    $statusLabels = [
                    'new' => 'جديد',
                    'under_review' => 'تحت المراجعة',
                    'contacted' => 'تم التواصل',
                    'interested' => 'مهتم',
                    'proposal_sent' => 'تم إرسال عرض',
                    'negotiation' => 'مفاوضات',
                    'sold' => 'تم البيع',
                    'lost' => 'مرفوض',
                    'cancelled' => 'ملغي'
                    ];
                    $statusColors = [
                    'new' => 'bg-gray-500',
                    'under_review' => 'bg-warning-500',
                    'contacted' => 'bg-primary-500',
                    'interested' => 'bg-primary-500',
                    'proposal_sent' => 'bg-primary-600',
                    'negotiation' => 'bg-primary-700',
                    'sold' => 'bg-success-500',
                    'lost' => 'bg-red-500',
                    'cancelled' => 'bg-gray-400'
                    ];
                    @endphp
                    @foreach($leadsByStatusProcessed as $status => $data)
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-primary-700">{{ $statusLabels[$status] ?? $status }}</span>
                            <span class="text-sm font-bold text-primary-900">{{ $data['count'] }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                            <div class="{{ $statusColors[$status] ?? 'bg-primary-500' }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $data['percentage'] }}%"></div>
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
    </div>
</div>