<?php

use Livewire\Volt\Component;
use App\Models\Lead;
use App\Models\Commission;
use App\Notifications\GeneralNotification;

use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    public $search = '';
    public $status_filter = '';

    // Create/Edit Modal State
    public $showCreateModal = false;
    public $leadId = null; // null for create, ID for edit
    public $name = '';
    public $email = '';
    public $phone = '';
    public $company_name = '';
    public $sector = '';
    public $commission_type = 'fixed';
    public $commission_rate = '';
    public $city = '';
    public $affiliate_id = ''; // Optional: assign to affiliate
    public $recommended_systems = []; // Array of system IDs

    public function resetForm()
    {
        $this->leadId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->company_name = '';
        $this->sector = '';
        $this->commission_type = 'fixed';
        $this->commission_rate = '';
        $this->city = '';
        $this->affiliate_id = '';
        $this->recommended_systems = [];
        $this->resetValidation();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function editLead($id)
    {
        $lead = Lead::findOrFail($id);
        $this->leadId = $lead->id;
        $this->name = $lead->client_name;
        $this->phone = $lead->client_phone;
        $this->email = $lead->email;
        $this->company_name = $lead->company_name;
        $this->city = $lead->city;
        $this->sector = $lead->sector;
        $this->commission_type = $lead->commission_type;
        $this->commission_rate = $lead->commission_rate;
        $this->affiliate_id = $lead->user_id === auth()->id() ? '' : $lead->user_id;
        $this->recommended_systems = $lead->recommended_systems ?? [];

        $this->showCreateModal = true;
    }

    public function toggleSystem($systemId)
    {
        if (in_array($systemId, $this->recommended_systems)) {
            $this->recommended_systems = array_diff($this->recommended_systems, [$systemId]);
        } else {
            $this->recommended_systems[] = $systemId;
        }
    }

    public function saveLead()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'commission_type' => 'required|in:fixed,percentage',
            'commission_rate' => 'required|numeric|min:0',
            'sector' => 'nullable|string|max:255',
            'affiliate_id' => 'nullable|exists:users,id',
        ]);

        $data = [
            'client_name' => $this->name,
            'client_phone' => $this->phone,
            'email' => $this->email,
            'company_name' => $this->company_name,
            'city' => $this->city,
            'sector' => $this->sector,
            'commission_type' => $this->commission_type,
            'commission_rate' => $this->commission_rate,
            'recommended_systems' => $this->recommended_systems,
        ];

        // Only update user_id (affiliate) if explicitly set for admin, or if creating new
        // For admin editing, we might want to allow changing affiliate? Yes.
        $userId = $this->affiliate_id ?: auth()->id();
        $data['user_id'] = $userId;

        if ($this->leadId) {
            $lead = Lead::findOrFail($this->leadId);
            $lead->update($data);
            $message = 'تم تحديث بيانات العميل بنجاح!';
        } else {
            $data['status'] = 'under_review';
            Lead::create($data);
            $message = 'تم إضافة العميل بنجاح!';
        }

        $this->showCreateModal = false;
        $this->resetForm();

        // Notify user if needed or just flash message
        session()->flash('message', $message);
    }

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

    public function deleteLead($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();
        session()->flash('message', 'تم حذف العميل بنجاح!');
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
            'affiliates' => \App\Models\User::where('role', 'affiliate')->get(),
            'available_systems' => [
                ['name' => 'قيود', 'id' => 'qoyod'],
                ['name' => 'دفترة', 'id' => 'daftra'],
            ]
        ];
    }
}; ?>

