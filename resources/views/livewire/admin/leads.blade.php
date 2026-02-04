<?php

use Livewire\Volt\Component;
use App\Models\Lead;
use App\Models\Commission;
use App\Notifications\GeneralNotification;

use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    public $search = '';
    public $status_filter = '';

    public function updateStatus($leadId, $status, $verify = false)
    {
        $lead = Lead::findOrFail($leadId);
        $oldStatus = $lead->status;
        $lead->status = $status;

        if ($verify) {
            $lead->is_verified = true;
            $lead->notes = ($lead->notes ? $lead->notes . "\n" : "") . "تم التأكيد يدوياً بواسطة الإدارة في " . now()->format('Y-m-d H:i');
        }

        $lead->save();

        if ($status === 'sold' && $oldStatus !== 'sold' && $lead->is_verified) {
            $lead->user->notify(new \App\Notifications\GeneralNotification([
                'title' => 'تم تحقيق مبيعة محققة!',
                'message' => 'مبروك! تم تأكيد المبيعة للعميل ' . $lead->client_name . ' وإضافة العمولة لرصيدك.',
                'type' => 'success'
            ]));
        }

        $this->dispatch('lead-updated');
    }

    public function with()
    {
        return [
            'leads' => Lead::when($this->search, function ($query) {
                $query->where('client_name', 'like', '%' . $this->search . '%')
                    ->orWhere('company_name', 'like', '%' . $this->search . '%');
            })
                ->when($this->status_filter, function ($query) {
                    $query->where('status', $this->status_filter);
                })
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-blue-100">
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="flex-1 relative">
                <input type="text" wire:model.live="search" placeholder="بحث بالاسم أو الشركة..." class="w-full pr-10 py-3 bg-white border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500/10 shadow-sm">
                <div class="absolute inset-y-0 right-3 flex items-center text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <select wire:model.live="status_filter" class="w-full md:w-48 py-3 bg-white border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500/10 cursor-pointer shadow-sm">
                <option value="">كل الحالات</option>
                <option value="under_review">تحت المراجعة</option>
                <option value="contacting">جاري التواصل</option>
                <option value="sold">تم البيع</option>
                <option value="cancelled">ملغي</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="text-blue-400 text-sm border-b border-blue-50">
                        <th class="pb-4 font-bold">العميل</th>
                        <th class="pb-4 font-bold">الشريك (المسوق)</th>
                        <th class="pb-4 font-bold">المدينة / الهاتف</th>
                        <th class="pb-4 font-bold">الحالة</th>
                        <th class="pb-4 font-bold">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    @foreach($leads as $lead)
                    <tr class="hover:bg-blue-50/20 border-b border-blue-50 transition">
                        <td class="py-4">
                            <p class="font-bold text-blue-900">{{ $lead->client_name }}</p>
                            <p class="text-xs text-blue-400">{{ $lead->company_name }}</p>
                        </td>
                        <td class="py-4">
                            <p class="text-sm font-bold text-blue-600">{{ $lead->user->name }}</p>
                        </td>
                        <td class="py-4">
                            <p class="text-sm font-bold text-blue-800">{{ $lead->city }}</p>
                            <p class="text-xs text-blue-400">{{ $lead->client_phone }}</p>
                        </td>
                        <td class="py-4">
                            @php
                            $statusColors = [
                            'under_review' => 'bg-red-50 text-red-600',
                            'contacting' => 'bg-yellow-50 text-yellow-600',
                            'sold' => 'bg-green-50 text-green-600',
                            'cancelled' => 'bg-gray-100 text-gray-500'
                            ];
                            $statusLabels = [
                            'under_review' => 'تحت المراجعة',
                            'contacting' => 'جاري التواصل',
                            'sold' => 'تم البيع',
                            'cancelled' => 'ملغي'
                            ];
                            @endphp
                            <div class="flex flex-col gap-1">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black {{ $statusColors[$lead->status] ?? 'bg-gray-100 text-gray-500' }} text-center">
                                    {{ $statusLabels[$lead->status] ?? $lead->status }}
                                </span>
                                @if($lead->is_verified)
                                <span class="flex items-center justify-center gap-1 text-[9px] font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-md border border-green-100" title="تم التحقق من الدفع">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path>
                                    </svg>
                                    مؤكد ({{ $lead->external_system ?? 'يدوي' }})
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-4">
                            <div class="flex gap-2">
                                @if($lead->status !== 'sold')
                                <button wire:click="updateStatus({{ $lead->id }}, 'sold', true)" class="p-1 text-green-500 hover:bg-green-50 rounded transition border border-transparent hover:border-green-200" title="تأكيد المبيعة والدفع">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                                @endif
                                @if($lead->status === 'under_review')
                                <button wire:click="updateStatus({{ $lead->id }}, 'contacting')" class="p-1 text-yellow-500 hover:bg-yellow-50 rounded transition border border-transparent hover:border-yellow-200" title="جاري التواصل">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 011 .684l.708 2.828a1 1 0 01-.366 1.05L7.42 8.42a15.05 15.05 0 006.58 6.58l1.07-1.07a1 1 0 011.05-.366l2.828.708a1 1 0 01.684 1V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </button>
                                @endif
                                <button wire:click="updateStatus({{ $lead->id }}, 'cancelled')" class="p-1 text-red-400 hover:bg-red-50 rounded transition border border-transparent hover:border-red-200" title="إلغاء">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $leads->links() }}
        </div>
    </div>
</div>