<?php

use Livewire\Volt\Component;
use App\Models\WithdrawalRequest;

new class extends Component {
    public $iban = '';
    public $bank_name = '';
    public $account_holder_name = '';
    public $showRequestModal = false;

    public function mount()
    {
        // Auto-fill bank information from user profile
        $user = auth()->user();
        $this->iban = $user->iban ?? '';
        $this->bank_name = $user->bank_name ?? '';
        $this->account_holder_name = $user->account_holder_name ?? '';
    }

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
            $this->dispatch('toast', type: 'error', message: 'ليس لديك عمولات كافية للصرف');
            return;
        }

        $withdrawalRequest = auth()->user()->withdrawalRequests()->create([
            'amount' => $amount,
            'iban' => $this->iban,
            'bank_name' => $this->bank_name,
            'account_holder_name' => $this->account_holder_name,
            'status' => 'pending',
        ]);

        // Notify all admin users
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\WithdrawalRequestNotification($withdrawalRequest));
        }

        $this->reset(['showRequestModal']);
        $this->dispatch('payout-requested');
        $this->dispatch('toast', type: 'success', message: 'تم تقديم طلب السحب بنجاح!');
    }
}; ?>

<div class="space-y-6" x-data="{ show: @entangle('showRequestModal') }" @payout-requested.window="show = false">
    <!-- Balance Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="section-title">الرصيد المتاح</h3>
            <p class="section-subtitle">الأرباح المتاحة للسحب</p>
        </div>
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-secondary mb-2">الرصيد المتاح للسحب</p>
                    <h2 class="text-4xl font-bold text-primary-900">
                        {{ number_format($stats->pending_commissions ?? 0, 2) }} <span class="text-lg font-normal text-secondary">ريس</span>
                    </h2>
                </div>
                <button @click="show = true"
                    @if(($stats->pending_commissions ?? 0) <= 0) disabled @endif
                        class="btn btn-primary">
                        تقديم طلب سحب
                </button>
            </div>
        </div>
    </div>

    @if(!auth()->user()->bank_account_verified_at)
    <div class="alert alert-info">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <p class="text-sm">لا يمكن تقديم طلب سحب حتى يتم التحقق من بيانات حسابك البنكي في الملف الشخصي.</p>
    </div>
    @endif

    <!-- Transactions History -->
    <div class="card">
        <div class="card-header">
            <h3 class="section-title">سجل طلبات الصرف</h3>
            <p class="section-subtitle">تاريخ جميع طلبات السحب</p>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>المبلغ</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th>الإثبات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $payout)
                    <tr class="animate-fade-in-up" style="animation-delay: {{ $loop->index * 100 }}ms">
                        <td>
                            <span class="font-semibold text-primary-900">{{ number_format($payout->amount, 2) }} ريس</span>
                        </td>
                        <td>{{ $payout->created_at->format('Y/m/d H:i') }}</td>
                        <td>
                            @if($payout->status == 'pending')
                            <span class="badge badge-pending">قيد المراجعة</span>
                            @elseif($payout->status == 'completed' || $payout->status == 'paid')
                            <span class="badge badge-active">تم الصرف</span>
                            @else
                            <span class="badge badge-rejected">مرفوض</span>
                            @endif
                        </td>
                        <td>
                            @if($payout->payment_proof_url)
                            <a href="{{ asset('storage/' . $payout->payment_proof_url) }}" target="_blank" class="text-primary-600 hover:text-primary-800 text-sm flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                عرض
                            </a>
                            @else
                            <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-12">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="empty-state-text">لا توجد طلبات سحب حالياً</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Withdrawal Modal -->
    <template x-teleport="body">
        <div x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 overflow-y-auto px-4 z-50" style="display: none;">
            <div class="flex items-center justify-center min-h-screen">
                <div @click="show = false" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

                <div class="bg-white rounded-2xl shadow-xl relative w-full max-w-lg"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-border-light flex items-center justify-between">
                        <h3 class="text-lg font-bold text-primary-900">طلب سحب عمولات</h3>
                        <button @click="show = false" class="text-secondary hover:text-primary-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit.prevent="requestPayout" class="p-6 space-y-4">
                        <div class="text-center p-4 bg-yellow-50 rounded-lg mb-4">
                            <p class="text-xs text-secondary mb-1">الرصيد المتاح للسحب</p>
                            <p class="text-3xl font-bold text-primary-900">{{ number_format($stats->pending_commissions ?? 0, 2) }} <span class="text-sm font-normal">ريس</span></p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">اسم صاحب الحساب</label>
                            <input type="text" wire:model="account_holder_name" class="form-input" placeholder="الاسم كما هو في البنك">
                            @error('account_holder_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">اسم البنك</label>
                            <input type="text" wire:model="bank_name" class="form-input" placeholder="مثال: مصرف الراجحي">
                            @error('bank_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">رقم الآيبان (IBAN)</label>
                            <input type="text" wire:model="iban" class="form-input font-mono" placeholder="SA..." dir="ltr">
                            @error('iban') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 pt-4">
                            <button type="button" @click="show = false" class="btn btn-outline">
                                إلغاء
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove>تأكيد وطلب السحب</span>
                                <span wire:loading>جاري المعالجة...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>