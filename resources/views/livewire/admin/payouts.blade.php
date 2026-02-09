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
        ]);

        // Update user stats
        $stats = UserStat::where('user_id', $request->user_id)->first();
        if ($stats) {
            $stats->decrement('pending_commissions', $request->amount);
        }

        $request->user->notify(new GeneralNotification([
            'title' => 'تم تحويل مستحقاتك!',
            'message' => 'تمت الموافقة على طلب سحب مبلغ ' . $request->amount . ' ر.س وإرفاق إثبات التحويل.',
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

<div class="space-y-6">
    <x-table.filter-bar :statusOptions="['pending' => 'قيد الانتظار', 'paid' => 'تم الدفع', 'cancelled' => 'ملغي']">
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
                    class="group flex items-center justify-center p-2.5 bg-white border border-gray-100 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all duration-300 shadow-sm"
                    title="تصدير Excel">
                    <svg class="w-5 h-5 text-green-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </a>
                <a href="{{ route('admin.reports.payouts.pdf', ['status' => $status_filter, 'date_from' => $date_from, 'date_to' => $date_to]) }}"
                    target="_blank"
                    class="group flex items-center justify-center p-2.5 bg-white border border-gray-100 rounded-xl hover:border-red-500 hover:bg-red-50 transition-all duration-300 shadow-sm"
                    title="تصدير PDF">
                    <svg class="w-5 h-5 text-red-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </a>
            </div>
        </x-slot>
    </x-table.filter-bar>

    <!-- Payouts Table -->
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="text-primary-400 text-sm border-b border-primary-50">
                        @if($columns['marketer_client'])
                        <th class="pb-4 font-semibold text-start text-gray-600">المسوق / العميل</th>
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
                        <th class="pb-4 font-semibold text-start text-gray-600">المرفقات</th>
                        @endif
                        @if($columns['actions'])
                        <th class="pb-4 font-semibold text-start text-gray-600">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($requests as $request)
                    <tr class="group hover:bg-gray-50 transition-all duration-300">
                        @if($columns['marketer_client'])
                        <td class="py-6">
                            <div class="space-y-1">
                                <div>
                                    <a href="{{ route('admin.affiliates.show', $request->user_id) }}" class="font-bold text-gray-900 hover:text-primary-600 transition">{{ $request->user->name }}</a>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">{{ $request->user->email }}</p>
                                </div>
                                <div class="pt-2 border-t border-primary-50 text-right">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ">العميل</p>
                                    <a href="{{ route('admin.leads.show', $request->lead_id ?? 0) }}" class="text-xs font-bold text-gray-700 hover:text-primary-900 transition block">
                                        {{ $request->client_name ?: 'غير محدد' }}
                                    </a>
                                    <p class="text-[10px] text-gray-500">{{ $request->company_name }}</p>
                                </div>
                            </div>
                        </td>
                        @endif
                        @if($columns['bank_info'])
                        <td class="py-6">
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-primary-600">{{ $request->iban }}</p>
                                <p class="text-[10px] text-gray-400 font-bold">{{ $request->bank_name }} - {{ $request->account_holder_name }}</p>
                            </div>
                        </td>
                        @endif
                        @if($columns['amount'])
                        <td class="py-6">
                            <p class="font-black text-primary-900">{{ number_format($request->amount, 2) }} <span class="text-[10px]">ر.س</span></p>
                        </td>
                        @endif
                        @if($columns['status'])
                        <td class="py-6">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black {{ $request->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-primary-100 text-primary-700' }}">
                                {{ $request->status === 'paid' ? 'تم الدفع' : 'قيد الانتظار' }}
                            </span>
                        </td>
                        @endif
                        @if($columns['attachments'])
                        <td class="py-6">
                            <div class="flex flex-col gap-1">
                                @if($request->invoice_url)
                                <a href="{{ Storage::url($request->invoice_url) }}" target="_blank" class="text-primary-600 hover:underline text-[10px] font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    الفاتورة
                                </a>
                                @endif
                                @if($request->iban_proof_url)
                                <a href="{{ Storage::url($request->iban_proof_url) }}" target="_blank" class="text-indigo-600 hover:underline text-[10px] font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    إثبات الآيبان
                                </a>
                                @endif
                                @if($request->payment_proof_url)
                                <a href="{{ Storage::url($request->payment_proof_url) }}" target="_blank" class="text-green-600 hover:underline text-[10px] font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    إثبات الدفع
                                </a>
                                @endif
                            </div>
                        </td>
                        @endif
                        @if($columns['actions'])
                        <td class="py-6">
                            @if($request->status === 'pending')
                            <button wire:click="selectRequest({{ $request->id }})" class="bg-primary-600 text-white px-5 py-2 rounded-xl text-xs font-bold hover:bg-primary-700 transition shadow-lg shadow-primary-500/20">
                                تحويل الآن
                            </button>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @if($activeRequestId == $request->id)
                    <tr class="bg-primary-50/50 border-b border-primary-100">
                        <td colspan="6" class="p-8">
                            <div class="bg-white p-8 rounded-[2rem] border border-primary-100 shadow-xl max-w-2xl mx-auto">
                                <h4 class="text-lg font-black text-primary-900 mb-6">تأكيد عملية التحويل</h4>
                                <div class="space-y-6">
                                    <div class="space-y-2">
                                        <label class="text-xs font-black text-primary-400 mr-2">صورة إثبات التحويل (Screenshot)</label>
                                        <input type="file" wire:model="payment_proof" class="w-full p-3 bg-primary-50 border border-primary-100 rounded-xl text-xs">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-black text-primary-400 mr-2">ملاحظات إضافية للمسوق</label>
                                        <textarea wire:model="admin_notes" class="w-full p-4 bg-primary-50 border border-primary-100 rounded-xl text-sm" placeholder="اختياري..."></textarea>
                                    </div>
                                    <div class="flex gap-4">
                                        <button wire:click="approve" wire:loading.attr="disabled" class="flex-1 btn btn-primary">
                                            تأكيد وإرسال الإشعار
                                        </button>
                                        <button wire:click="$set('activeRequestId', null)" class="px-8 bg-gray-100 text-gray-500 font-black py-4 rounded-2xl hover:bg-gray-200 transition">
                                            إلغاء
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center">
                            <p class="text-primary-400 font-bold">لا توجد طلبات سحب حالياً</p>
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