<div class="space-y-6" x-data="{ showDeleteModal: false, deletingId: null }">
    @if (session()->has('message'))
    <div class="p-4 text-sm text-green-700 bg-green-100 rounded-2xl font-bold border border-green-200" role="alert">
        {{ session('message') }}
    </div>
    @endif
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-blue-100">
        <div class="flex flex-col md:flex-row gap-4 mb-6 justify-between items-center">
            <div class="flex gap-4 w-full md:w-auto flex-1">
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
                <select wire:model.live="sector_filter" class="w-full md:w-48 py-3 bg-white border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500/10 cursor-pointer shadow-sm">
                    <option value="">كل القطاعات</option>
                    <option value="العقارات">العقارات</option>
                    <option value="التقنية والبرمجة">التقنية والبرمجة</option>
                    <option value="التسويق والدعاية">التسويق والدعاية</option>
                    <option value="التجارة الإلكترونية">التجارة الإلكترونية</option>
                    <option value="التعليم">التعليم</option>
                    <option value="الصحة">الصحة</option>
                    <option value="الخدمات المالية">الخدمات المالية</option>
                    <option value="المقاولات والبناء">المقاولات والبناء</option>
                    <option value="المطاعم والكافيهات">المطاعم والكافيهات</option>
                    <option value="أخرى">أخرى</option>
                </select>
            </div>
            <button wire:click="openCreateModal" class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-600/20 font-bold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                إضافة عميل
            </button>
        </div>

        @if($showCreateModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden animate-fade-in-up">
                <div class="bg-blue-600 p-6 flex justify-between items-center text-white">
                    <h3 class="text-xl font-bold">{{ $leadId ? 'تعديل بيانات العميل' : 'إضافة عميل جديد' }}</h3>
                    <button wire:click="$set('showCreateModal', false)" class="hover:bg-white/20 p-2 rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">اسم العميل</label>
                            <input type="text" wire:model="name" class="w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">رقم الهاتف</label>
                            <input type="text" wire:model="phone" class="w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">البريد الإلكتروني (اختياري)</label>
                            <input type="email" wire:model="email" class="w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">المدينة</label>
                            <input type="text" wire:model="city" class="w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">اسم الشركة</label>
                            <input type="text" wire:model="company_name" class="w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">القطاع / المجال</label>
                            <select wire:model="sector" class="w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                                <option value="">اختر القطاع</option>
                                <option value="العقارات">العقارات</option>
                                <option value="التقنية والبرمجة">التقنية والبرمجة</option>
                                <option value="التسويق والدعاية">التسويق والدعاية</option>
                                <option value="التجارة الإلكترونية">التجارة الإلكترونية</option>
                                <option value="التعليم">التعليم</option>
                                <option value="الصحة">الصحة</option>
                                <option value="الخدمات المالية">الخدمات المالية</option>
                                <option value="المقاولات والبناء">المقاولات والبناء</option>
                                <option value="المطاعم والكافيهات">المطاعم والكافيهات</option>
                                <option value="أخرى">أخرى</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">الأنظمة المقترحة</label>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($available_systems as $system)
                                <button type="button"
                                    wire:click="toggleSystem('{{ $system['id'] }}')"
                                    class="relative group flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all duration-200 {{ in_array($system['id'], $recommended_systems) ? 'border-primary-900 bg-blue-50/50' : 'border-gray-200 hover:border-blue-200 bg-white' }}">

                                    <div class="h-10 flex items-center justify-center mb-2 transition-transform group-hover:scale-105">
                                        <img src="{{ asset('images/systems/' . $system['id'] . '.png') }}" alt="{{ $system['name'] }}" class="h-full object-contain filter {{ in_array($system['id'], $recommended_systems) ? '' : 'grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100' }} transition-all">
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition-colors {{ in_array($system['id'], $recommended_systems) ? 'border-primary-900 bg-primary-900' : 'border-gray-300' }}">
                                            @if(in_array($system['id'], $recommended_systems))
                                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            @endif
                                        </div>
                                        <span class="font-bold text-xs {{ in_array($system['id'], $recommended_systems) ? 'text-primary-900' : 'text-gray-500 group-hover:text-gray-700' }}">{{ $system['name'] }}</span>
                                    </div>

                                    @if(in_array($system['id'], $recommended_systems))
                                    <div class="absolute inset-0 border-2 border-primary-900 rounded-xl pointer-events-none"></div>
                                    @endif
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4 mt-4">
                        <h4 class="font-bold text-blue-900 mb-4">إعدادات العمولة</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">نوع العمولة</label>
                                <select wire:model.live="commission_type" class="w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                                    <option value="fixed">مبلغ ثابت</option>
                                    <option value="percentage">نسبة مئوية</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    {{ $commission_type === 'fixed' ? 'قيمة العمولة (ريال)' : 'نسبة العمولة (%)' }}
                                </label>
                                <input type="number" wire:model="commission_rate" class="w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                                @error('commission_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4 mt-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">تعيين لمسوق (اختياري)</label>
                        <select wire:model="affiliate_id" class="w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- بدون مسوق (مباشر) --</option>
                            @foreach($affiliates as $affiliate)
                            <option value="{{ $affiliate->id }}">{{ $affiliate->name }} ({{ $affiliate->email }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">إذا لم يتم التحديد، سيتم تعيين العميل لك.</p>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button wire:click="$set('showCreateModal', false)" class="px-6 py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 font-bold transition">إلغاء</button>
                        <button wire:click="saveLead" class="px-6 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold hover:shadow-lg hover:shadow-blue-500/30 transition">
                            {{ $leadId ? 'حفظ التغييرات' : 'حفظ العميل' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="text-blue-400 text-sm border-b border-blue-50">
                        <th class="pb-4 font-bold">العميل</th>
                        <th class="pb-4 font-bold">القطاع / الأنظمة</th>
                        <th class="pb-4 font-bold">العمولة</th>
                        <th class="pb-4 font-bold">الشريك (المسوق)</th>
                        <th class="pb-4 font-bold">تاريخ الإضافة</th>
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
                            <span class="text-sm text-gray-600 block mb-1">{{ $lead->sector ?? '-' }}</span>
                            @if($lead->recommended_systems)
                            <div class="flex flex-wrap gap-1">
                                @foreach($lead->recommended_systems as $sysId)
                                <img src="{{ asset('images/systems/'.$sysId.'.png') }}" class="w-10 h-10 object-contain" title="{{ $sysId }}">
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="py-4">
                            @if($lead->commission_type === 'fixed')
                            <span class="text-sm font-bold text-green-600">{{ number_format($lead->commission_rate) }} ريال</span>
                            @else
                            <span class="text-sm font-bold text-blue-600">{{ $lead->commission_rate }}%</span>
                            @endif
                        </td>
                        <td class="py-4">
                            <p class="text-sm font-bold text-blue-600">{{ $lead->user->name }}</p>
                        </td>
                        <td class="py-4">
                            <p class="text-sm font-bold text-blue-600">{{ $lead->created_at->format('Y-m-d') }}</p>
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
                                <button wire:click="editLead({{ $lead->id }})" class="p-1 text-blue-500 hover:bg-blue-50 rounded transition border border-transparent hover:border-blue-200" title="تعديل">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button @click="deletingId = {{ $lead->id }}; showDeleteModal = true" class="p-1 text-red-500 hover:bg-red-50 rounded transition border border-transparent hover:border-red-200" title="حذف">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
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
    <!-- Professional Delete Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="showDeleteModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-blue-900/60 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-sm transform overflow-hidden rounded-3xl bg-white p-8 shadow-2xl transition-all text-center">
                    <div class="mb-6">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-50 text-red-600 mb-4">
                            <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-blue-900 mb-2">هل أنت متأكد؟</h3>
                        <p class="text-blue-500 font-medium">سيتم حذف بيانات العميل نهائياً، هذا الإجراء لا يمكن التراجع عنه.</p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button
                            @click="$wire.deleteLead(deletingId); showDeleteModal = false"
                            class="w-full py-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-bold shadow-lg shadow-red-200 hover:shadow-red-300 transition-all transform active:scale-95">
                            تأكيد الحذف
                        </button>
                        <button
                            @click="showDeleteModal = false"
                            class="w-full py-4 bg-blue-50 text-blue-600 rounded-2xl font-bold hover:bg-blue-100 transition-all font-bold">
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>