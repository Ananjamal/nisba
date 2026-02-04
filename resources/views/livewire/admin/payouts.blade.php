<?php

use Livewire\Volt\Component;
use App\Models\WithdrawalRequest;
use App\Models\UserStat;
use App\Notifications\GeneralNotification;

use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    use \Livewire\WithFileUploads;

    public $payment_proof;
    public $admin_notes = '';
    public $activeRequestId = null;

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
            'requests' => WithdrawalRequest::latest()->paginate(10),
        ];
    }
}; ?>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-blue-100">
    <div class="overflow-x-auto">
        <table class="w-full text-right">
            <thead>
                <tr class="text-blue-400 text-sm border-b border-blue-50">
                    <th class="pb-4 font-bold">المسوق</th>
                    <th class="pb-4 font-bold">المبلغ</th>
                    <th class="pb-4 font-bold">التاريخ</th>
                    <th class="pb-4 font-bold">الحالة</th>
                    <th class="pb-4 font-bold">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blue-50">
                @foreach($requests as $request)
                <tr class="hover:bg-blue-50/20 border-b border-blue-50 transition {{ $activeRequestId == $request->id ? 'bg-blue-50/30' : '' }}">
                    <td class="py-6">
                        <p class="font-black text-blue-900">{{ $request->user->name }}</p>
                        <p class="text-[10px] text-blue-400 font-bold uppercase tracking-wider">{{ $request->user->email }}</p>
                    </td>
                    <td class="py-6">
                        <div class="space-y-1">
                            <p class="text-xs font-black text-blue-600">{{ $request->iban }}</p>
                            <p class="text-[10px] text-blue-400 font-bold">{{ $request->bank_name }} - {{ $request->account_holder_name }}</p>
                        </div>
                    </td>
                    <td class="py-6">
                        <p class="font-black text-blue-900">{{ number_format($request->amount, 2) }} <span class="text-[10px]">ر.س</span></p>
                    </td>
                    <td class="py-6">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black {{ $request->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $request->status === 'paid' ? 'تم الدفع' : 'قيد الانتظار' }}
                        </span>
                    </td>
                    <td class="py-6">
                        @if($request->status === 'pending')
                        <button wire:click="selectRequest({{ $request->id }})" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-xs font-black hover:bg-blue-700 transition shadow-lg shadow-blue-100">
                            تحويل الآن
                        </button>
                        @elseif($request->payment_proof_url)
                        <a href="{{ Storage::url($request->payment_proof_url) }}" target="_blank" class="text-blue-600 hover:underline text-xs font-bold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            عرض الإثبات
                        </a>
                        @endif
                    </td>
                </tr>
                @if($activeRequestId == $request->id)
                <tr class="bg-blue-50/50 border-b border-blue-100">
                    <td colspan="5" class="p-8">
                        <div class="bg-white p-8 rounded-[2rem] border border-blue-100 shadow-xl max-w-2xl mx-auto">
                            <h4 class="text-lg font-black text-blue-900 mb-6">تأكيد عملية التحويل</h4>
                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-blue-400 mr-2">صورة إثبات التحويل (Screenshot)</label>
                                    <input type="file" wire:model="payment_proof" class="w-full p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-blue-400 mr-2">ملاحظات إضافية للمسوق</label>
                                    <textarea wire:model="admin_notes" class="w-full p-4 bg-blue-50 border border-blue-100 rounded-xl text-sm" placeholder="اختياري..."></textarea>
                                </div>
                                <div class="flex gap-4">
                                    <button wire:click="approve" wire:loading.attr="disabled" class="flex-1 bg-green-500 text-white font-black py-4 rounded-2xl hover:bg-green-600 transition shadow-lg shadow-green-100">
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
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">
        {{ $requests->links() }}
    </div>
</div>