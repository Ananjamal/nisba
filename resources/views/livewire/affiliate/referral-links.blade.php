<?php

use Livewire\Volt\Component;
use App\Models\ReferralLink;
use App\Models\UserReferral;
use Illuminate\Support\Str;

new class extends Component {
    public $showEditModal = false;
    public $editingReferralId = null;
    public $newCustomCode = '';
    public $editingLinkName = '';

    public function with()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user) return [];

        $links = ReferralLink::active()->get()->map(function ($link) use ($user) {
            $userRef = $user->referrals()->where('referral_link_id', $link->id)->first();

            if (!$userRef) {
                // Default to auto-generated link
                $uniqueId = strtolower(str_replace(' ', '', $link->service_name)) . '-' . $user->id . '-' . Str::random(4);
                $userRef = $user->referrals()->create([
                    'referral_link_id' => $link->id,
                    'unique_ref_id' => $uniqueId,
                ]);
            }

            $link->user_referral = $userRef;
            $link->tracking_url = route('referral.redirect', $userRef->unique_ref_id);
            return $link;
        });

        return [
            'links' => $links,
        ];
    }

    public function openEditModal($referralId, $linkName)
    {
        $referral = UserReferral::findOrFail($referralId);
        $this->editingReferralId = $referralId;
        $this->newCustomCode = $referral->unique_ref_id;
        $this->editingLinkName = $linkName;
        $this->showEditModal = true;
    }

    public function saveCustomCode()
    {
        $this->validate([
            'newCustomCode' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9\-_]+$/',
        ], [
            'newCustomCode.regex' => 'يجب أن يحتوي الكود على أحرف وأرقام وشرطات فقط',
        ]);

        $referral = UserReferral::findOrFail($this->editingReferralId);

        // Check if unique_ref_id is already taken
        $exists = UserReferral::where('unique_ref_id', $this->newCustomCode)
            ->where('id', '!=', $this->editingReferralId)
            ->exists();

        if ($exists) {
            $this->addError('newCustomCode', 'هذا الكود مستخدم بالفعل، يرجى اختيار كود آخر');
            return;
        }

        $referral->update([
            'unique_ref_id' => $this->newCustomCode,
        ]);

        $this->showEditModal = false;
        $this->dispatch('toast', type: 'success', message: 'تم تحديث كود الإحالة بنجاح');
    }

    public function regenerateAutoCode($referralId, $linkName)
    {
        $referral = UserReferral::findOrFail($referralId);
        $link = ReferralLink::find($referral->referral_link_id);

        $uniqueId = strtolower(str_replace(' ', '', $link->service_name)) . '-' . auth()->id() . '-' . Str::random(4);

        $referral->update([
            'unique_ref_id' => $uniqueId,
        ]);

        $this->dispatch('toast', type: 'success', message: 'تم إعادة توليد الكود تلقائياً');
    }
}; ?>

