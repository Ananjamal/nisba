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

<div class="space-y-10 py-4">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 px-2">
        <div class="space-y-4">
            <span class="badge-primary !px-4 !py-1.5">{{ __('برنامج الشركاء') }}</span>
            <h3 class="heading-md !mb-0">{{ __('روابط الإحالة النشطة') }}</h3>
            <p class="text-body !text-sm">{{ __('قم بنسخ الروابط أدناه وابدأ في الترويج لخدماتنا. نقدّم عمولات مجزية وتتبع دقيق.') }}</p>
        </div>

        <!-- Quick Stats -->
        <div class="flex items-center gap-6 bg-white p-2 pr-6 rounded-[2rem] shadow-sm border border-slate-100">
            <div class="text-right">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('معدل التحويل') }}</div>
                <div class="text-lg font-black text-slate-900">2.4%</div>
            </div>
            <div class="w-px h-8 bg-slate-100"></div>
            <div class="text-right pl-4">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('النقرات') }}</div>
                <div class="text-lg font-black text-slate-900">124</div>
            </div>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        @foreach($links as $link)
        <div class="neo-card p-8 group hover:-translate-y-1 transition-transform duration-500">
            <div class="flex items-start justify-between mb-8">
                <!-- Logo & Title -->
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center p-3 group-hover:scale-105 transition-transform duration-500 shadow-sm">
                        <img src="{{ $link->logo_url }}" alt="{{ $link->service_name }}" class="w-full h-full object-contain mix-blend-multiply">
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-slate-900 tracking-tight">{{ $link->service_name }}</h4>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('عمولة') }} <span class="text-emerald-600">20%</span></span>
                        </div>
                    </div>
                </div>

                <!-- Action Badge -->
                <span class="bg-primary-50 text-primary-600 border border-primary-100/50 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">
                    {{ __('نشط') }}
                </span>
            </div>

            <!-- Tracking Link Field -->
            <div class="relative group/input">
                <div class="flex items-center bg-slate-50 border border-slate-100 rounded-[1.5rem] p-2 pr-6 focus-within:bg-white focus-within:border-primary-200 focus-within:ring-4 focus-within:ring-primary-500/10 transition-all duration-300">
                    <div class="flex-1 overflow-hidden">
                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">{{ __('رابط التتبع الحصري') }}</div>
                        <input type="text"
                            readonly
                            value="{{ $link->tracking_url }}"
                            class="w-full bg-transparent border-none p-0 text-sm font-bold text-slate-700 focus:ring-0 truncate font-mono"
                            dir="ltr">
                    </div>
                    <button onclick="navigator.clipboard.writeText('{{ $link->tracking_url }}');"
                        class="w-12 h-12 flex items-center justify-center bg-white border border-slate-200 rounded-2xl text-slate-400 hover:text-primary-600 hover:border-primary-200 hover:shadow-lg hover:shadow-primary-600/10 transition-all active:scale-95 shrink-0 ml-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Marketing Tip - Consistent Style -->
    <div class="bg-primary-50/50 border border-primary-100 rounded-[2.5rem] p-8 flex flex-col md:flex-row items-center gap-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-64 h-64 bg-white/40 rounded-full blur-3xl -ml-20 -mt-20"></div>

        <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-primary-600 shadow-sm relative z-10 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>

        <div class="flex-1 text-center md:text-right relative z-10">
            <h5 class="text-lg font-black text-slate-900 mb-2 tracking-tight">{{ __('نصيحة لزيادة الأرباح') }}</h5>
            <p class="text-sm font-medium text-slate-600 leading-relaxed max-w-2xl">
                {{ __('المسوقون الذين يستخدمون قنوات تواصل اجتماعي متعددة (تويتر، انستقرام، يوتيوب) يحققون مبيعات أعلى بنسبة 45%. قم بتنويع مصادر زياراتك.') }}
            </p>
        </div>

        <button class="px-6 py-3 bg-white border border-primary-100 text-primary-700 text-xs font-black rounded-xl hover:bg-primary-50 transition-colors shrink-0 uppercase tracking-widest relative z-10 shadow-sm">
            {{ __('أدوات التسويق') }}
        </button>
    </div>
</div>