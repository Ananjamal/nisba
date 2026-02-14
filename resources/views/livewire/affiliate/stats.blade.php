<?php

use Livewire\Volt\Component;

new class extends Component {
    public function with()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Rank Progress Logic
        $currentRank = \App\Models\Rank::where('name', $user->rank)->first();
        $nextRank = \App\Models\Rank::where('min_sales_count', '>', $currentRank?->min_sales_count ?? -1)
            ->orderBy('min_sales_count')
            ->first();

        $salesCount = $user->leads()->where('leads.status', 'sold')->count();
        $revenueCount = $user->leads()->where('leads.status', 'sold')->sum('expected_deal_value');

        $progress = 0;
        if ($nextRank) {
            $salesProgress = $nextRank->min_sales_count > 0 ? ($salesCount / $nextRank->min_sales_count) * 100 : 0;
            $revenueProgress = $nextRank->min_revenue > 0 ? ($revenueCount / $nextRank->min_revenue) * 100 : 0;
            $progress = min(100, max($salesProgress, $revenueProgress));
        }

        return [
            'stats' => $user->stats ?? (object)[
                'clicks_count' => 0,
                'active_clients_count' => 0,
                'total_contracts_value' => 0,
                'pending_commissions' => 0
            ],
            'user' => $user,
            'currentRank' => $currentRank,
            'nextRank' => $nextRank,
            'progress' => $progress,
            'salesCount' => $salesCount,
            'revenueCount' => $revenueCount,
            'rankHistory' => $user->rankHistories()->latest()->take(5)->get(),
        ];
    }
}; ?>

