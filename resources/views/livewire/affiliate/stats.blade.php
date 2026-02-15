<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
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
            'modalData' => $this->getModalData(),
        ];
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

    private function getModalData()
    {
        if (!$this->showModal) return collect();
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return match ($this->modalType) {
            'total_earnings' => $user->leads()->where('leads.status', 'sold')->latest()->take(20)->get(),
            'pending_commissions' => \App\Models\Commission::with('lead')->where('user_id', $user->id)->where('status', 'pending')->latest()->take(20)->get(),
            'active_clients' => $user->leads()->whereNotIn('leads.status', ['sold', 'lost', 'cancelled'])->latest()->take(20)->get(),
            'total_clicks' => collect(),
            default => collect(),
        };
    }
}; ?>

<div class="space-y-8 pb-12" x-data="{ chartTab: 'sales' }">
    <!-- Header -->
    <div class="flex items-end justify-between bg-white/50 backdrop-blur-lg p-6 rounded-3xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">{{ __('لوحة القيادة') }}</h2>
            <p class="text-gray-500 font-bold mt-2">{{ __('نظرة عامة على أدائك وأرباحك') }}</p>
        </div>
        <div class="px-6 py-3 bg-white border border-gray-200 text-gray-900 rounded-2xl shadow-sm font-black flex items-center gap-3">
            <div class="flex flex-col items-end">
                <span class="text-xs text-gray-400 font-bold uppercase">المسوق</span>
                <span>{{ $user->name }}</span>
            </div>
            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center text-primary-600 border border-primary-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Stats Grid (Minimalist Design) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- إجمالي الأرباح -->
        <div wire:click="openModal('total_earnings', 'المبيعات المكتملة')"
            class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all cursor-pointer group flex items-center gap-5">
            <div class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600 shrink-0 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <div class="flex-1">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">إجمالي الأرباح</p>
                <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats->total_contracts_value ?? 0, 2) }}</h3>
                <div class="flex items-center gap-1 mt-1 text-[10px] font-bold text-primary-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span>+12% نمو</span>
                </div>
            </div>
        </div>

        <!-- العمولة المعلقة -->
        <div wire:click="openModal('pending_commissions', 'العمولات المعلقة')"
            class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all cursor-pointer group flex items-center gap-5">
            <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 shrink-0 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <div class="flex-1">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">بانتظار الصرف</p>
                <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats->pending_commissions ?? 0, 2) }}</h3>
                <div class="flex items-center gap-1 mt-1 text-[10px] font-bold text-amber-600">
                    <span>قيد المعالجة</span>
                </div>
            </div>
        </div>

        <!-- العملاء المشتركين -->
        <div wire:click="openModal('active_clients', 'العملاء المهتمون')"
            class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all cursor-pointer group flex items-center gap-5">
            <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 shrink-0 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>

            <div class="flex-1">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">عملاء مشتركين</p>
                <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats->active_clients_count ?? 0) }}</h3>
                <div class="flex items-center gap-1 mt-1 text-[10px] font-bold text-emerald-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span>+5 جدد</span>
                </div>
            </div>
        </div>

        <!-- إجمالي النقرات -->
        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group flex items-center gap-5">
            <div class="w-16 h-16 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 shrink-0 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </div>

            <div class="flex-1">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">إجمالي النقرات</p>
                <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats->clicks_count ?? 0) }}</h3>
                <div class="flex items-center gap-1 mt-1 text-[10px] font-bold text-rose-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span>+24% نشاط</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid with 3:1 Ratio -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">

        <!-- Tabbed Charts Card (3/4 Width) -->
        <div class="xl:col-span-3 space-y-8">
            <div class="bg-white rounded-[2rem] border border-gray-200 shadow-sm hover:shadow-xl transition-shadow duration-300 overflow-hidden min-h-[500px]">
                <!-- Header & Tabs -->
                <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-black text-gray-900" x-text="chartTab === 'sales' ? 'تحليلات المبيعات' : (chartTab === 'commissions' ? 'نمو العمولات' : 'الإيرادات')"></h3>
                        <p class="text-xs text-gray-400 font-bold mt-1">متابعة دقيقة للأداء البياني</p>
                    </div>

                    <!-- Segmented Control Tabs -->
                    <div class="bg-gray-100/80 p-1 rounded-xl flex items-center">
                        <button @click="chartTab = 'sales'"
                            :class="chartTab === 'sales' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'"
                            class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200">
                            المبيعات
                        </button>
                        <button @click="chartTab = 'commissions'"
                            :class="chartTab === 'commissions' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'"
                            class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200">
                            العمولات
                        </button>
                        <button @click="chartTab = 'revenue'"
                            :class="chartTab === 'revenue' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'"
                            class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200">
                            الإيرادات
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="p-0">
                    <div x-show="chartTab === 'sales'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="h-full">
                        <livewire:affiliate.components.chart
                            chartId="affiliate-sales-tab"
                            type="sales"
                            period="month"
                            title="إجمالي عدد المبيعات"
                            color="blue"
                            :key="'aff-sales-tab-'.now()->timestamp" />
                    </div>

                    <div x-show="chartTab === 'commissions'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="h-full">
                        <livewire:affiliate.components.chart
                            chartId="affiliate-commissions-tab"
                            type="commissions"
                            period="month"
                            title="قيمة العمولات المكتسبة"
                            color="emerald"
                            :key="'aff-comm-tab-'.now()->timestamp" />
                    </div>

                    <div x-show="chartTab === 'revenue'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="h-full">
                        <livewire:affiliate.components.chart
                            chartId="affiliate-revenue-tab"
                            type="revenue"
                            period="month"
                            title="إجمالي قيمة الإيرادات"
                            color="indigo"
                            :key="'aff-rev-tab-'.now()->timestamp" />
                    </div>
                </div>
            </div>

            <!-- Activity Log Moved Here (Optional, based on user preference for flow. Keeping it at bottom typically better) -->
            <div class="bg-white p-8 rounded-[2rem] border border-gray-200 shadow-sm">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 border border-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-gray-900">سجل النشاط</h4>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse($rankHistory as $history)
                    <div class="flex items-start gap-4 p-4 rounded-2xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                        <div class="w-2 h-2 rounded-full bg-primary-500 mt-2 shrink-0"></div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">
                                تغيير الرتبة لـ <span class="text-primary-600">{{ $user->getRankLabel($history->new_rank) }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $history->reason }}</p>
                        </div>
                        <span class="mr-auto text-[10px] font-bold text-gray-400">{{ $history->created_at->diffForHumans() }}</span>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <p class="text-gray-400 text-sm font-bold">لا يوجد نشاط مسجل</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Rank Progress Card (1/4 Width) -->
        <div class="xl:col-span-1 space-y-8">
            <div class="bg-gray-900 text-white p-8 rounded-[2rem] shadow-xl shadow-gray-900/10 relative overflow-hidden group min-h-[500px] flex flex-col">
                <!-- Background decoration -->
                <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-gradient-to-br from-primary-500/10 to-transparent rounded-full blur-3xl -mr-64 -mt-64"></div>

                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-black text-white">تقدم الرتبة</h3>
                            <p class="text-xs text-gray-400 font-medium mt-1">رحلتك نحو النخبة</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center border border-white/10">
                            <span class="text-xl">🏆</span>
                        </div>
                    </div>

                    @if($nextRank)
                    <div class="flex-1 flex flex-col justify-center">
                        <div class="text-center mb-8">
                            <p class="text-xs font-black uppercase text-gray-500 mb-2">المستوى الحالي</p>
                            <h2 class="text-4xl font-black text-white mb-1">{{ $user->getRankLabel($user->rank) }}</h2>
                            <p class="text-[10px] text-gray-400">الهدف: {{ $user->getRankLabel($nextRank->name) }}</p>
                        </div>

                        <div class="relative w-48 h-48 mx-auto mb-8 flex items-center justify-center">
                            <!-- Circular Progress -->
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="12" fill="none" class="text-gray-800" />
                                <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="12" fill="none" class="text-primary-500" stroke-dasharray="553" stroke-dashoffset="{{ 553 - (553 * $progress / 100) }}" stroke-linecap="round" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center flex-col">
                                <span class="text-4xl font-black text-white">{{ round($progress) }}%</span>
                                <span class="text-[10px] text-gray-400 uppercase font-bold mt-1">مكتمل</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-auto">
                        <div class="bg-white/5 p-3 rounded-xl border border-white/5 text-center">
                            <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">المبيعات</p>
                            <p class="text-sm font-black">{{ $salesCount }} / {{ $nextRank->min_sales_count }}</p>
                        </div>
                        <div class="bg-white/5 p-3 rounded-xl border border-white/5 text-center">
                            <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">الإيرادات</p>
                            <p class="text-sm font-black">{{ number_format($revenueCount) }}</p>
                        </div>
                    </div>
                    @else
                    <div class="flex-1 flex flex-col justify-center text-center">
                        <div class="text-8xl mb-6">👑</div>
                        <h4 class="text-2xl font-black text-white mb-2">رتبة القمة!</h4>
                        <p class="text-gray-400 text-sm">أنت في أعلى مستوى. تهانينا!</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Details Modal -->
    <x-modal name="stats-details" :show="$showModal" maxWidth="lg">
        <div dir="rtl" class="relative overflow-hidden rounded-3xl bg-white border border-gray-200 shadow-2xl max-w-lg mx-auto">
            <!-- Header -->
            <div class="relative px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-primary-500 text-white flex items-center justify-center shadow-lg shadow-primary-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900">{{ $modalTitle }}</h3>
                            <p class="text-xs text-gray-400 font-bold">ملخص شامل للبيانات</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="w-10 h-10 rounded-xl hover:bg-gray-100 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="px-6 py-6 space-y-4 max-h-[60vh] overflow-y-auto">
                @php
                $statusMap = [
                'new' => 'جديد',
                'under_review' => 'مراجعة',
                'contacted' => 'تواصل',
                'interested' => 'مهتم',
                'proposal_sent' => 'عرض',
                'negotiation' => 'مفاوضة',
                'sold' => 'تم البيع',
                'lost' => 'مرفوض',
                'cancelled' => 'ملغي'
                ];
                @endphp

                @forelse($modalData as $item)
                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 hover:border-primary-200 hover:bg-white transition-all duration-300 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-primary-600 font-black group-hover:scale-110 transition-transform">
                                {{ mb_substr($this->modalType === 'pending_commissions' ? ($item->lead->client_name ?? 'C') : ($item->client_name ?? 'L'), 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-black text-gray-900 leading-tight">
                                    @if($this->modalType === 'pending_commissions')
                                    {{ $item->lead->client_name ?? 'عميل غير معروف' }}
                                    <span class="block text-[10px] text-primary-600 mt-0.5">عمولة: {{ number_format($item->amount, 2) }}</span>
                                    @else
                                    {{ $item->client_name ?? 'عميل' }}
                                    @endif
                                </p>
                                <p class="text-[10px] font-bold text-gray-400 mt-0.5">
                                    {{ $item->created_at->format('Y-m-d H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="px-3 py-1 rounded-lg text-[10px] font-black {{ $this->modalType === 'pending_commissions' ? 'bg-amber-50 text-amber-600' : 'bg-primary-50 text-primary-600' }}">
                            {{ $this->modalType === 'pending_commissions' ? 'معلق' : ($statusMap[$item->status] ?? $item->status ?? 'نشط') }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <div class="text-5xl mb-4 opacity-20">📂</div>
                    <p class="text-gray-400 font-bold">لا توجد بيانات ليتم عرضها</p>
                </div>
                @endforelse
            </div>

            <!-- Footer -->
            <div class="px-6 py-5 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button wire:click="closeModal" class="px-6 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-black text-gray-600 hover:bg-gray-50 transition-colors">
                    إغلاق
                </button>
            </div>
        </div>
    </x-modal>
</div>