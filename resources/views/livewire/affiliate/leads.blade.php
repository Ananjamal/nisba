<?php

use Livewire\Volt\Component;
use App\Models\Lead;

new class extends Component {
    public $search = '';
    public $status_filter = '';
    public $showModal = false;

    // Form fields
    public $client_name = '';
    public $company_name = '';
    public $city = '';
    public $client_phone = '';
    public $sector = '';
    public $needs = '';
    public $recommended_systems = [];

    public $leadId = null;

    public function saveLead()
    {
        $this->validate([
            'client_name' => 'required|min:3',
            'client_phone' => 'required',
            'city' => 'required',
            'sector' => 'nullable|string|max:255',
        ]);

        if ($this->leadId) {
            $lead = auth()->user()->leads()->find($this->leadId);
            $lead->update([
                'client_name' => $this->client_name,
                'company_name' => $this->company_name,
                'city' => $this->city,
                'client_phone' => $this->client_phone,
                'sector' => $this->sector,
                'needs' => $this->needs,
                'recommended_systems' => $this->recommended_systems,
            ]);
            $message = 'تم تحديث بيانات العميل بنجاح!';
        } else {
            auth()->user()->leads()->create([
                'user_id' => auth()->id(),
                'client_name' => $this->client_name,
                'company_name' => $this->company_name,
                'city' => $this->city,
                'client_phone' => $this->client_phone,
                'sector' => $this->sector,
                'needs' => $this->needs,
                'recommended_systems' => $this->recommended_systems,
                'status' => 'under_review',
            ]);
            $message = 'تم إضافة العميل بنجاح!';
        }

        $this->reset(['leadId', 'client_name', 'company_name', 'city', 'client_phone', 'sector', 'needs', 'recommended_systems', 'showModal']);
        $this->dispatch('lead-saved');
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function editLead($id)
    {
        $lead = auth()->user()->leads()->find($id);
        if (!$lead) return;

        $this->leadId = $lead->id;
        $this->client_name = $lead->client_name;
        $this->company_name = $lead->company_name;
        $this->city = $lead->city;
        $this->client_phone = $lead->client_phone;
        $this->sector = $lead->sector;
        $this->needs = $lead->needs;
        $this->recommended_systems = $lead->recommended_systems ?? [];
        $this->showModal = true;
    }

    public function deleteLead($id)
    {
        $lead = auth()->user()->leads()->find($id);
        if ($lead) {
            $lead->delete();
            $this->dispatch('toast', type: 'success', message: 'تم حذف العميل بنجاح');
        }
    }

    public function openModal()
    {
        $this->reset(['leadId', 'client_name', 'company_name', 'city', 'client_phone', 'sector', 'needs', 'recommended_systems']);
        $this->showModal = true;
    }

    public function toggleSystem($systemName)
    {
        if (in_array($systemName, $this->recommended_systems)) {
            $this->recommended_systems = array_diff($this->recommended_systems, [$systemName]);
        } else {
            $this->recommended_systems[] = $systemName;
        }
    }

    public function with()
    {
        $leads = auth()->user()->leads()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('client_name', 'like', '%' . $this->search . '%')
                        ->orWhere('company_name', 'like', '%' . $this->search . '%')
                        ->orWhere('client_phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status_filter, function ($query) {
                $query->where('status', $this->status_filter);
            })
            ->latest()
            ->get();

        return [
            'leads' => $leads,
            'available_systems' => [
                ['name' => 'قيود', 'id' => 'qoyod'],
                ['name' => 'دفترة', 'id' => 'daftra'],
            ]
        ];
    }
}; ?>

