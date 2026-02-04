<?php

use Livewire\Volt\Component;
use App\Models\ReferralLink;

new class extends Component {
    public function with()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $links = ReferralLink::all()->map(function ($link) use ($user) {
            $userRef = $user->referrals()->where('referral_link_id', $link->id)->first();

            if (!$userRef) {
                $userRef = $user->referrals()->create([
                    'referral_link_id' => $link->id,
                    'unique_ref_id' => strtolower(str_replace(' ', '', $link->service_name)) . '-' . $user->id . '-' . bin2hex(random_bytes(2)),
                ]);
            }

            $link->tracking_url = route('referral.redirect', $userRef->unique_ref_id);
            return $link;
        });


        return [
            'links' => $links,
        ];
    }
}; ?>

<div class="space-y-12 py-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
        <div>
            <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-white/20 text-white text-[10px] font-black uppercase tracking-[0.2em] mb-4 border border-white/30 shadow-sm backdrop-blur-sm">{{ __('برنامج الشركاء المميز') }}</span>
            <h3 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-2">{{ __('روابط الإحالة الخاصة بك') }}</h3>
            <p class="text-base text-white/70 leading-relaxed max-w-2xl">{{ __('ابدأ بمشاركة روابطك وحقق عوائد مجزية من كل عملية بيع ناجحة بكل سهولة وفخامة.') }}</p>
        </div>
        <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md p-3 rounded-[2rem] border border-white/20 shadow-soft">
            <div class="flex -space-x-3 space-x-reverse">
                @for($i=1; $i<=3; $i++)
                    <div class="w-10 h-10 rounded-[1.1rem] border-2 border-white bg-white/90 flex items-center justify-center overflow-hidden shadow-sm">
                    <img src="https://ui-avatars.com/api/?name=User+{{$i}}&background=random" class="w-full h-full object-cover">
            </div>
            @endfor
            <div class="w-10 h-10 rounded-[1.1rem] border-2 border-white bg-cyber-600 flex items-center justify-center text-white text-[10px] font-black shadow-sm">
                +50
            </div>
        </div>
        <p class="text-[11px] font-black text-white/60 uppercase tracking-widest">{{ __('مسوق نشط حالياً') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
    @foreach($links as $link)
    <div class="neo-card group relative flex flex-col h-full border-deep-blue-100/50">
        <!-- Glow Effect -->
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-cyber-500/5 rounded-full blur-[80px] group-hover:bg-cyber-500/10 transition-all duration-700"></div>

        <div class="relative p-10 flex flex-col h-full z-10">
            <div class="flex items-start justify-between mb-10">
                <div class="flex items-center gap-6">
                    <div class="relative group/logo">
                        <div class="absolute inset-0 bg-cyber-500/15 blur-2xl rounded-full scale-0 group-hover/logo:scale-125 transition-transform duration-700"></div>
                        <div class="relative w-22 h-22 bg-white border border-deep-blue-100 rounded-[2rem] p-4 flex items-center justify-center shadow-soft group-hover/logo:border-cyber-200 transition-all duration-500">
                            <img src="{{ $link->logo_url }}" alt="{{ $link->service_name }}" class="w-full h-full object-contain filter group-hover/logo:scale-110 transition-transform duration-500">
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-deep-blue-900 mb-2 tracking-tighter">{{ $link->service_name }}</h3>
                        <div class="flex items-center gap-2.5 bg-electric-50/50 px-3 py-1.5 rounded-full border border-electric-200/30">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-electric-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-electric-500"></span>
                            </span>
                            <span class="text-[9px] font-black text-electric-600 uppercase tracking-widest">{{ __('رابط نشط للعمل') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-2">
                    <div class="bg-cyber-50 px-4 py-2 rounded-2xl border border-cyber-200/50 shadow-sm">
                        <span class="text-[11px] font-black text-cyber-700 uppercase tracking-widest">{{ __('عمولة 20%') }}</span>
                    </div>
                    <span class="text-[9px] font-black text-deep-blue-300 uppercase tracking-[0.2em] px-1">Fixed Profit</span>
                </div>
            </div>

            <div class="mt-auto pt-8 border-t border-deep-blue-100/50">
                <div class="flex justify-between items-center mb-4">
                    <label class="text-[10px] font-black text-deep-blue-400 uppercase tracking-[0.25em] block px-1">{{ __('رابط التتبع الحصري') }}</label>
                    <span class="text-[9px] font-black text-cyber-500 uppercase tracking-widest opacity-60">Verified Link</span>
                </div>

                <div class="relative">
                    <div class="flex items-center bg-deep-blue-50/50 border border-deep-blue-200/40 rounded-[1.75rem] p-2 pl-2 pr-8 focus-within:ring-8 focus-within:ring-cyber-500/5 focus-within:border-cyber-300 focus-within:bg-white transition-all duration-500">
                        <input type="text"
                            readonly
                            value="{{ $link->tracking_url }}"
                            class="w-full bg-transparent border-none text-left text-deep-blue-900 font-mono text-[13px] font-bold focus:ring-0 truncate py-4 opacity-70 group-hover:opacity-100 transition-opacity"
                            dir="ltr">

                        <button onclick="navigator.clipboard.writeText('{{ $link->tracking_url }}'); alert('تم نسخ الرابط بنجاح!')"
                            class="btn-primary !px-8 !py-4 !rounded-2xl !text-[13px]">
                            <svg class="w-4 h-4 mr-1 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                            </svg>
                            <span>{{ __('نسخ الرابط') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Smart Tip - Modern Luxury Design -->
<div class="relative mt-16 group">
    <div class="absolute inset-0 bg-primary-600 rounded-[3rem] rotate-1 group-hover:rotate-0 transition-transform duration-700 opacity-5 shadow-2xl"></div>
    <div class="bg-white border border-slate-100 rounded-[3rem] p-8 md:p-12 relative overflow-hidden shadow-glass group-hover:shadow-premium transition-all duration-700">
        <!-- Abstract background shape -->
        <div class="absolute top-0 right-0 w-1/3 h-full bg-gradient-to-l from-primary-50/30 to-transparent -z-10"></div>

        <div class="flex flex-col md:flex-row items-center gap-12 relative z-10">
            <div class="relative">
                <div class="w-24 h-24 bg-primary-600 rounded-[2.5rem] flex items-center justify-center shadow-2xl shadow-primary-600/30 animate-float">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="absolute -bottom-2 -right-2 bg-accent-rose text-white text-[10px] font-black px-2 py-1 rounded-lg shadow-lg">TIP</div>
            </div>

            <div class="flex-1 text-center md:text-right">
                <h4 class="text-3xl font-black text-slate-900 mb-4 tracking-tighter">{{ __('كيف تضاعف أرباحك بلمسة فنية؟') }}</h4>
                <p class="text-lg text-slate-500 font-medium leading-relaxed max-w-2xl">
                    {{ __('الإحصائيات تؤكد أن المسوقين الذين يقدمون مراجعات صادقة ومحتوى تعليمي حول الخدمة يحققون نسب مبيعات أعلى بـ 5 أضعاف. ابدأ بصناعة المحتوى اليوم.') }}
                </p>
            </div>

            <div class="shrink-0">
                <button class="btn-luxury-outline group/btn !px-10 !py-5 !text-sm">
                    <span>{{ __('تنزيل أدوات المسوق') }}</span>
                    <svg class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
</div>
</div>