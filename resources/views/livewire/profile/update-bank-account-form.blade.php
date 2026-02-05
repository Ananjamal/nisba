<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $iban = '';
    public string $bank_name = '';
    public string $account_holder_name = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->iban = $user->iban ?? '';
        $this->bank_name = $user->bank_name ?? '';
        $this->account_holder_name = $user->account_holder_name ?? '';
    }

    /**
     * Update bank account information.
     */
    public function updateBankAccount(): void
    {
        $validated = $this->validate([
            'iban' => ['required', 'string', 'regex:/^SA[0-9]{22}$/'],
            'bank_name' => ['required', 'string', 'min:3', 'max:255'],
            'account_holder_name' => ['required', 'string', 'min:3', 'max:255'],
        ], [
            'iban.required' => 'رقم الآيبان مطلوب',
            'iban.regex' => 'رقم الآيبان يجب أن يبدأ بـ SA ويحتوي على 24 حرف',
            'bank_name.required' => 'اسم البنك مطلوب',
            'bank_name.min' => 'اسم البنك يجب أن يكون 3 أحرف على الأقل',
            'account_holder_name.required' => 'اسم صاحب الحساب مطلوب',
            'account_holder_name.min' => 'اسم صاحب الحساب يجب أن يكون 3 أحرف على الأقل',
        ]);

        $user = Auth::user();
        $user->update($validated);

        $this->dispatch('bank-updated');

    }
}; ?>

<section x-data @bank-updated.window="$dispatch('toast', {message: 'تم حفظ معلومات الحساب البنكي بنجاح!', type: 'success'})">
    @if(auth()->user()->bank_account_verified_at)
    <div class="alert alert-success mb-6">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <p class="font-bold">{{ __('حسابك البنكي موثق') }}</p>
            <p class="text-xs mt-1">{{ __('تم التحقق في') }} {{ auth()->user()->bank_account_verified_at->format('d/m/Y') }}</p>
        </div>
    </div>
    @else
    <div class="alert alert-warning mb-6">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <p class="text-sm font-semibold">{{ __('معلوماتك البنكية قيد المراجعة. سيتم التوثيق بعد التحقق من البيانات.') }}</p>
    </div>
    @endif

    <form wire:submit="updateBankAccount" class="space-y-6">
        <!-- Account Holder Name -->
        <div class="form-group">
            <label class="form-label">{{ __('اسم صاحب الحساب') }}</label>
            <input type="text" wire:model="account_holder_name" class="form-input" placeholder="{{ __('الاسم كما هو في البنك') }}">
            @error('account_holder_name') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Bank Name -->
        <div class="form-group">
            <label class="form-label">{{ __('اسم البنك') }}</label>
            <input type="text" wire:model="bank_name" class="form-input" placeholder="{{ __('مثال: مصرف الراجحي') }}">
            @error('bank_name') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- IBAN -->
        <div class="form-group">
            <label class="form-label">{{ __('رقم الآيبان (IBAN)') }}</label>
            <input type="text" wire:model="iban" class="form-input font-mono" placeholder="SA..." dir="ltr">
            @error('iban') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
            <p class="text-xs text-secondary mt-1">{{ __('يجب أن يبدأ بـ SA ويحتوي على 24 حرف') }}</p>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="btn btn-primary">
                <span wire:loading.remove>{{ __('حفظ المعلومات البنكية') }}</span>
                <span wire:loading>{{ __('جاري الحفظ...') }}</span>
            </button>
        </div>
    </form>
</section>