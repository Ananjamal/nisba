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
    }

    public function moveToReview()
    {
        if (!$this->activeRequestId) return;
        $request = WithdrawalRequest::findOrFail($this->activeRequestId);
        $request->update(['status' => 'under_review']);
        $this->reset(['activeRequestId']);
        $this->dispatch('payout-updated');
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
        $this->dispatch('payout-updated');
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
        $this->dispatch('payout-updated');
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
        $this->dispatch('payout-approved');
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
                    'marketer_client' => 'المسوق / العميل',
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
                        @if($columns['marketer_client'])
                        <th class="pb-4 font-bold text-left text-gray-600">المسوق / العميل</th>
                        @endif
                        @if($columns['bank_info'])
                        <x-table.th field="bank_name" :sortField="$sortField" :sortDirection="$sortDirection" label="البيانات البنكية" />
                        @endif
                        @if($columns['amount'])
                        <x-table.th field="amount" :sortField="$sortField" :sortDirection="$sortDirection" label="المبلغ" />
                        @endif
                        @if($columns['status'])
                        <x-table.th field="status" :sortField="$sortField" :sortDirection="$sortDirection" label="الحالة" />
                        @endif
                        @if($columns['attachments'])
                        <th class="pb-4 font-bold text-left text-gray-600">المرفقات</th>
                        @endif
                        @if($columns['actions'])
                        <th class="pb-4 font-bold text-left text-gray-600">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($requests as $request)
                    <tr class="group hover:bg-gray-50 transition-colors duration-200">
                        @if($columns['marketer_client'])
                        <td class="py-4">
                            <div class="space-y-1">
                                <div>
                                    <a href="{{ route('admin.affiliates.show', $request->user_id) }}" class="font-bold text-gray-900 hover:text-primary-600 transition">{{ $request->user->name }}</a>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ $request->user->email }}</p>
                                </div>
                                <div class="pt-2 border-t border-gray-100">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">العميل</p>
                                    <a href="{{ route('admin.leads.show', $request->lead_id ?? 0) }}" class="text-xs font-bold text-gray-700 hover:text-primary-900 transition block">
                                        {{ $request->client_name ?: 'غير محدد' }}
                                    </a>
                                    <p class="text-[10px] text-gray-500 font-bold">{{ $request->company_name }}</p>
                                </div>
                            </div>
                        </td>
                        @endif
                        @if($columns['bank_info'])
                        <td class="py-4">
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-900 font-mono tracking-tight">{{ $request->iban }}</p>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                    <p class="text-[10px] text-gray-500 font-bold">{{ $request->bank_name }}</p>
                                </div>
                                <p class="text-[10px] text-gray-400 font-bold">{{ $request->account_holder_name }}</p>
                            </div>
                        </td>
                        @endif
                        @if($columns['amount'])
                        <td class="py-4">
                            <span class="font-black text-gray-900 text-base">{{ number_format($request->amount, 2) }}</span>
                            <span class="text-[10px] text-gray-500 font-bold">ر.س</span>
                        </td>
                        @endif
                        @if($columns['status'])
                        <td class="py-4">
                            @php
                            $statusClasses = match($request->status) {
                            'pending' => 'bg-amber-50 text-amber-600 border border-amber-100',
                            'under_review' => 'bg-blue-50 text-blue-600 border border-blue-100',
                            'approved_finance' => 'bg-indigo-50 text-indigo-600 border border-indigo-100',
                            'paid' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                            'rejected' => 'bg-rose-50 text-rose-600 border border-rose-100',
                            default => 'bg-gray-50 text-gray-600 border border-gray-100'
                            };
                            $statusLabel = match($request->status) {
                            'pending' => 'بانتظار المراجعة',
                            'under_review' => 'تحت المراجعة',
                            'approved_finance' => 'معتمد مالياً',
                            'paid' => 'تم الدفع',
                            'rejected' => 'مرفوض',
                            default => $request->status
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        @endif
                        @if($columns['attachments'])
                        <td class="py-4">
                            <div class="flex flex-col gap-2">
                                @if($request->invoice_url)
                                <a href="{{ Storage::url($request->invoice_url) }}" target="_blank" class="text-gray-600 hover:text-primary-600 hover:bg-primary-50 px-2 py-1 rounded-lg transition-all text-[10px] font-bold flex items-center gap-1.5 w-max">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    الفاتورة
                                </a>
                                @endif
                                @if($request->iban_proof_url)
                                <a href="{{ Storage::url($request->iban_proof_url) }}" target="_blank" class="text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 px-2 py-1 rounded-lg transition-all text-[10px] font-bold flex items-center gap-1.5 w-max">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    إثبات الآيبان
                                </a>
                                @endif
                                @if($request->payment_proof_url)
                                <a href="{{ Storage::url($request->payment_proof_url) }}" target="_blank" class="text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded-lg transition-all text-[10px] font-bold flex items-center gap-1.5 w-max">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    إثبات الدفع
                                </a>
                                @endif
                            </div>
                        </td>
                        @endif
                        @if($columns['actions'])
                        <td class="py-4">
                            @if(in_array($request->status, ['pending', 'under_review', 'approved_finance']))
                            <button wire:click="selectRequest({{ $request->id }})" class="bg-primary-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary-700 transition-all shadow-md shadow-primary-500/20 active:scale-95">
                                إدارة الطلب
                            </button>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @if($activeRequestId == $request->id)
                    <tr class="bg-gray-50/50">
                        <td colspan="10" class="p-6">
                            <div class="bg-white rounded-2xl border border-gray-100 shadow-xl max-w-3xl mx-auto overflow-hidden">
                                <div class="bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 p-6 flex items-center justify-between">
                                    <div>
                                        <h4 class="text-lg font-black text-gray-900">إدارة طلب السحب #{{ $request->id }}</h4>
                                        <p class="text-xs font-bold text-gray-500 mt-1">المستفيد: {{ $request->user->name }}</p>
                                    </div>
                                    <button wire:click="$set('activeRequestId', null)" class="text-gray-400 hover:text-gray-600 transition p-2 hover:bg-gray-100 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="p-8">
                                    <!-- Workflow Steps -->
                                    <div class="relative flex items-center justify-between mb-10 px-4">
                                        <div class="absolute left-0 right-0 h-1 bg-gray-100 top-5 -z-10 rounded-full">
                                            <div class="h-full bg-green-500 rounded-full transition-all duration-500" style="width: {{ 
                                                match($request->status) {
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
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 ring-4 ring-white {{ in_array($request->status, ['under_review', 'approved_finance', 'paid']) ? 'bg-green-500 text-white shadow-lg shadow-green-200' : 'bg-gray-50 border-2 border-gray-200 text-gray-400' }}">
                                                @if(in_array($request->status, ['under_review', 'approved_finance', 'paid']))
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                @else
                                                <span class="font-bold">2</span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] font-bold mt-3 {{ $request->status === 'under_review' ? 'text-primary-600' : 'text-gray-500' }}">المراجعة</span>
                                        </div>

                                        <!-- Step 3: Finance Approval -->
                                        <div class="flex flex-col items-center group">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 ring-4 ring-white {{ in_array($request->status, ['approved_finance', 'paid']) ? 'bg-green-500 text-white shadow-lg shadow-green-200' : 'bg-gray-50 border-2 border-gray-200 text-gray-400' }}">
                                                @if(in_array($request->status, ['approved_finance', 'paid']))
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                @else
                                                <span class="font-bold">3</span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] font-bold mt-3 {{ $request->status === 'approved_finance' ? 'text-primary-600' : 'text-gray-500' }}">الاعتماد المالي</span>
                                        </div>

                                        <!-- Step 4: Payment -->
                                        <div class="flex flex-col items-center group">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 ring-4 ring-white {{ $request->status === 'paid' ? 'bg-green-500 text-white shadow-lg shadow-green-200' : 'bg-gray-50 border-2 border-gray-200 text-gray-400' }}">
                                                @if($request->status === 'paid')
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                @else
                                                <span class="font-bold">4</span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] font-bold mt-3 {{ $request->status === 'paid' ? 'text-primary-600' : 'text-gray-500' }}">تم التحويل</span>
                                        </div>
                                    </div>

                                    @if($request->status === 'pending')
                                    @can('finance approve withdrawals')
                                    <div class="bg-gray-50 p-8 rounded-2xl border border-gray-100 text-center">
                                        <p class="text-sm font-bold text-gray-700 mb-6">هل ترغب في بدء مراجعة هذا الطلب؟</p>
                                        <button wire:click="moveToReview" class="btn btn-primary w-full shadow-lg shadow-primary-500/20">بدء المراجعة والتدقيق</button>
                                    </div>
                                    @else
                                    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 text-center text-gray-400 text-sm font-bold">بانتظار مراجعة المالية</div>
                                    @endcan
                                    @elseif($request->status === 'under_review')
                                    @can('finance approve withdrawals')
                                    <div class="bg-amber-50 p-8 rounded-2xl border border-amber-100">
                                        <p class="text-sm font-bold text-amber-800 mb-6 text-center">بانتظار الاعتماد المالي</p>
                                        <button wire:click="financeApprove" class="btn bg-amber-500 hover:bg-amber-600 text-white w-full font-bold py-3.5 rounded-xl transition shadow-lg shadow-amber-500/20 border-0">اعتماد البيانات المالية</button>
                                    </div>
                                    @else
                                    <div class="bg-amber-50 p-6 rounded-2xl border border-amber-100 text-center text-amber-600 text-sm font-bold italic">هذا الطلب قيد المراجعة المالية حالياً</div>
                                    @endcan
                                    @elseif($request->status === 'approved_finance')
                                    @can('admin approve withdrawals')
                                    <div class="bg-indigo-50 p-8 rounded-2xl border border-indigo-100 space-y-6">
                                        <div class="space-y-2">
                                            <label class="text-xs font-black text-indigo-900 mr-2">صورة إثبات التحويل (Screenshot) <span class="text-red-500">*</span></label>
                                            <input type="file" wire:model="payment_proof" class="w-full p-2 bg-white border border-indigo-200 rounded-xl text-xs file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-xs font-black text-indigo-900 mr-2">ملاحظات إضافية للمسوق</label>
                                            <textarea wire:model="admin_notes" class="w-full p-4 bg-white border border-indigo-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" placeholder="اختياري..."></textarea>
                                        </div>
                                        <button wire:click="approve" wire:loading.attr="disabled" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl transition shadow-lg shadow-indigo-500/20">
                                            تأكيد الحوالة النهائية
                                        </button>
                                    </div>
                                    @else
                                    <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 text-center text-indigo-600 text-sm font-bold italic">تم اعتماد الطلب مالياً وبانتظار التحويل النهائي من قبل المدير</div>
                                    @endcan
                                    @endif

                                    @if(in_array($request->status, ['pending', 'under_review', 'approved_finance']))
                                    @can('reject withdrawals')
                                    <div class="mt-8 pt-8 border-t border-gray-100">
                                        <div class="space-y-3 mb-6">
                                            <label class="text-xs font-black text-rose-500 mr-2">سبب الرفض (في حال الرفض)</label>
                                            <textarea wire:model="rejection_reason" class="w-full p-4 bg-rose-50 border border-rose-100 rounded-xl text-sm focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition" placeholder="اكتب سبب الرفض هنا..."></textarea>
                                            @error('rejection_reason') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="flex gap-4">
                                            <button wire:click="reject" class="flex-1 bg-rose-50 text-rose-600 font-bold py-3 rounded-xl hover:bg-rose-100 transition border border-rose-100 hover:border-rose-200">رفض الطلب</button>
                                            <button wire:click="$set('activeRequestId', null)" class="px-8 bg-gray-50 text-gray-400 font-bold py-3 rounded-xl hover:bg-gray-100 transition border border-gray-200 hover:text-gray-600">إغلاق</button>
                                        </div>
                                    </div>
                                    @else
                                    <div class="mt-8 pt-8 border-t border-gray-100 flex justify-end">
                                        <button wire:click="$set('activeRequestId', null)" class="px-8 bg-gray-50 text-gray-400 font-bold py-3 rounded-xl hover:bg-gray-100 transition border border-gray-200 hover:text-gray-600">إغلاق</button>
                                    </div>
                                    @endcan
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endif
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
    </div>
</div>