<div class="card">
    <!-- Header -->
    <div class="card-header flex items-center justify-between">
        <div>
            <h3 class="section-title">قائمة العملاء</h3>
            <p class="section-subtitle">إجمالي {{ count($leads) }} سجلات</p>
        </div>
        <button @click="$wire.openModal()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            إضافة عميل جديد
        </button>
    </div>

    <!-- Filters -->
    <div class="px-6 py-4 border-b border-border-light flex flex-wrap items-center gap-3">
        <input type="text" wire:model.live="search" placeholder="بحث بالاسم، الشركة، الهاتف..." class="form-input max-w-md">

        <div class="flex items-center gap-2">
            <button wire:click="$set('status_filter', '')"
                class="btn btn-sm {{ $status_filter === '' ? 'btn-primary' : 'btn-outline' }}">
                الكل
            </button>
            <button wire:click="$set('status_filter', 'under_review')"
                class="btn btn-sm {{ $status_filter === 'under_review' ? 'btn-primary' : 'btn-outline' }}">
                قيد المراجعة
            </button>
            <button wire:click="$set('status_filter', 'sold')"
                class="btn btn-sm {{ $status_filter === 'sold' ? 'btn-primary' : 'btn-outline' }}">
                مباع
            </button>
            <button wire:click="$set('status_filter', 'contacting')"
                class="btn btn-sm {{ $status_filter === 'contacting' ? 'btn-primary' : 'btn-outline' }}">
                جاري التواصل
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>اسم العميل</th>
                    <th>القطاع / الأنظمة</th>
                    <th>المدينة</th>
                    <th>العمولة</th>
                    <th>رقم الهاتف</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr class="animate-fade-in-up" style="animation-delay: {{ $loop->index * 100 }}ms">
                    <td>
                        <div>
                            <p class="font-semibold text-primary-900">{{ $lead->client_name }}</p>
                            @if($lead->company_name)
                            <p class="text-xs text-secondary">{{ $lead->company_name }}</p>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="block mb-1">{{ $lead->sector ?? '-' }}</span>
                        @if($lead->recommended_systems)
                        <div class="flex flex-wrap gap-1">
                            @foreach($lead->recommended_systems as $sysId)
                            <img src="{{ asset('images/systems/'.$sysId.'.png') }}" class="w-10 h-10 object-contain" title="{{ $sysId }}" alt="{{ $sysId }}">
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td>{{ $lead->city }}</td>
                    <td>
                        @php
                        $userPivot = $lead->users->where('id', auth()->id())->first()?->pivot;
                        $totalCommission = $lead->commission_type === 'fixed'
                        ? $lead->commission_rate
                        : ($lead->expected_deal_value * $lead->commission_rate / 100);

                        // حساب نصيب المسوق
                        if ($userPivot && $userPivot->fixed_amount) {
                        $baseShare = $userPivot->fixed_amount;
                        } elseif ($userPivot && $userPivot->commission_share) {
                        $baseShare = ($totalCommission * $userPivot->commission_share) / 100;
                        } else {
                        $marketerCount = $lead->users->count();
                        $baseShare = $marketerCount > 0 ? $totalCommission / $marketerCount : 0;
                        }

                        $finalShare = $baseShare * auth()->user()->commission_multiplier;
                        $isShared = $lead->users->count() > 1;
                        @endphp

                        <div class="space-y-1">
                            <!-- العمولة الإجمالية -->
                            <div class="flex items-center gap-2">
                                @if($lead->commission_type === 'fixed')
                                <span class="font-bold text-green-600">{{ number_format($totalCommission) }} ريال</span>
                                @elseif($lead->commission_rate)
                                <span class="font-bold text-primary-600">{{ $lead->commission_rate }}%</span>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif

                                @if($isShared)
                                <svg class="w-4 h-4 text-amber-500" title="عمولة مشتركة مع {{ $lead->users->count() }} مسوقين" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                </svg>
                                @endif
                            </div>

                            <!-- نصيب المسوق -->
                            @if($lead->status === 'sold' && $lead->is_verified)
                            <div class="bg-green-50 px-2 py-1 rounded-lg border border-green-200">
                                <p class="text-[10px] text-green-600 font-bold">نصيبك:</p>
                                <p class="text-sm font-black text-green-700">
                                    {{ number_format($finalShare, 2) }} ريال
                                </p>
                                @if($isShared && $userPivot)
                                <p class="text-[9px] text-green-500">
                                    @if($userPivot->fixed_amount)
                                    (مبلغ ثابت)
                                    @elseif($userPivot->commission_share)
                                    ({{ $userPivot->commission_share }}% من الإجمالي)
                                    @else
                                    (مقسم بالتساوي)
                                    @endif
                                </p>
                                @endif
                            </div>
                            @elseif($totalCommission > 0)
                            <div class="bg-gray-50 px-2 py-1 rounded-lg border border-gray-200">
                                <p class="text-[10px] text-gray-500 font-bold">نصيبك المتوقع:</p>
                                <p class="text-sm font-bold text-gray-700">
                                    {{ number_format($finalShare, 2) }} ريال
                                </p>
                                @if($isShared && $userPivot)
                                <p class="text-[9px] text-gray-400">
                                    @if($userPivot->fixed_amount)
                                    (مبلغ ثابت)
                                    @elseif($userPivot->commission_share)
                                    ({{ $userPivot->commission_share }}%)
                                    @else
                                    (مقسم بالتساوي)
                                    @endif
                                </p>
                                @endif
                            </div>
                            @endif
                        </div>
                    </td>
                    <td dir="ltr" class="text-right">{{ $lead->client_phone }}</td>
                    <td>{{ $lead->created_at->format('Y/m/d') }}</td>
                    <td>
                        @php
                        $statusConfig = [
                        'under_review' => ['class' => 'badge-pending', 'label' => 'قيد المراجعة'],
                        'sold' => ['class' => 'badge-active', 'label' => 'مباع'],
                        'contacting' => ['class' => 'badge-info bg-primary-100 text-primary-800', 'label' => 'جاري التواصل'],
                        'cancelled' => ['class' => 'badge-rejected', 'label' => 'ملغي']
                        ];
                        $config = $statusConfig[$lead->status] ?? ['class' => 'badge-pending', 'label' => $lead->status];
                        @endphp
                        <span class="badge {{ $config['class'] }}">{{ $config['label'] }}</span>
                    </td>
                    <td>
                    <td>
                    <td>
                        <div class="relative" x-data="{ open: false, showDeleteModal: false, deletingId: null }">
                            <button @click="open = !open" @click.away="open = false" class="text-secondary hover:text-primary-900 transition-colors p-1 rounded-full hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>
                            <div x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-border-light z-10 py-1"
                                style="display: none;">
                                <button wire:click="editLead({{ $lead->id }})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-900">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        تعديل الطلب
                                    </div>
                                </button>
                                </button>
                                <button @click="deletingId = {{ $lead->id }}; showDeleteModal = true; open = false" class="block w-full text-right px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        حذف الطلب
                                    </div>
                                </button>
                            </div>

                            <!-- Professional Delete Confirmation Modal -->
                            <template x-teleport="body">
                                <div x-show="showDeleteModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
                                    <div class="fixed inset-0 bg-primary-900/60 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>

                                    <div class="flex min-h-full items-center justify-center p-4">
                                        <div class="relative w-full max-w-sm transform overflow-hidden rounded-3xl bg-white p-8 shadow-2xl transition-all text-center">
                                            <div class="mb-6">
                                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-50 text-red-600 mb-4">
                                                    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                </div>
                                                <h3 class="text-2xl font-black text-primary-900 mb-2">هل أنت متأكد؟</h3>
                                                <p class="text-primary-500 font-medium">سيتم حذف بيانات العميل نهائياً، هذا الإجراء لا يمكن التراجع عنه.</p>
                                            </div>

                                            <div class="flex flex-col gap-3">
                                                <button
                                                    @click="$wire.deleteLead(deletingId); showDeleteModal = false"
                                                    class="w-full py-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-bold shadow-lg shadow-red-200 hover:shadow-red-300 transition-all transform active:scale-95">
                                                    تأكيد الحذف
                                                </button>
                                                <button
                                                    @click="showDeleteModal = false"
                                                    class="w-full py-4 bg-primary-50 text-primary-600 rounded-2xl font-bold hover:bg-primary-100 transition-all font-bold">
                                                    إلغاء
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-12">
                        <div class="empty-state">
                            <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <p class="empty-state-text">لا توجد طلبات سحب حالياً</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add Lead Modal -->
    <template x-teleport="body">
        <div x-show="$wire.showModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 overflow-y-auto px-4 z-50" style="display: none;">
            <div class="flex items-center justify-center min-h-screen">
                <div @click="$wire.set('showModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

                <div class="bg-white rounded-2xl shadow-xl relative w-full max-w-2xl"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-border-light flex items-center justify-between">
                        <h3 class="text-lg font-bold text-primary-900">{{ $leadId ? 'تعديل بيانات العميل' : 'إضافة عميل جديد' }}</h3>
                        <button @click="$wire.set('showModal', false)" class="text-secondary hover:text-primary-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit.prevent="saveLead" class="p-6 space-y-4">
                        <div class="form-group">
                            <label class="form-label">اسم العميل *</label>
                            <input type="text" wire:model="client_name" class="form-input" placeholder="أدخل اسم العميل">
                            @error('client_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">اسم الشركة</label>
                                <input type="text" wire:model="company_name" class="form-input" placeholder="اختياري">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">رقم الهاتف *</label>
                                <input type="text" wire:model="client_phone" class="form-input" placeholder="05xxxxxxxx" dir="ltr">
                                @error('client_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">القطاع / المجال</label>
                                <select wire:model="sector" class="form-input">
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
                        </div>

                        <div class="form-group">
                            <label class="form-label">المدينة *</label>
                            <select wire:model="city" class="form-select">
                                <option value="">اختر المدينة</option>
                                <option value="الرياض">الرياض</option>
                                <option value="جدة">جدة</option>
                                <option value="الدمام">الدمام</option>
                                <option value="مكة">مكة المكرمة</option>
                                <option value="المدينة">المدينة المنورة</option>
                            </select>
                            @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">الاحتياجات</label>
                            <textarea wire:model="needs" class="form-textarea" rows="3" placeholder="وصف احتياجات العميل"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">الأنظمة المقترحة</label>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($available_systems as $system)
                                <button type="button"
                                    wire:click="toggleSystem('{{ $system['id'] }}')"
                                    class="relative group flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all duration-200 {{ in_array($system['id'], $recommended_systems) ? 'border-primary-900 bg-primary-50/50' : 'border-gray-100 hover:border-primary-200 bg-white' }}">

                                    <div class="h-12 flex items-center justify-center mb-3 transition-transform group-hover:scale-105">
                                        <img src="{{ asset('images/systems/' . $system['id'] . '.png') }}" alt="{{ $system['name'] }}" class="h-full object-contain filter {{ in_array($system['id'], $recommended_systems) ? '' : 'grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100' }} transition-all">
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors {{ in_array($system['id'], $recommended_systems) ? 'border-primary-900 bg-primary-900' : 'border-gray-300' }}">
                                            @if(in_array($system['id'], $recommended_systems))
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            @endif
                                        </div>
                                        <span class="font-bold text-sm {{ in_array($system['id'], $recommended_systems) ? 'text-primary-900' : 'text-gray-500 group-hover:text-gray-700' }}">{{ $system['name'] }}</span>
                                    </div>

                                    @if(in_array($system['id'], $recommended_systems))
                                    <div class="absolute inset-0 border-2 border-primary-900 rounded-xl pointer-events-none"></div>
                                    @endif
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 pt-4">
                            <button type="button" @click="$wire.set('showModal', false)" class="btn btn-outline">
                                إلغاء
                            </button>
                            <button type="submit" class="btn btn-primary">
                                {{ $leadId ? 'تحديث العميل' : 'حفظ العميل' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>