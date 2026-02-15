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
        $this->dispatch('open-modal', 'payout-management');
    }

    public function moveToReview()
    {
        if (!$this->activeRequestId) return;
        $request = WithdrawalRequest::findOrFail($this->activeRequestId);
        $request->update(['status' => 'under_review']);
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
            'activeRequest' => $this->activeRequestId ? WithdrawalRequest::with(['user', 'lead'])->find($this->activeRequestId) : null,
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
                            <div class="flex flex-col items-start px-2">
                                <span class="text-xs font-black text-gray-400 mb-0.5">المبلغ</span>
                                <div class="flex items-baseline gap-1">
                                    <span class="font-black text-primary-900 text-lg leading-none">{{ number_format($request->amount, 2) }}</span>
                                    <span class="text-[10px] text-primary-500 font-black">ر.س</span>
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
        <x-modal name="payout-management" :show="$activeRequestId !== null" maxWidth="xl" x-on:close="$wire.set('activeRequestId', null)">
            @if($activeRequest)
            <div class="bg-white rounded-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 p-6 flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-black text-gray-900">إدارة طلب السحب #{{ $activeRequest->id }}</h4>
                        <p class="text-xs font-bold text-gray-500 mt-1">المستفيد: {{ $activeRequest->user->name }}</p>
                    </div>
                    <button wire:click="$set('activeRequestId', null)" x-on:click="$dispatch('close-modal', 'payout-management')" class="text-gray-400 hover:text-gray-600 transition p-2 hover:bg-gray-100 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-8">
                    <!-- Basic Info Summary -->
                    <div class="grid grid-cols-2 gap-6 mb-8 bg-gray-50/50 p-4 rounded-2xl border border-gray-100">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">المسوق والعميل</p>
                            <div class="space-y-1">
                                <p class="text-sm font-bold text-gray-900">{{ $activeRequest->user->name }}</p>
                                <p class="text-xs font-bold text-primary-600">{{ $activeRequest->client_name ?: 'بدون عميل محدد' }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">البيانات البنكية والمبلغ</p>
                            <div class="space-y-1">
                                <p class="text-sm font-bold text-gray-900">{{ number_format($activeRequest->amount, 2) }} ر.س</p>
                                <p class="text-[10px] font-mono font-bold text-gray-500">{{ $activeRequest->iban }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Workflow Steps -->
                    <div class="relative flex items-center justify-between mb-10 px-4">
                        <div class="absolute left-0 right-0 h-1 bg-gray-100 top-5 -z-10 rounded-full">
                            <div class="h-full bg-green-500 rounded-full transition-all duration-500" style="width: {{ 
                                        match($activeRequest->status) {
                                            'pending' => '0%',
                                            'under_review' => '33%',
                                            'approved_finance' => '66%',
                                            'paid' => '100%',
                                            default => '0%'
                                        }
                                    }}"></div>
                        </div>

                        <!-- Step 1: Submission -->
                        <div class="flex flex-col items-center group">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-green-500 text-white shadow-lg shadow-green-200 ring-4 ring-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold mt-3 text-gray-600">تم الطلب</span>
                        </div>

                        <!-- Step 2: Review -->
                        <div class="flex flex-col items-center group">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 ring-4 ring-white {{ in_array($activeRequest->status, ['under_review', 'approved_finance', 'paid']) ? 'bg-green-500 text-white shadow-lg shadow-green-200' : 'bg-gray-50 border-2 border-gray-200 text-gray-400' }}">
                                @if(in_array($activeRequest->status, ['under_review', 'approved_finance', 'paid']))
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                @else
                                <span class="font-bold">2</span>
                                @endif
                            </div>
                            <span class="text-[10px] font-bold mt-3 {{ $activeRequest->status === 'under_review' ? 'text-primary-600' : 'text-gray-500' }}">المراجعة</span>
                        </div>

                        <!-- Step 3: Finance Approval -->
                        <div class="flex flex-col items-center group">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 ring-4 ring-white {{ in_array($activeRequest->status, ['approved_finance', 'paid']) ? 'bg-green-500 text-white shadow-lg shadow-green-200' : 'bg-gray-50 border-2 border-gray-200 text-gray-400' }}">
                                @if(in_array($activeRequest->status, ['approved_finance', 'paid']))
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                @else
                                <span class="font-bold">3</span>
                                @endif
                            </div>
                            <span class="text-[10px] font-bold mt-3 {{ $activeRequest->status === 'approved_finance' ? 'text-primary-600' : 'text-gray-500' }}">الاعتماد المالي</span>
                        </div>

                        <!-- Step 4: Payment -->
                        <div class="flex flex-col items-center group">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 ring-4 ring-white {{ $activeRequest->status === 'paid' ? 'bg-green-500 text-white shadow-lg shadow-green-200' : 'bg-gray-50 border-2 border-gray-200 text-gray-400' }}">
                                @if($activeRequest->status === 'paid')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                @else
                                <span class="font-bold">4</span>
                                @endif
                            </div>
                            <span class="text-[10px] font-bold mt-3 {{ $activeRequest->status === 'paid' ? 'text-primary-600' : 'text-gray-500' }}">تم التحويل</span>
                        </div>
                    </div>

                    @if($activeRequest->status === 'pending')
                    @can('finance approve withdrawals')
                    <div class="bg-gray-50/50 p-8 rounded-2xl border border-gray-100/50 text-center">
                        <p class="text-xs font-black text-gray-500 mb-6 uppercase tracking-widest">هل ترغب في بدء مراجعة هذا الطلب؟</p>
                        <button wire:click="moveToReview" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl transition shadow-lg shadow-blue-500/20 active:scale-[0.98]">بدء المراجعة والتدقيق</button>
                    </div>
                    @else
                    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 text-center text-gray-400 text-sm font-bold">بانتظار مراجعة المالية</div>
                    @endcan
                    @elseif($activeRequest->status === 'under_review')
                    @can('finance approve withdrawals')
                    <div class="bg-gray-50/30 p-8 rounded-2xl border border-gray-100 shadow-sm">
                        <p class="text-[10px] font-black text-gray-400 mb-6 text-center uppercase tracking-[0.2em]">بانتظار الاعتماد المالي</p>
                        <button wire:click="financeApprove" class="w-full btn btn-primary active:scale-[0.98]">
                            اعتماد البيانات المالية
                        </button>
                    </div>
                    @else
                    <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100 text-center text-gray-500 text-sm font-bold italic">هذا الطلب قيد المراجعة المالية حالياً</div>
                    @endcan
                    @elseif($activeRequest->status === 'approved_finance')
                    @can('admin approve withdrawals')
                    <div class="bg-gray-50/30 p-8 rounded-2xl border border-gray-100 shadow-sm space-y-6">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 mr-2 uppercase tracking-widest">صورة إثبات التحويل (Screenshot) <span class="text-rose-500">*</span></label>
                            <input type="file" wire:model="payment_proof" class="w-full p-2.5 bg-white border border-gray-100 rounded-xl text-xs file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition shadow-sm">
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 mr-2 uppercase tracking-widest">ملاحظات إضافية للمسوق</label>
                            <textarea wire:model="admin_notes" class="w-full p-4 bg-white border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition shadow-sm" placeholder="اختياري..."></textarea>
                            <button wire:click="approve" wire:loading.attr="disabled" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 rounded-xl transition shadow-xl shadow-emerald-500/30 active:scale-[0.98] disabled:opacity-50">
                                تأكيد الحوالة النهائية
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100 text-center text-primary-600 text-sm font-bold italic">تم اعتماد الطلب مالياً وبانتظار التحويل النهائي من قبل المدير</div>
                    @endcan
                    @endif

                    @if(in_array($activeRequest->status, ['pending', 'under_review', 'approved_finance']))
                    @can('reject withdrawals')
                    <div class="mt-8 pt-8 border-t border-gray-100">
                        <div class="space-y-3 mb-6">
                            <label class="text-xs font-black text-rose-500 mr-2">سبب الرفض (في حال الرفض)</label>
                            <textarea wire:model="rejection_reason" class="w-full p-4 bg-rose-50 border border-rose-100 rounded-xl text-sm focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition" placeholder="اكتب سبب الرفض هنا..."></textarea>
                            @error('rejection_reason') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex gap-4">
                            <button wire:click="reject" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-black py-4 rounded-xl transition shadow-lg shadow-rose-500/30 active:scale-[0.98]">رفض الطلب</button>
                            <button wire:click="$set('activeRequestId', null)" x-on:click="$dispatch('close-modal', 'payout-management')" class="px-8 bg-gray-100 text-gray-700 font-black py-4 rounded-xl hover:bg-gray-200 transition border border-gray-200 active:scale-[0.98]">إغلاق</button>
                        </div>
                    </div>
                    @else
                    <div class="mt-8 pt-8 border-t border-gray-100 flex justify-end">
                        <button wire:click="$set('activeRequestId', null)" x-on:click="$dispatch('close-modal', 'payout-management')" class="px-8 bg-gray-100 text-gray-700 font-black py-4 rounded-xl hover:bg-gray-200 transition border border-gray-200 active:scale-[0.98]">إغلاق</button>
                    </div>
                    @endcan
                    @endif
                </div>
            </div>
            @endif
        </x-modal>

    </div>
</div>