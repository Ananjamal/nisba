<?php

use Livewire\Volt\Component;
use App\Models\WithdrawalRequest;
use App\Models\UserStat;
use App\Notifications\GeneralNotification;

use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    use \Livewire\WithFileUploads;
    use App\Livewire\Traits\WithDynamicTable;

    public $payment_proof;
    public $admin_notes = '';
    public $rejection_reason = '';
    public $activeRequestId = null;
    public $delegated_to = '';
    public $notes = '';

    public function mount()
    {
        $this->loadTablePrefs([
            'marketer_client' => true,
            'bank_info' => true,
            'amount' => true,
            'status' => true,
            'attachments' => true,
            'actions' => true,
        ]);
    }

    public function selectRequest($id)
    {
        $this->activeRequestId = $id;
        $this->admin_notes = '';
        $this->rejection_reason = '';

        $request = WithdrawalRequest::find($id);
        if ($request) {
            $this->delegated_to = $request->delegated_to ?? '';
            $this->notes = $request->notes ?? '';
        }

        $this->dispatch('open-modal', 'payout-management');
    }

    public function delegateRequest()
    {
        if (!$this->activeRequestId) return;

        $request = WithdrawalRequest::findOrFail($this->activeRequestId);
        $request->update([
            'delegated_to' => $this->delegated_to ?: null,
            'notes' => $this->notes,
        ]);

        if ($this->delegated_to) {
            $staff = \App\Models\User::find($this->delegated_to);
            if ($staff) {
                // Log activity
                \App\Models\ActivityLog::log($request, 'withdrawal_delegated', "تم تفويض الطلب إلى: {$staff->name}", ['delegated_to' => $this->delegated_to]);

                // Notify staff
                $staff->notify(new GeneralNotification([
                    'title' => 'تم تفويض طلب سحب لك',
                    'message' => 'تم تفويض طلب سحب المبالغ الخاص بالعميل ' . $request->user->name . ' لمتابعتك.',
                    'type' => 'info'
                ]));
            }
        }

        $this->dispatch('toast', type: 'success', message: 'تم تحديث التفويض والملاحظات بنجاح');
    }

    public function moveToReview()
    {
        if (!$this->activeRequestId) return;
        $request = WithdrawalRequest::findOrFail($this->activeRequestId);
        $request->update(['status' => 'under_review']);

        // Log activity
        \App\Models\ActivityLog::log($request, 'status_changed', "تم بدء مراجعة طلب السحب", ['status' => 'under_review']);

        $this->reset(['activeRequestId']);
        $this->dispatch('close-modal', 'payout-management');
        $this->dispatch('payout-updated');
        $this->dispatch('toast', type: 'info', message: 'تم بدء عملية المراجعة بنجاح');
    }

    public function financeApprove()
    {
        if (!$this->activeRequestId) return;
        $request = WithdrawalRequest::findOrFail($this->activeRequestId);
        $request->update([
            'status' => 'approved_finance',
            'finance_approved_by' => auth()->id(),
            'finance_approved_at' => now(),
        ]);

        // Log activity
        \App\Models\ActivityLog::log($request, 'status_changed', "تم الاعتماد المالي لطلب السحب", ['status' => 'approved_finance']);

        $this->reset(['activeRequestId']);
        $this->dispatch('close-modal', 'payout-management');
        $this->dispatch('payout-updated');
        $this->dispatch('toast', type: 'success', message: 'تم الاعتماد المالي للطلب بنجاح');
    }

    public function reject()
    {
        if (!$this->activeRequestId) return;
        $this->validate(['rejection_reason' => 'required|min:5']);

        $request = WithdrawalRequest::findOrFail($this->activeRequestId);
        $request->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejection_reason,
        ]);

        // Log activity
        \App\Models\ActivityLog::log($request, 'status_changed', "تم رفض طلب السحب", ['status' => 'rejected', 'reason' => $this->rejection_reason]);

        $request->user->notify(new GeneralNotification([
            'title' => 'تم رفض طلب السحب',
            'message' => 'نعتذر، تم رفض طلب سحب مبلغ ' . $request->amount . ' ر.س. السبب: ' . $this->rejection_reason,
            'type' => 'error'
        ]));

        $this->reset(['activeRequestId', 'rejection_reason']);
        $this->dispatch('close-modal', 'payout-management');
        $this->dispatch('payout-updated');
        $this->dispatch('toast', type: 'error', message: 'تم رفض طلب السحب');
    }

    public function approve()
    {
        if (!$this->activeRequestId) return;

        $request = WithdrawalRequest::findOrFail($this->activeRequestId);

        $proofPath = null;
        if ($this->payment_proof) {
            $proofPath = $this->payment_proof->store('payout-proofs', 'public');
        }

        $request->update([
            'status' => 'paid',
            'payment_proof_url' => $proofPath,
            'admin_notes' => $this->admin_notes,
            'admin_approved_by' => auth()->id(),
            'admin_approved_at' => now(),
        ]);

        // Log activity
        \App\Models\ActivityLog::log($request, 'withdrawal_approved', "تم تأكيد الحوالة النهائية لطلب السحب", ['status' => 'paid']);

        // Update user stats
        $stats = UserStat::where('user_id', $request->user_id)->first();
        if ($stats) {
            $stats->decrement('pending_commissions', $request->amount);
        }

        $request->user->notify(new GeneralNotification([
            'title' => 'تم تحويل مستحقاتك!',
            'message' => 'تمت الموافقة النهائية على طلب سحب مبلغ ' . $request->amount . ' ر.س وإرفاق إثبات التحويل.',
            'type' => 'success'
        ]));

        $this->reset(['activeRequestId', 'payment_proof', 'admin_notes']);
        $this->dispatch('close-modal', 'payout-management');
        $this->dispatch('payout-approved');
        $this->dispatch('toast', type: 'success', message: 'تم تأكيد الحوالة النهائية بنجاح');
    }

    public function with()
    {
        return [
            'requests' => WithdrawalRequest::with(['user', 'lead'])
                ->when($this->status_filter, fn($q) => $q->where('status', $this->status_filter))
                ->when($this->date_from, fn($q) => $q->whereDate('created_at', '>=', $this->date_from))
                ->when($this->date_to, fn($q) => $q->whereDate('created_at', '<=', $this->date_to))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),
            'activeRequest' => $this->activeRequestId ? WithdrawalRequest::with(['user', 'lead', 'delegatedTo'])->find($this->activeRequestId) : null,
            'staffUsers' => \App\Models\User::role(['admin', 'super-admin', 'employee'])->get(),
        ];
    }
}; ?>