<div class="space-y-8">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- إجمالي الأرباح -->
        <div class="relative overflow-hidden bg-gradient-to-br from-primary-700 to-primary-950 p-6 rounded-[2rem] shadow-2xl shadow-primary-200/20 group border border-primary-800">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/5 rounded-full blur-2xl group-hover:bg-white/10 transition-all duration-500"></div>

            <div class="relative flex flex-col h-full justify-between">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white border border-white/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-white/60 text-xs font-black uppercase tracking-widest mb-1">إجمالي الأرباح</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-white tracking-tight">{{ number_format($stats->total_contracts_value ?? 0, 2) }}</h3>
                        <span class="text-white/40 text-[10px] font-black uppercase">ريس</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- العمولة المعلقة -->
        <div class="bg-slate-50/80 p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-700 transition-all group-hover:bg-amber-600 group-hover:text-white group-hover:scale-110 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-[9px] font-black text-amber-600 uppercase tracking-widest bg-amber-50 px-2 py-1 rounded-lg border border-amber-100">بانتظار الصرف</div>
            </div>

            <div class="mt-6">
                <p class="text-slate-500 text-xs font-black uppercase tracking-wide mb-1">العمولة المعلقة</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($stats->pending_commissions ?? 0, 2) }}</h3>
                    <span class="text-slate-400 text-[10px] font-bold">ريس</span>
                </div>
            </div>
        </div>

        <!-- العملاء المشتركين -->
        <div class="bg-slate-50/80 p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700 transition-all group-hover:bg-emerald-600 group-hover:text-white group-hover:scale-110 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="text-[9px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">نمو نشط</div>
            </div>

            <div class="mt-6">
                <p class="text-slate-500 text-xs font-black uppercase tracking-wide mb-1">العملاء المشتركين</p>
                <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($stats->active_clients_count ?? 0) }}</h3>
            </div>
        </div>

        <!-- إجمالي النقرات -->
        <div class="bg-slate-50/80 p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-2xl bg-sky-100 flex items-center justify-center text-sky-700 transition-all group-hover:bg-sky-600 group-hover:text-white group-hover:scale-110 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div class="text-[9px] font-black text-sky-600 uppercase tracking-widest bg-sky-50 px-2 py-1 rounded-lg border border-sky-100">تفاعل عالي</div>
            </div>

            <div class="mt-6">
                <p class="text-slate-500 text-xs font-black uppercase tracking-wide mb-1">إجمالي النقرات</p>
                <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($stats->clicks_count ?? 0) }}</h3>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Rank Progress Card -->
        <div class="lg:col-span-1 bg-slate-50/80 p-8 rounded-[2.5rem] border border-slate-200 shadow-sm h-fit">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-black text-slate-900">تقدم الرتبة</h3>
                    <p class="text-xs text-slate-500 font-medium">رحلتك نحو النخبة</p>
                </div>
                <div class="px-4 py-2 bg-primary-900 text-white rounded-2xl text-[10px] font-black shadow-lg shadow-primary-200/50">
                    {{ $user->getRankLabel() }}
                </div>
            </div>

            @if($nextRank)
            <div class="space-y-6">
                <div class="relative pt-1">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div>
                            <span class="text-[10px] font-black text-slate-500 uppercase">الرتبة القادمة</span>
                            <div class="text-sm font-black text-slate-900">{{ $user->getRankLabel($nextRank->name) }}</div>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-primary-600">{{ round($progress) }}%</span>
                        </div>
                    </div>
                    <div class="flex overflow-hidden h-3 text-xs flex-col rounded-full bg-slate-200 shadow-inner">
                        <div style="width: {{ $progress }}%" class="flex flex-col justify-center overflow-hidden bg-primary-600 rounded-full transition-all duration-1000 ease-out shadow-lg shadow-primary-200"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-white rounded-3xl border border-slate-200 hover:border-primary-100 transition-colors shadow-sm">
                        <p class="text-[9px] text-slate-400 font-black uppercase mb-2">عدد المبيعات</p>
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg font-black text-slate-900">{{ $salesCount }}</span>
                            <span class="text-slate-400 text-[10px] font-bold">/ {{ $nextRank->min_sales_count }}</span>
                        </div>
                    </div>
                    <div class="p-4 bg-white rounded-3xl border border-slate-200 hover:border-primary-100 transition-colors shadow-sm">
                        <p class="text-[9px] text-slate-400 font-black uppercase mb-2">قيمة المبيعات</p>
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-slate-900">{{ number_format($revenueCount) }}</span>
                            <span class="text-slate-400 text-[9px] font-medium tracking-tighter">من {{ number_format($nextRank->min_revenue) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-10">
                <div class="relative inline-block mb-6">
                    <span class="text-6xl filter drop-shadow-xl animate-bounce inline-block">👑</span>
                    <div class="absolute inset-0 bg-yellow-400/20 blur-3xl rounded-full"></div>
                </div>
                <h4 class="text-2xl font-black text-primary-900 mb-2">قمة النجاح!</h4>
                <p class="text-gray-500 text-sm font-medium">لقد وصلت حقاً لأعلى رتبة متاحة، تهانينا على هذا الإنجاز!</p>
            </div>
            @endif

            <!-- History Timeline -->
            <div class="mt-12 space-y-6">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-1.5 h-4 bg-primary-600 rounded-full"></div>
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest">سجل النشاط</h4>
                </div>

                <div class="space-y-4">
                    @forelse($rankHistory as $history)
                    <div class="relative flex items-center gap-4 group">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-primary-600 transition-all group-hover:bg-primary-600 group-hover:text-white group-hover:scale-110 border border-slate-200 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div class="flex-1 border-b border-slate-100 pb-4 group-last:border-0">
                            <div class="flex justify-between items-start mb-1">
                                <p class="text-xs font-black text-slate-900 leading-tight">
                                    تغيير الرتبة لـ <span class="text-primary-600">{{ $user->getRankLabel($history->new_rank) }}</span>
                                </p>
                                <span class="text-[9px] font-bold text-slate-400">{{ $history->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium tracking-tight">{{ $history->reason }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 bg-white rounded-3xl border border-dashed border-slate-200">
                        <p class="text-[10px] text-slate-400 font-black italic uppercase">لا يوجد نشاط مسجل</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="lg:col-span-2 flex flex-col gap-8">
            <div class="bg-slate-50/80 p-4 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <livewire:components.dashboard-chart
                    chartId="affiliate-sales"
                    type="sales"
                    period="month"
                    title="تحليلات المبيعات"
                    :key="'aff-sales-'.now()->timestamp" />
            </div>

            <div class="bg-slate-50/80 p-4 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <livewire:components.dashboard-chart
                    chartId="affiliate-commissions"
                    type="commissions"
                    period="month"
                    title="نمو العمولات"
                    :key="'aff-comm-'.now()->timestamp" />
            </div>
        </div>
    </div>
</div>
</div>