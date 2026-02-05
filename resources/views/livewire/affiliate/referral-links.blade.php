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

<div class="card">
    <!-- Header -->
    <div class="card-header">
        <h3 class="section-title">روابط الإحالة الخاصة بك</h3>
        <p class="section-subtitle">شارك هذه الروابط لتبدأ في كسب العمولات</p>
    </div>

    <!-- Links -->
    <div class="card-body space-y-4">
        @foreach($links as $link)
        <div class="p-4 bg-bg-main rounded-lg" x-data="{ copied: false }">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    @php
                    $logo = $link->logo_url;
                    if (str_contains($link->service_name, 'قيود') || str_contains($link->service_name, 'Qoyod')) {
                    $logo = asset('images/systems/qoyod.png');
                    } elseif (str_contains($link->service_name, 'دفترة') || str_contains($link->service_name, 'Daftra')) {
                    $logo = asset('images/systems/daftra.png');
                    }
                    @endphp

                    @if($logo)
                    <img src="{{ $logo }}" alt="{{ $link->service_name }}" class="h-8 object-contain">
                    @endif
                    <span class="font-semibold text-primary-900">{{ $link->service_name }}</span>
                </div>
                <span class="text-xs text-secondary">نشط</span>
            </div>

            <div class="flex items-center gap-2">
                <input type="text"
                    readonly
                    value="{{ $link->tracking_url }}"
                    class="form-input flex-1 text-sm font-mono"
                    dir="ltr">
                <button @click="
                        navigator.clipboard.writeText('{{ $link->tracking_url }}');
                        copied = true;
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                type: 'success',
                                message: 'تم نسخ الرابط بنجاح!'
                            }
                        }));
                        setTimeout(() => copied = false, 2000);
                    "
                    class="copy-btn"
                    :class="{ '!bg-green-600': copied }">
                    <span x-show="!copied">نسخ</span>
                    <span x-show="copied" x-cloak>✓</span>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Info Alert -->
    <div class="mx-6 mb-6">
        <div class="alert alert-info">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm">شارك هذه الروابط مع عملائك المحتملين لتبدأ في كسب العمولات. كل عملية بيع ناجحة من خلال روابطك ستحصل على عمولة منها.</p>
        </div>
    </div>
</div>