<div class="space-y-8">
    @if (session()->has('message'))
    <div class="p-4 text-sm text-green-700 bg-green-100 rounded-2xl font-bold border border-green-200" role="alert">
        {{ session('message') }}
    </div>
    @endif

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <x-table.filter-bar :statusOptions="[
            'pending' => 'بانتظار المراجعة', 
            'under_review' => 'تحت المراجعة (مالية)', 
            'approved_finance' => 'بانتظار الدفع النهائي', 
            'paid' => 'تم الدفع', 
            'rejected' => 'مرفوض'
        ]">
            <x-slot name="actions">
                <div class="flex gap-2">
                    <x-table.column-toggler :columns="$columns" :labels="[
                    'marketer_client' => 'المسوق',
                    'bank_info' => 'البيانات البنكية',
                    'amount' => 'المبلغ',
                    'status' => 'الحالة',
                    'attachments' => 'المرفقات',
                    'actions' => 'العمليات'
                ]" />
                    <a href="{{ route('admin.reports.payouts.excel', ['status' => $status_filter, 'date_from' => $date_from, 'date_to' => $date_to]) }}"
                        target="_blank"
                        class="group flex items-center justify-center p-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all duration-300 shadow-sm"
                        title="تصدير Excel">
                        <svg class="w-5 h-5 text-green-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                    <a href="{{ route('admin.reports.payouts.pdf', ['status' => $status_filter, 'date_from' => $date_from, 'date_to' => $date_to]) }}"
                        target="_blank"
                        class="group flex items-center justify-center p-2.5 bg-white border border-gray-200 rounded-xl hover:border-red-500 hover:bg-red-50 transition-all duration-300 shadow-sm"
                        title="تصدير PDF">
                        <svg class="w-5 h-5 text-red-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </a>
                </div>
            </x-slot>
        </x-table.filter-bar>

        <div class="overflow-x-auto mt-6">
            <table class="w-full text-right">
                <thead>
                    <tr class="text-primary-400 text-sm border-b border-primary-50">
                        @if ($columns['marketer_client'])
                        <x-table.th field="user_id" :sortField="$sortField" :sortDirection="$sortDirection" label="المسوق" />
                        @endif
                        @if ($columns['bank_info'])
                        <x-table.th field="bank_name" :sortField="$sortField" :sortDirection="$sortDirection" label="البيانات البنكية" />
                        @endif
                        @if ($columns['amount'])
                        <x-table.th field="amount" :sortField="$sortField" :sortDirection="$sortDirection" label="المبلغ" />
                        @endif
                        @if ($columns['status'])
                        <x-table.th field="status" :sortField="$sortField" :sortDirection="$sortDirection" label="الحالة" />
                        @endif
                        @if ($columns['attachments'])
                        <th class="pb-4 font-black text-right text-primary-400 uppercase tracking-widest text-[10px]">المرفقات</th>
                        @endif
                        @if ($columns['actions'])
                        <th class="pb-4 font-black text-left text-primary-400 uppercase tracking-widest text-[10px]">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($requests as $request)
                    <tr wire:key="payout-row-{{ $request->id }}" class="group hover:bg-gray-50 transition-all duration-300 border-b border-gray-50 last:border-0 {{ $activeRequestId == $request->id ? 'bg-primary-50/30' : '' }}">
                        @if ($columns['marketer_client'])
                        <td class="py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600 font-bold overflow-hidden transition-transform group-hover:scale-105">
                                    {{ substr($request->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.affiliates.show', $request->user_id) }}" class="font-black text-primary-900 hover:text-primary-600 transition-colors block leading-tight">{{ $request->user->name }}</a>
                                    <p class="text-[10px] text-primary-400 font-bold uppercase tracking-wider">{{ $request->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        @endif

                        @if ($columns['bank_info'])
                        <td class="py-5 px-3">
                            <div class="flex flex-col items-start gap-1">
                                <span class="font-bold text-gray-900 text-sm leading-tight">{{ $request->bank_name }}</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">{{ $request->account_holder_name }}</span>

                                <div x-data="{ copied: false }" class="flex items-center justify-between gap-2 bg-gray-50 px-2 py-1.5 rounded-lg border border-gray-100 group/iban hover:border-primary-100 transition-colors w-full">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="text-[9px] font-black text-gray-400 shrink-0 select-none">IBAN</span>
                                        <span class="font-mono text-[10px] font-bold text-gray-600 tracking-tight truncate" dir="ltr">{{ $request->iban }}</span>
                                    </div>
                                    <button
                                        @click="
                                            navigator.clipboard.writeText('{{ $request->iban }}');
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                            $dispatch('toast', {type: 'success', message: 'تم نسخ الآيبان'})
                                        "
                                        class="text-gray-400 hover:text-primary-600 transition-colors shrink-0"
                                        title="نسخ">
                                        <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                        </svg>
                                        <svg x-show="copied" style="display: none;" class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        @endif

                        @if ($columns['amount'])
                        <td class="py-5">
                            <div class="flex flex-col items-start px-2 space-y-1">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">الإجمالي</span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="font-black text-gray-500 text-sm leading-none">{{ number_format($request->amount, 2) }}</span>
                                        <span class="text-[9px] text-gray-400 font-bold">ر.س</span>
                                    </div>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-rose-400 uppercase tracking-tighter">الضريبة</span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="font-black text-rose-500 text-sm leading-none">{{ number_format($request->tax_amount, 2) }}</span>
                                        <span class="text-[9px] text-rose-400 font-bold">ر.س</span>
                                    </div>
                                </div>
                                <div class="flex flex-col pt-1 border-t border-gray-100 min-w-[80px]">
                                    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-tighter">الصافي</span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="font-black text-emerald-600 text-base leading-none">{{ number_format($request->final_amount, 2) }}</span>
                                        <span class="text-[9px] text-emerald-500 font-black">ر.س</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        @endif

                        @if ($columns['status'])
                        <td class="py-5">
                            @php
                            $statusConfig = match ($request->status) {
                            'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100', 'icon' => '⏳', 'label' => 'بانتظار المراجعة'],
                            'under_review' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-100', 'icon' => '🔍', 'label' => 'تحت المراجعة'],
                            'approved_finance' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-100', 'icon' => '💳', 'label' => 'معتمد مالياً'],
                            'paid' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'icon' => '✅', 'label' => 'تم الدفع'],
                            'rejected' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-100', 'icon' => '❌', 'label' => 'مرفوض'],
                            default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'border' => 'border-gray-100', 'icon' => '?', 'label' => $request->status],
                            };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-black border shadow-sm {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} {{ $statusConfig['border'] }}">
                                <span>{{ $statusConfig['icon'] }}</span>
                                <span>{{ $statusConfig['label'] }}</span>
                            </span>
                        </td>
                        @endif

                        @if ($columns['attachments'])
                        <td class="py-5">
                            <div class="flex items-center gap-2">
                                @if ($request->invoice_url)
                                <a href="{{ Storage::url($request->invoice_url) }}" target="_blank"
                                    class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center text-primary-600 hover:bg-primary-600 hover:text-white transition-all shadow-sm group/att"
                                    title="الفاتورة">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </a>
                                @endif
                                @if ($request->iban_proof_url)
                                <a href="{{ Storage::url($request->iban_proof_url) }}" target="_blank"
                                    class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm group/att"
                                    title="إثبات الآيبان">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </a>
                                @endif
                                @if ($request->payment_proof_url)
                                <a href="{{ Storage::url($request->payment_proof_url) }}" target="_blank"
                                    class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm group/att"
                                    title="إثبات الدفع">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </td>
                        @endif

                        @if ($columns['actions'])
                        <td class="py-4 text-left whitespace-nowrap px-4">
                            @if (in_array($request->status, ['pending', 'under_review', 'approved_finance']))
                            <button wire:click="selectRequest({{ $request->id }})"
                                x-on:click="$dispatch('open-modal', 'payout-management')"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[11px] font-bold transition-all duration-300 relative group bg-blue-600 text-white hover:bg-blue-700 shadow-md shadow-blue-100 active:scale-95">
                                <span class="tracking-wide">إدارة الطلب</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </button>
                            @else
                            <div class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-50 border border-gray-100 text-gray-400 opacity-50 cursor-not-allowed mx-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-lg font-bold text-gray-900">لا توجد طلبات سحب</p>
                                <p class="text-sm text-gray-500 mt-1"> لم يتم تقديم أي طلبات سحب حتى الآن</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $requests->links() }}
        </div>

        <!-- Payout Management Modal (Executive Redesign) -->
        <x-modal name="payout-management" :show="$activeRequestId !== null" maxWidth="2xl" x-on:close="$wire.set('activeRequestId', null)">
            @if($activeRequest)
            <div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-gray-100 text-right" dir="rtl">

                <div class="relative p-8 text-white" style="background: linear-gradient(135deg, #064e3b 0%, #059669 100%);">
                    <div class="flex justify-between items-start relative z-10">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="bg-white/20 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-white/30 backdrop-blur-md">PAYOUT SYSTEM</span>
                                <span class="text-emerald-200 font-mono text-sm mr-2 opacity-80">#{{ $activeRequest->id }}</span>
                            </div>
                            <h4 class="text-2xl font-black tracking-tight text-white">إدارة طلب السحب</h4>
                            <p class="text-emerald-100/80 text-sm mt-1 font-medium">مراجعة البيانات المالية واعتماد الحوالة للمسوق</p>
                        </div>
                        <button wire:click="$set('activeRequestId', null)" x-on:click="$dispatch('close-modal', 'payout-management')"
                            class="bg-white/10 hover:bg-white/20 text-white p-2.5 rounded-xl transition-all border border-white/10 shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-white/5 rounded-full blur-3xl"></div>
                </div>

                <div class="p-8 -mt-6 bg-white rounded-t-[32px] relative z-20">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 group hover:border-emerald-200 transition-all duration-300 shadow-sm">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="p-2 bg-emerald-600 text-white rounded-lg shadow-md shadow-emerald-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">المستفيد</span>
                            </div>
                            <div class="space-y-1">
                                <p class="text-base font-black text-gray-900">{{ $activeRequest->user->name }}</p>
                                <p class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $activeRequest->client_name ?: 'بدون عميل محدد' }}
                                </p>
                            </div>
                        </div>

                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 group hover:border-emerald-200 transition-all duration-300 shadow-sm">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="p-2 bg-emerald-600 text-white rounded-lg shadow-md shadow-emerald-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">تفاصيل المبلغ</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500">المبلغ الإجمالي:</span>
                                    <span class="text-sm font-black text-gray-900">{{ number_format($activeRequest->amount, 2) }} ر.س</span>
                                </div>
                                @if($activeRequest->tax_amount > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-rose-500">الضريبة ({{ $activeRequest->tax_rate }}%):</span>
                                    <span class="text-sm font-black text-rose-500">- {{ number_format($activeRequest->tax_amount, 2) }} ر.س</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                    <span class="text-xs font-black text-emerald-700">الصافي للتحويل:</span>
                                    <span class="text-lg font-black text-emerald-700">{{ number_format($activeRequest->final_amount, 2) }} ر.س</span>
                                </div>
                                @else
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                    <span class="text-xs font-black text-emerald-700">المبلغ النهائي:</span>
                                    <span class="text-lg font-black text-emerald-700">{{ number_format($activeRequest->amount, 2) }} ر.س</span>
                                </div>
                                @endif
                                <p class="text-[10px] font-mono font-bold text-gray-500 bg-white px-2 py-1 rounded border border-gray-100 mt-2 block text-left" dir="ltr">{{ $activeRequest->iban }}</p>
                            </div>
                        </div>
                    </div>

                    @if($activeRequest->status === 'pending')
                    <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 text-center mb-6">
                        <div class="w-16 h-16 bg-white rounded-full shadow-inner flex items-center justify-center mx-auto mb-4 border-4 border-emerald-50 text-emerald-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h5 class="text-gray-900 font-black text-lg">بدء المراجعة والتدقيق</h5>
                        <p class="text-gray-500 text-xs mb-6 px-10">سيتم إخطار المسوق بأن طلبه قيد المراجعة المالية حالياً</p>
                        @can('finance approve withdrawals')
                        <button wire:click="moveToReview" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-emerald-200 active:scale-[0.98]">
                            تأكيد البدء في المراجعة
                        </button>
                        @else
                        <div class="text-amber-600 text-sm font-bold bg-amber-50 p-4 rounded-xl">بانتظار مراجعة المالية</div>
                        @endcan
                    </div>
                    @elseif($activeRequest->status === 'under_review')
                    <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 text-center mb-6">
                        <div class="w-16 h-16 bg-white rounded-full shadow-inner flex items-center justify-center mx-auto mb-4 border-4 border-blue-50 text-blue-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h5 class="text-gray-900 font-black text-lg">الاعتماد المالي</h5>
                        <p class="text-gray-500 text-xs mb-6 px-10">مراجعة البيانات المالية واعتمادها للتحويل النهائي</p>
                        @can('finance approve withdrawals')
                        <button wire:click="financeApprove" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-blue-200 active:scale-[0.98]">
                            اعتماد البيانات المالية
                        </button>
                        @else
                        <div class="text-blue-600 text-sm font-bold bg-blue-50 p-4 rounded-xl">الطلب قيد المراجعة المالية حالياً</div>
                        @endcan
                    </div>
                    @elseif($activeRequest->status === 'approved_finance')
                    <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 mb-6">
                        <h5 class="text-gray-900 font-black text-lg text-center mb-6">تأكيد الحوالة النهائية</h5>
                        @can('admin approve withdrawals')
                        <div class="space-y-6">
                            <div class="space-y-3">
                                <label class="text-[11px] font-black text-gray-400 mr-2 uppercase">صورة إثبات التحويل <span class="text-red-500">*</span></label>
                                <input type="file" wire:model="payment_proof" class="w-full p-2.5 bg-white border border-gray-100 rounded-xl text-xs file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition shadow-sm">
                            </div>
                            <div class="space-y-3">
                                <label class="text-[11px] font-black text-gray-400 mr-2 uppercase">ملاحظات للمسوق</label>
                                <textarea wire:model="admin_notes" class="w-full p-4 bg-white border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="اختياري..."></textarea>
                                <button wire:click="approve" wire:loading.attr="disabled" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-emerald-200 active:scale-[0.98] disabled:opacity-50">
                                    <span wire:loading.remove>إتمام عملية التحويل</span>
                                    <span wire:loading>جاري التحميل...</span>
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="text-emerald-600 text-sm font-bold bg-emerald-50 p-4 rounded-xl text-center">تم الاعتماد المالي وبانتظار التحويل النهائي من المدير</div>
                        @endcan
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="space-y-1">
                            <label class="text-[11px] font-black text-gray-400 mr-2 uppercase">التفويض</label>
                            <select wire:model="delegated_to" wire:change="delegateRequest" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none">
                                <option value="">غير مفوض</option>
                                @foreach($staffUsers as $staff) <option value="{{ $staff->id }}">{{ $staff->name }}</option> @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-black text-gray-400 mr-2 uppercase">ملاحظات داخلية</label>
                            <textarea wire:model.blur="notes" wire:change="delegateRequest" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none min-h-[46px] resize-none" placeholder="ملاحظات النظام..."></textarea>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-4">
                        @if(in_array($activeRequest->status, ['pending', 'under_review', 'approved_finance']))
                        @can('reject withdrawals')
                        <div x-data="{ open: false }" class="border-t border-gray-100 pt-6">
                            <button @click="open = !open" class="text-red-500 text-xs font-black flex items-center gap-2 hover:bg-red-50 px-3 py-2 rounded-lg transition-colors">
                                <span class="text-base" x-text="open ? '−' : '+'"></span>
                                منطقة الرفض وإلغاء الطلب
                            </button>
                            <div x-show="open" x-collapse class="mt-4 bg-red-50 p-5 rounded-2xl border border-red-100">
                                <textarea wire:model="rejection_reason" class="w-full p-4 bg-white border border-red-200 rounded-xl text-sm focus:ring-2 focus:ring-red-500 outline-none mb-4" placeholder="اكتب سبب الرفض هنا..."></textarea>
                                @error('rejection_reason') <p class="text-red-500 text-xs mb-4 font-bold">{{ $message }}</p> @enderror
                                <button wire:click="reject" class="w-full bg-red-600 text-white font-black py-3.5 rounded-xl hover:bg-red-700 transition shadow-lg shadow-red-200">تأكيد رفض الطلب</button>
                            </div>
                        </div>
                        @endcan
                        @endif

                        <button wire:click="$set('activeRequestId', null)" x-on:click="$dispatch('close-modal', 'payout-management')"
                            class="w-full text-white-500 font-bold text-sm py-3 hover:bg-gray-100 rounded-xl transition-colors btn btn-primary">
                            إغلاق النافذة
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </x-modal>

    </div>
</div>