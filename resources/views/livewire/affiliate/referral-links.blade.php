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

<div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
    <!-- Header -->
    <div class="mb-10 text-center lg:text-right">
        <h3 class="text-2xl font-black text-primary-900 mb-2">روابط الإحالة الذكية</h3>
        <p class="text-primary-500 text-sm font-medium">اختر الخدمة المناسبة لعميلك وشارك الرابط لتبدأ في جني الأرباح</p>
    </div>

    <!-- Links Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($links as $link)
        <div class="relative bg-gray-50/50 p-6 rounded-[2rem] border border-gray-100 group hover:bg-white hover:shadow-xl hover:shadow-primary-100/50 hover:border-primary-100 transition-all duration-500" x-data="{ copied: false }">
            @php
            $logo = $link->logo_url;
            $themeClass = 'text-primary-600';
            if (str_contains($link->service_name, 'قيود') || str_contains($link->service_name, 'Qoyod')) {
            $logo = asset('images/systems/qoyod.png');
            $themeClass = 'text-sky-600';
            } elseif (str_contains($link->service_name, 'دفترة') || str_contains($link->service_name, 'Daftra')) {
            $logo = asset('images/systems/daftra.png');
            $themeClass = 'text-emerald-600';
            }
            @endphp

            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-white rounded-2xl shadow-sm border border-gray-100 p-2 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                        @if($logo)
                        <img src="{{ $logo }}" alt="{{ $link->service_name }}" class="w-full h-full object-contain">
                        @else
                        <div class="w-full h-full bg-primary-50 text-primary-600 flex items-center justify-center font-black rounded-xl">
                            {{ substr($link->service_name, 0, 1) }}
                        </div>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-primary-900 leading-tight">{{ $link->service_name }}</h4>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">متاح الآن</span>
                        </div>
                    </div>
                </div>

                <div class="w-10 h-10 rounded-full border border-gray-100 flex items-center justify-center text-primary-300 group-hover:bg-primary-50 group-hover:text-primary-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </div>
            </div>

            <div class="space-y-4">
                <div class="relative">
                    <input type="text"
                        readonly
                        value="{{ $link->tracking_url }}"
                        class="w-full pl-4 pr-12 py-4 bg-white border border-gray-100 rounded-2xl text-xs font-bold text-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-100 transition-all cursor-default"
                        dir="ltr">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.823a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                </div>

                <button @click="
                        navigator.clipboard.writeText('{{ $link->tracking_url }}');
                        copied = true;
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'success', message: 'تم نسخ الرابط بنجاح!' }
                        }));
                        setTimeout(() => copied = false, 2000);
                    "
                    class="w-full py-4 rounded-2xl font-black text-sm transition-all flex items-center justify-center gap-2 group/btn relative overflow-hidden"
                    :class="copied ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' : 'bg-primary-900 text-white hover:bg-primary-800 shadow-xl shadow-primary-100'">
                    <span x-show="!copied" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                        </svg>
                        نسخ الرابط الذكي
                    </span>
                    <span x-show="copied" x-cloak class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        تم النسخ بنجاح
                    </span>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Professional Support Card -->
    <div class="mt-10 p-8 bg-gradient-to-r from-primary-50 to-white rounded-[2.5rem] border border-primary-100 flex flex-col md:flex-row items-center gap-6">
        <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-primary-100 flex items-center justify-center text-primary-600 flex-shrink-0">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="text-center md:text-right">
            <h5 class="text-lg font-black text-primary-900 mb-1">تلميحات كسب الأرباح</h5>
            <p class="text-primary-500 text-sm font-medium leading-relaxed">شارك هذه الروابط في منصات التواصل الاجتماعي أو مع عملائك المحتملين. كل عملية بيع ناجحة من خلال روابطك تضمن لك عمولة فورية تضاف لمحفظتك.</p>
        </div>
    </div>
</div>