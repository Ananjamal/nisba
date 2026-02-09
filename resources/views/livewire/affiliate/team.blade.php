<?php

use Livewire\Volt\Component;

new class extends Component {
    public function with()
    {
        $user = auth()->user();
        return [
            'teamMembers' => $user->children()->with('stats')->get(),
        ];
    }
}; ?>

<div class="space-y-10 py-4">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2">
        <div>
            <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-primary-50 text-primary-600 text-[10px] font-black uppercase tracking-[0.2em] mb-4 border border-primary-100/50 shadow-sm">{{ __('إدارة الفريق') }}</span>
            <h3 class="section-title !text-3xl !mb-0">{{ __('فريق المسوقين الخاص بك') }}</h3>
            <p class="section-subtitle !text-sm mt-2">{{ __('تابع أداء فريقك وعمولاتك غير المباشرة بكل دقة وفخامة.') }}</p>
        </div>
        <div class="bg-white/60 backdrop-blur-md px-6 py-3 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-2 h-2 bg-primary-500 rounded-full animate-pulse"></div>
                <span class="text-[11px] font-black text-slate-900 uppercase tracking-widest">{{ __('عمولة الفريق الثابتة: 5%') }}</span>
            </div>
        </div>
    </div>

    <!-- Recruitment Luxury Banner -->
    <div class="premium-card !bg-white p-1 overflow-hidden group">
        <div class="bg-slate-900 rounded-[2.3rem] p-10 relative overflow-hidden flex flex-col xl:flex-row items-center justify-between gap-10">
            <!-- Background Decoration -->
            <div class="absolute inset-0 bg-gradient-to-br from-primary-600/20 to-transparent opacity-50 group-hover:opacity-70 transition-opacity duration-700"></div>
            <div class="absolute -top-24 -left-24 w-80 h-80 bg-primary-600/10 rounded-full blur-[100px]"></div>

            <div class="relative z-10 space-y-4 max-w-2xl text-center xl:text-right">
                <div class="inline-flex items-center gap-2 bg-white/10 px-4 py-2 rounded-xl border border-white/10 mb-2">
                    <span class="text-[10px] font-black text-primary-400 uppercase tracking-[0.3em]">Ambassador Program</span>
                </div>
                <h4 class="text-3xl font-black text-white tracking-tighter">{{ __('توسيع فريقك يعني أرباحاً لا تتوقف! 🚀') }}</h4>
                <p class="text-slate-400 text-lg font-medium leading-relaxed">
                    {{ __('شارك رابط الدعوة الحصري مع المسوقين المبدعين واربح عمولة ثابتة 5% من كل مبيعة يحققونها آلياً دون أي مجهود إضافي.') }}
                </p>
            </div>

            <div class="relative z-10 w-full xl:w-auto">
                <div class="bg-white/5 backdrop-blur-xl p-4 rounded-[2rem] border border-white/10 flex flex-col sm:flex-row items-center gap-4">
                    <div class="flex-1 px-6 py-4 bg-black/40 rounded-2xl border border-white/5 shadow-inner">
                        <code class="text-xs font-mono text-primary-300 tracking-wider truncate block text-center" dir="ltr">{{ route('register', ['ref' => auth()->id()]) }}</code>
                    </div>
                    <button @click="navigator.clipboard.writeText('{{ route('register', ['ref' => auth()->id()]) }}'); alert('تم نسخ رابط دعوة الفريق!');"
                        class="btn-luxury !bg-white !text-slate-900 !px-8 !py-5 !rounded-2xl !text-xs !shadow-2xl hover:!bg-primary-50 transition-all duration-500 whitespace-nowrap">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                        {{ __('نسخ الرابط الذكي') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($teamMembers as $member)
        <div class="premium-card p-8 group hover:border-primary-100 transition-all duration-500">
            <div class="flex items-center gap-5 mb-8">
                <div class="relative">
                    <div class="absolute inset-0 bg-primary-100 blur-xl rounded-full opacity-0 group-hover:opacity-40 transition-opacity duration-500"></div>
                    <div class="relative w-16 h-16 bg-slate-50 border border-slate-100 rounded-[1.5rem] flex items-center justify-center font-black text-slate-400 group-hover:text-primary-600 group-hover:bg-white group-hover:border-primary-100 transition-all duration-500 shadow-sm text-2xl">
                        {{ mb_substr($member->name, 0, 1) }}
                    </div>
                </div>
                <div>
                    <h4 class="font-black text-slate-900 text-lg tracking-tight mb-1">{{ $member->name }}</h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest opacity-60">{{ $member->email }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-8 pt-8 border-t border-slate-50 relative">
                <div class="absolute -top-3 right-1/2 translate-x-1/2 bg-white px-3 py-1 rounded-full border border-slate-50 shadow-sm">
                    <span class="text-[8px] font-black text-slate-300 uppercase tracking-[0.3em]">Performance</span>
                </div>
                <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100/30">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('المبيعات') }}</p>
                    <p class="text-xl font-black text-slate-900 tracking-tighter">{{ $member->stats->active_clients_count ?? 0 }}</p>
                </div>
                <div class="bg-primary-50/50 p-4 rounded-2xl border border-primary-100/30">
                    <p class="text-[9px] font-black text-primary-600 uppercase tracking-widest mb-2">{{ __('ربحك') }}</p>
                    <div class="flex items-baseline gap-1">
                        <p class="text-xl font-black text-primary-600 tracking-tighter">{{ number_format(($member->stats->pending_commissions ?? 0) * 0.05, 2) }}</p>
                        <span class="text-[8px] font-black text-primary-400 uppercase tracking-widest">ر.س</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-24 flex flex-col items-center justify-center text-center bg-slate-50/20 rounded-[3rem] border-2 border-dashed border-slate-100 group">
            <div class="w-20 h-20 bg-white rounded-[2rem] flex items-center justify-center text-slate-200 mb-6 shadow-sm group-hover:scale-110 transition-transform duration-500">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <p class="text-sm font-black text-slate-300 uppercase tracking-[0.2em] italic">{{ __('لا يوجد أعضاء في فريقك حالياً') }}</p>
            <p class="text-[10px] text-slate-400 font-bold mt-2">{{ __('ابدأ بمشاركة رابط الدعوة لبناء إمبراطوريتك التسويقية') }}</p>
        </div>
        @endforelse
    </div>
</div>