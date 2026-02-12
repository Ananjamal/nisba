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

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- إجمالي الأرباح -->
        <div class="stat-card">
            <div class="stat-icon bg-primary-100">
                <svg class="w-6 h-6 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-content">
                <div class="flex items-center gap-2">
                    <p class="stat-label">إجمالي الأرباح</p>
                    <div class="relative group">
                        <button class="text-primary-400 hover:text-primary-600 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <!-- Tooltip -->
                        <div class="invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 absolute z-10 w-64 p-3 bg-gray-900 text-white text-xs rounded-lg shadow-lg -top-2 left-6 transform -translate-y-full">
                            <div class="space-y-1">
                                <p class="font-bold text-yellow-300">💡 كيفية حساب العمولة:</p>
                                <p>العمولة = (القيمة الأساسية × نسبتك) × مضاعف الرتبة</p>
                                <p class="text-yellow-200 font-bold mt-2">مضاعف رتبتك الحالية: {{ $user->commission_multiplier }}×</p>
                                <p class="text-gray-300 text-[10px] mt-1">كلما ارتفعت رتبتك، زادت أرباحك! 🚀</p>
                            </div>
                            <!-- Arrow -->
                            <div class="absolute top-1/2 -left-1 -translate-y-1/2 w-2 h-2 bg-gray-900 transform rotate-45"></div>
                        </div>
                    </div>
                </div>
                <h3 class="stat-value">{{ number_format($stats->total_contracts_value ?? 0, 2) }} <span class="text-sm font-normal">ريال</span></h3>
                <p class="stat-description">الأرباح المحققة حتى الآن (شاملة مضاعف الرتبة)</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Rank Progress -->
        <div class="lg:col-span-1 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm h-fit">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">تقدم الرتبة</h3>
                <span class="px-3 py-1 bg-primary-50 text-primary-700 rounded-xl text-xs font-bold">{{ $user->getRankLabel() }}</span>
            </div>

            @if($nextRank)
            <div class="space-y-4">
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-bold text-gray-700">التقدم نحو رتبة {{ $user->getRankLabel($nextRank->name) }}</span>
                    <span class="text-primary-600 font-bold">{{ round($progress) }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="bg-primary-600 h-3 rounded-full transition-all duration-1000" style="width: {{ $progress }}%"></div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                        <p class="text-xs text-gray-500 font-bold mb-1">المبيعات</p>
                        <p class="text-sm font-bold text-gray-900">{{ $salesCount }} / {{ $nextRank->min_sales_count }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                        <p class="text-xs text-gray-500 font-bold mb-1">قيمة المبيعات</p>
                        <p class="text-sm font-bold text-gray-900">{{ number_format($revenueCount) }} / {{ number_format($nextRank->min_revenue) }}</p>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-8">
                <span class="text-4xl shadow-sm bg-yellow-50 p-4 rounded-full border border-yellow-100 mb-4 inline-block">👑</span>
                <p class="text-gray-900 font-bold">لقد وصلت لأعلى رتبة!</p>
                <p class="text-gray-500 text-sm">أنت الآن من النخبة الذهبية</p>
            </div>
            @endif

            <!-- Activity Timeline -->
            <h3 class="text-lg font-bold text-gray-900 mt-8 mb-4">سجل النشاط</h3>
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @forelse($rankHistory as $history)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                            <span class="absolute top-4 right-4 -ml-px h-full w-0.5 bg-gray-100" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3 rtl:space-x-reverse">
                                <div>
                                    <span class="h-8 w-8 rounded-full bg-primary-50 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-5 w-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5 rtl:space-x-reverse">
                                    <div>
                                        <p class="text-sm text-gray-600 font-bold">تغيير الرتبة إلى <span class="text-primary-600">{{ $user->getRankLabel($history->new_rank) }}</span></p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $history->reason }}</p>
                                    </div>
                                    <div class="whitespace-nowrap text-left text-xs text-gray-400">
                                        <time datetime="{{ $history->created_at }}">{{ $history->created_at->diffForHumans() }}</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @empty
                    <p class="text-gray-400 text-xs italic">لا يوجد نشاط مسجل مؤخراً</p>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Charts -->
        <div class="lg:col-span-2 space-y-6">
            <livewire:components.dashboard-chart
                chartId="affiliate-sales"
                type="sales"
                period="month"
                title="أدائي في المبيعات"
                :key="'aff-sales-'.now()->timestamp" />

            <livewire:components.dashboard-chart
                chartId="affiliate-commissions"
                type="commissions"
                period="month"
                title="تطور عمولاتي"
                :key="'aff-comm-'.now()->timestamp" />
        </div>
    </div>
</div>