<div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
    <!-- Header -->
    <div class="mb-10 text-center lg:text-right flex flex-col lg:flex-row justify-between items-center gap-4">
        <div>
            <h3 class="text-2xl font-black text-primary-900 mb-2">روابط الإحالة الذكية</h3>
            <p class="text-primary-500 text-sm font-medium">اختر الخدمة المناسبة لعميلك وشارك الرابط لتبدأ في جني الأرباح</p>
        </div>
        <div class="flex items-center gap-2 bg-primary-50 px-4 py-2 rounded-2xl">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs font-black text-primary-700">التتبع المباشر نشط</span>
        </div>
    </div>

    <!-- Links Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($links as $link)
        <div class="relative bg-gray-50/50 p-6 rounded-[2rem] border border-gray-100 group hover:bg-white hover:shadow-xl hover:shadow-primary-100/50 hover:border-primary-100 transition-all duration-500" x-data="{ copied: false, showOptions: false }">
            @php
            $logo = $link->logo_url;
            if (str_contains($link->service_name, 'قيود') || str_contains($link->service_name, 'Qoyod')) {
            $logo = asset('images/systems/qoyod.png');
            } elseif (str_contains($link->service_name, 'دفترة') || str_contains($link->service_name, 'Daftra')) {
            $logo = asset('images/systems/daftra.png');
            }
            @endphp

            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-white rounded-2xl shadow-sm border border-gray-100 p-2 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                        @if($logo && (str_contains($logo, 'http') || file_exists(public_path(parse_url($logo, PHP_URL_PATH)))))
                        <img src="{{ $logo }}" alt="{{ $link->service_name }}" class="w-full h-full object-contain">
                        @else
                        <div class="w-full h-full bg-primary-50 text-primary-600 flex items-center justify-center font-black rounded-xl text-xl">
                            {{ substr($link->service_name, 0, 1) }}
                        </div>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-primary-900 leading-tight">{{ $link->service_name }}</h4>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">نوع الرابط: {{ $link->getLinkTypeLabel() }}</span>
                        </div>
                    </div>
                </div>

                <div class="relative" x-on:click.away="showOptions = false">
                    <button @click="showOptions = !showOptions" class="w-10 h-10 rounded-full border border-gray-100 flex items-center justify-center text-primary-300 hover:bg-primary-50 hover:text-primary-600 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                    </button>

                    <div x-show="showOptions" x-cloak class="absolute left-0 mt-2 w-48 bg-white border border-gray-100 rounded-2xl shadow-xl z-10 py-2">
                        <button wire:click="openEditModal({{ $link->user_referral->id }}, '{{ $link->service_name }}')" class="w-full text-right px-4 py-2 text-xs font-bold text-primary-600 hover:bg-primary-50 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            تعديل الكود يدوياً
                        </button>
                        <button wire:click="regenerateAutoCode({{ $link->user_referral->id }}, '{{ $link->service_name }}')" class="w-full text-right px-4 py-2 text-xs font-bold text-primary-600 hover:bg-primary-50 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            إعادة توليد تلقائي
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="relative group/input">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-primary-300 group-hover/input:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.823a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <input type="text" readonly value="{{ $link->tracking_url }}"
                        class="w-full pr-10 pl-4 py-4 bg-white border border-gray-100 rounded-2xl text-xs font-bold text-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-100 transition-all cursor-default text-left"
                        dir="ltr">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                        <span class="text-[10px] font-black text-primary-200 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100">ID: {{ $link->user_referral->unique_ref_id }}</span>
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
                    class="w-full py-4 rounded-2xl font-black text-sm transition-all flex items-center justify-center gap-2 group/btn relative overflow-hidden shadow-xl shadow-primary-100 hover:shadow-primary-200 active:scale-95"
                    :class="copied ? 'bg-emerald-500 text-white' : 'bg-primary-900 text-white hover:bg-primary-800'">
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

    <!-- Edit Modal -->
    @if($showEditModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-primary-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="$set('showEditModal', false)"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-primary-100">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-black text-primary-900">تعديل كود الإحالة</h3>
                        <button @click="$wire.set('showEditModal', false)" class="text-primary-300 hover:text-primary-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div class="p-4 bg-primary-50 rounded-2xl border border-primary-100 mb-6">
                            <p class="text-xs font-bold text-primary-700">الخدمة: {{ $editingLinkName }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-primary-900 mb-2">الكود المخصص</label>
                            <input type="text" wire:model="newCustomCode"
                                class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold text-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all text-left"
                                dir="ltr"
                                placeholder="مثلاً: my-custom-link">
                            @error('newCustomCode')
                            <p class="text-[10px] font-black text-rose-500 mt-2 mr-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button wire:click="saveCustomCode" class="flex-1 bg-primary-900 text-white font-black py-4 rounded-2xl hover:bg-primary-800 transition shadow-xl shadow-primary-100 active:scale-[0.98]">حفظ التغييرات</button>
                            <button @click="$wire.set('showEditModal', false)" class="px-8 bg-gray-50 text-primary-400 font-black py-4 rounded-2xl hover:bg-gray-100 transition border border-gray-100 active:scale-[0.98]">إلغاء</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Support Card -->
    <div class="mt-10 p-8 bg-gradient-to-r from-primary-50 to-white rounded-[2.5rem] border border-primary-100 flex flex-col md:flex-row items-center gap-6">
        <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-primary-100 flex items-center justify-center text-primary-600 flex-shrink-0">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="text-center md:text-right">
            <h5 class="text-lg font-black text-primary-900 mb-1">تلميحات كسب الأرباح</h5>
            <p class="text-primary-500 text-sm font-medium leading-relaxed">يمكنك تخصيص الكود الخاص بك ليكون أسهل في الحفظ والتذكر لعملائك. شارك الروابط في منصات التواصل الاجتماعي لزيادة فرصك في جنى العمولات.</p>
        </div>
    </div>
</div>