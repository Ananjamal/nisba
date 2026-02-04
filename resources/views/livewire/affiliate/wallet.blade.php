<?php

use Livewire\Volt\Component;
use App\Models\WithdrawalRequest;

new class extends Component {
    public $iban = '';
    public $bank_name = '';
    public $account_holder_name = '';
    public $showRequestModal = false;

    public function with()
    {
        return [
            'stats' => auth()->user()->stats,
            'requests' => auth()->user()->withdrawalRequests()->latest()->get(),
        ];
    }

    public function requestPayout()
    {
        $this->validate([
            'iban' => 'required|min:15',
            'bank_name' => 'required|min:3',
            'account_holder_name' => 'required|min:3',
        ]);

        $amount = auth()->user()->stats->pending_commissions;

        if ($amount <= 0) {
            $this->dispatch('notify', message: 'ليس لديك عمولات كافية للصرف', type: 'error');
            return;
        }

        auth()->user()->withdrawalRequests()->create([
            'amount' => $amount,
            'iban' => $this->iban,
            'bank_name' => $this->bank_name,
            'account_holder_name' => $this->account_holder_name,
            'status' => 'pending',
        ]);

        $this->reset(['iban', 'bank_name', 'account_holder_name', 'showRequestModal']);
        $this->dispatch('payout-requested', message: 'تم تقديم طلب السحب بنجاح!');
        $this->dispatch('notify', message: 'تم تقديم طلب السحب بنجاح!');
    }
}; ?>

<div class="space-y-8">
    <!-- Balance Card -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="heading-sm !mb-0">{{ __('الرصيد المحفظة') }}</h3>
            <span class="badge-primary !px-4 !py-1.5">{{ __('إدارة الارباح') }}</span>
        </div>

        <div class="neo-card p-1">
            <div class="bg-gradient-to-br from-cyber-600 to-cyber-700 rounded-[2.3rem] p-10 relative overflow-hidden shadow-glow-cyber">
                <!-- Abstract Design Elements -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-32 -mt-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full blur-2xl -ml-24 -mb-24"></div>

                <div class="relative flex flex-col md:flex-row items-center justify-between gap-10">
                    <div class="text-center md:text-right">
                        <span class="text-xs font-black text-cyber-100 uppercase tracking-[0.3em] block mb-4 opacity-80">{{ __('الرصيد المتاح للسحب حالياً') }}</span>
                        <div class="flex items-baseline justify-center md:justify-start gap-3">
                            <h2 class="text-6xl font-black text-white tracking-tighter">
                                {{ number_format($stats->pending_commissions ?? 0, 2) }}
                            </h2>
                            <span class="text-xl font-black text-cyber-200 uppercase tracking-widest">ر.س</span>
                        </div>
                    </div>

                    <div class="shrink-0 w-full md:w-auto">
                        <button
                            @click="$dispatch('show-withdrawal-modal')"
                            @if(($stats->pending_commissions ?? 0) <= 0) disabled @endif
                                class="group relative overflow-hidden bg-white text-cyber-600 px-10 py-5 rounded-[1.75rem] font-black text-sm transition-all duration-500 hover:shadow-2xl hover:shadow-white/20 hover:-translate-y-1 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                                <div class="absolute inset-0 bg-cyber-50 translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
                                <svg class="w-5 h-5 relative z-10 group-hover:rotate-12 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="relative z-10">{{ __('سحب العمولات الآن') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if(!auth()->user()->bank_account_verified_at)
        <div class="glass-card !bg-amber-50/50 !rounded-2xl border-amber-100/50 p-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600 shadow-sm animate-pulse">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <p class="text-xs font-black text-amber-800 tracking-tight leading-relaxed">
                {{ __('تنبيه الحماية: يجب توثيق بيانات حسابك البنكي في الملف الشخصي لتفعيل طلبات السحب.') }}
            </p>
        </div>
        @endif
    </div>

    <!-- Transactions -->
    <div class="space-y-6">
        <div class="flex items-center justify-between px-2">
            <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ __('سجل طلبات الصرف') }}</h3>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('محدث لحظياً') }}</span>
            </div>
        </div>

        <div class="premium-card overflow-hidden">
            <div class="divide-y divide-slate-50">
                @forelse($requests as $payout)
                <div class="p-6 hover:bg-slate-50/50 transition-all duration-500 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <div class="w-14 h-14 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 group-hover:scale-110 group-hover:bg-primary-600 group-hover:text-white group-hover:border-primary-600 transition-all duration-500 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-baseline gap-1.5 mb-1">
                                    <span class="text-xl font-black text-slate-900">{{ number_format($payout->amount, 2) }}</span>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">ر.س</span>
                                </div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest opacity-60">{{ $payout->created_at->format('d M, Y') }} • {{ $payout->created_at->format('H:i') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            @if($payout->status == 'pending')
                            <span class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 border border-amber-100/50 shadow-sm">
                                {{ __('قيد المراجعة') }}
                            </span>
                            @elseif($payout->status == 'completed' || $payout->status == 'paid')
                            <span class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100/50 shadow-sm">
                                {{ __('تم الصرف') }}
                            </span>
                            @else
                            <span class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 border border-rose-100/50 shadow-sm">
                                {{ __('مرفوض') }}
                            </span>
                            @endif
                            <button class="p-2 text-slate-300 hover:text-slate-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-24 flex flex-col items-center justify-center text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mb-6 border border-slate-100/50 animate-float">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-black text-slate-300 uppercase tracking-[0.2em]">{{ __('لا توجد طلبات صرف حتى الآن') }}</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>