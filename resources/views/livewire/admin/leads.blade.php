<?php

use Livewire\Volt\Component;
use App\Models\Lead;
use App\Models\Commission;
use App\Notifications\GeneralNotification;

use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    use App\Livewire\Traits\WithDynamicTable;

    public $sector_filter = '';
    public $affiliate_filter = '';
    public $showFilters = false;

    public function mount()
    {
        $this->loadTablePrefs([
            'client' => true,
            'sector' => true,
            'commission' => true,
            'affiliate' => true,
            'date' => true,
            'renewal_date' => true,
            'city_phone' => true,
            'status' => true,
            'actions' => true,
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status_filter', 'sector_filter', 'affiliate_filter', 'date_from', 'date_to']);
    }

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
    public $region = '';
    public $city = '';
    public $affiliate_ids = []; // Multiple affiliates
    public $affiliate_shares = []; // [userId => percentage]
    public $affiliate_fixed = []; // [userId => fixedAmount]
    public $recommended_systems = []; // Array of system IDs

    // New Service fields
    public $new_service_name = '';
    public $new_service_id = '';
    public $new_service_image = null;
    public $show_add_service = false;

    // New Sector fields
    public $new_sector_name_input = '';
    public $show_add_sector = false;

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
        $this->region = '';
        $this->city = '';
        $this->affiliate_ids = [];
        $this->affiliate_shares = [];
        $this->affiliate_fixed = [];
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
        $lead = Lead::with('users')->findOrFail($id);
        $this->leadId = $lead->id;
        $this->name = $lead->client_name;
        $this->phone = $lead->client_phone;
        $this->email = $lead->email;
        $this->company_name = $lead->company_name;
        $this->city = $lead->city;
        $this->sector = $lead->sector;
        $this->commission_type = $lead->commission_type;
        $this->commission_rate = $lead->commission_rate;
        $this->affiliate_ids = $lead->users->pluck('id')->toArray();

        $this->affiliate_shares = [];
        $this->affiliate_fixed = [];

        $hasShares = false;
        foreach ($lead->users as $u) {
            $this->affiliate_shares[$u->id] = $u->pivot->commission_share;
            $this->affiliate_fixed[$u->id] = $u->pivot->fixed_amount;
            if ($u->pivot->commission_share > 0) {
                $hasShares = true;
            }
        }

        // If no shares are set (legacy data or manual clear), recalculate default shares
        if (!$hasShares && !empty($this->affiliate_ids)) {
            $this->updatedAffiliateIds($this->affiliate_ids);
        }

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

    public function addNewSector()
    {
        $this->validate([
            'new_sector_name_input' => 'required|string|max:50',
        ]);

        $available = json_decode(\App\Models\Setting::get('available_sectors', '[]'), true) ?: [
            'العقارات',
            'التقنية والبرمجة',
            'التسويق والدعاية',
            'التجارة الإلكترونية',
            'التعليم',
            'الصحة',
            'الخدمات المالية',
            'المقاولات والبناء',
            'المطاعم والكافيهات',
            'أخرى'
        ];

        if (in_array($this->new_sector_name_input, $available)) {
            $this->addError('new_sector_name_input', 'هذا القطاع موجود بالفعل');
            return;
        }

        $available[] = $this->new_sector_name_input;
        \App\Models\Setting::set('available_sectors', json_encode($available));

        $this->sector = $this->new_sector_name_input;
        $this->new_sector_name_input = '';
        $this->show_add_sector = false;
        $this->dispatch('toast', type: 'success', message: 'تم إضافة القطاع الجديد بنجاح');
    }

    public function addNewService()
    {
        $this->validate([
            'new_service_name' => 'required|string|max:50',
            'new_service_id' => 'required|string|max:20|alpha_dash',
            'new_service_image' => 'nullable|image|max:1024',
        ]);

        $available = json_decode(\App\Models\Setting::get('available_systems', '[]'), true) ?: [
            ['name' => 'قيود', 'id' => 'qoyod'],
            ['name' => 'دفترة', 'id' => 'daftra'],
        ];

        foreach ($available as $sys) {
            if ($sys['id'] === $this->new_service_id) {
                $this->addError('new_service_id', 'هذا المعرف موجود بالفعل');
                return;
            }
        }

        if ($this->new_service_image) {
            $this->new_service_image->storeAs('images/systems', $this->new_service_id . '.png', 'public_uploads');
        }

        $available[] = ['name' => $this->new_service_name, 'id' => $this->new_service_id];
        \App\Models\Setting::set('available_systems', json_encode($available));

        $this->recommended_systems[] = $this->new_service_id;
        $this->new_service_name = '';
        $this->new_service_id = '';
        $this->new_service_image = null;
        $this->show_add_service = false;
        $this->dispatch('toast', type: 'success', message: 'تم إضافة الخدمة الجديدة بنجاح');
    }

    public function updatedSector($value)
    {
        if ($value === 'أخرى') {
            $this->show_add_sector = true;
            $this->sector = '';
        }
    }

    public function updatedAffiliateIds($value)
    {
        // Ensure ids are integers and unique
        $ids = collect($this->affiliate_ids)->map(fn($id) => (int)$id)->filter()->unique()->toArray();
        $count = count($ids);

        if ($count > 0) {
            $baseShare = floor(10000 / $count) / 100; // 2 decimal places precision
            $totalDistributed = $baseShare * $count;
            $remainder = round(100 - $totalDistributed, 2);

            $newShares = [];
            foreach ($ids as $index => $id) {
                // Add the remainder to the first marketer to ensure total is exactly 100%
                $share = $baseShare + ($index === 0 ? $remainder : 0);
                $newShares[$id] = round($share, 2);
            }
            $this->affiliate_shares = $newShares;
        } else {
            $this->affiliate_shares = [];
        }
    }

    public function updatedCommissionType($value)
    {
        // Recalculate shares when commission type changes
        if (!empty($this->affiliate_ids)) {
            $this->updatedAffiliateIds($this->affiliate_ids);
        }
    }

    public function updatedCommissionRate($value)
    {
        // Recalculate shares when commission rate changes
        if (!empty($this->affiliate_ids)) {
            $this->updatedAffiliateIds($this->affiliate_ids);
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
            'affiliate_ids' => 'nullable|array',
            'affiliate_ids.*' => 'exists:users,id',
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

        // Determine affiliate IDs to sync
        $affiliateIdsFull = !empty($this->affiliate_ids) ? $this->affiliate_ids : [\Illuminate\Support\Facades\Auth::id()];
        $syncData = [];
        foreach ($affiliateIdsFull as $id) {
            $syncData[$id] = [
                'commission_share' => $this->affiliate_shares[$id] ?? null,
                'fixed_amount' => $this->affiliate_fixed[$id] ?? null,
            ];
        }

        if ($this->leadId) {
            $lead = Lead::findOrFail($this->leadId);
            $lead->update($data);
            $lead->users()->sync($syncData);
            $message = 'تم تحديث بيانات العميل بنجاح!';
        } else {
            $data['status'] = \App\Models\Lead::STATUS_NEW;
            $data['user_id'] = \Illuminate\Support\Facades\Auth::id();
            $lead = Lead::create($data);
            $lead->users()->sync($syncData);
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

    public $showViewModal = false;
    public $viewLead = null;
    public $showAffiliatesModal = false;
    public $viewAffiliatesLead = null;

    public function openAffiliatesModal($id)
    {
        $this->viewAffiliatesLead = Lead::with('users')->findOrFail($id);
        $this->showAffiliatesModal = true;
    }

    public function openViewModal($id)
    {
        $this->viewLead = Lead::with(['user', 'users'])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function with()
    {
        return [
            'leads' => Lead::with('users')
                ->when($this->search, function ($query) {
                    $query->where('client_name', 'like', '%' . $this->search . '%')
                        ->orWhere('company_name', 'like', '%' . $this->search . '%')
                        ->orWhere('client_phone', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->when($this->status_filter, function ($query) {
                    $query->where('status', $this->status_filter);
                })
                ->when($this->sector_filter, function ($query) {
                    $query->where('sector', $this->sector_filter);
                })
                ->when($this->affiliate_filter, function ($query) {
                    $query->whereHas('users', fn($q) => $q->where('users.id', $this->affiliate_filter));
                })
                ->when($this->date_from, fn($query) => $query->whereDate('created_at', '>=', $this->date_from))
                ->when($this->date_to, fn($query) => $query->whereDate('created_at', '<=', $this->date_to))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),
            'affiliates' => \App\Models\User::where('role', 'affiliate')->get(),
            'regions' => \App\Services\SaudiGeoService::getRegions(),
            'regionsWithCities' => \App\Services\SaudiGeoService::getRegionsWithCities(),
            'available_systems' => json_decode(\App\Models\Setting::get('available_systems', '[]'), true) ?: [
                ['name' => 'قيود', 'id' => 'qoyod'],
                ['name' => 'دفترة', 'id' => 'daftra'],
            ],
            'available_sectors' => array_unique(array_merge(json_decode(\App\Models\Setting::get('available_sectors', '[]'), true) ?: [
                'العقارات',
                'التقنية والبرمجة',
                'التسويق والدعاية',
                'التجارة الإلكترونية',
                'التعليم',
                'الصحة',
                'الخدمات المالية',
                'المقاولات والبناء',
                'المطاعم والكافيهات'
            ], ['أخرى']))
        ];
    }
}; ?>

<div class="space-y-8" x-data="{ showDeleteModal: false, deletingId: null }">
    @if (session()->has('message'))
    <div class="p-4 text-sm text-green-700 bg-green-100 rounded-2xl font-bold border border-green-200" role="alert">
        {{ session('message') }}
    </div>
    @endif
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <x-table.filter-bar :statusOptions="collect(\App\Models\Lead::lifecycleStatuses())->mapWithKeys(fn($v, $k) => [$k => $v['label']])->toArray()">

            <x-slot name="actions">
                <div class="flex gap-2">
                    <x-table.column-toggler :columns="$columns" :labels="[
                    'client' => 'العميل',
                    'sector' => 'القطاع / الأنظمة',
                    'commission' => 'العمولة',
                    'affiliate' => 'الشريك (المسوق)',
                    'date' => 'تاريخ الإضافة',
                    'city_phone' => 'المدينة / الهاتف',
                    'status' => 'الحالة',
                    'actions' => 'العمليات'
                ]" />

                    <a href="{{ route('admin.reports.export.excel', ['search' => $search, 'status' => $status_filter, 'sector' => $sector_filter, 'affiliate' => $affiliate_filter]) }}"
                        target="_blank"
                        class="group flex items-center justify-center p-2.5 bg-white border border-gray-100 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all duration-300 shadow-sm"
                        title="تصدير Excel">
                        <svg class="w-5 h-5 text-green-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                    <a href="{{ route('admin.reports.export.pdf', ['search' => $search, 'status' => $status_filter, 'sector' => $sector_filter, 'affiliate' => $affiliate_filter]) }}"
                        target="_blank"
                        class="group flex items-center justify-center p-2.5 bg-white border border-gray-100 rounded-xl hover:border-red-500 hover:bg-red-50 transition-all duration-300 shadow-sm"
                        title="تصدير PDF">
                        <svg class="w-5 h-5 text-red-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z M12 11h4m-4 4h4m-4-8h4" />
                        </svg>
                    </a>
                    <button wire:click="openCreateModal" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        إضافة عميل
                    </button>
                </div>
            </x-slot>
        </x-table.filter-bar>

        <!-- فلاتر إضافية -->
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <!-- فلتر القطاع -->
            <div class="relative min-w-[180px] group">
                <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none z-10">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <select wire:model.live="sector_filter" class="w-full appearance-none pl-9 pr-10 py-2.5 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-sm transition-all text-sm font-bold text-gray-700 hover:border-gray-300">
                    <option value="">جميع القطاعات</option>
                    @foreach(['العقارات', 'التقنية والبرمجة', 'التسويق والدعاية', 'التجارة الإلكترونية', 'التعليم', 'الصحة', 'الخدمات المالية', 'المقاولات والبناء', 'المطاعم والكافيهات', 'أخرى'] as $sec)
                    <option value="{{ $sec }}">{{ $sec }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- فلتر المسوق -->
            <div class="relative min-w-[180px] group">
                <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none z-10">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <select wire:model.live="affiliate_filter" class="w-full appearance-none pl-9 pr-10 py-2.5 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-sm transition-all text-sm font-bold text-gray-700 hover:border-gray-300">
                    <option value="">جميع المسوقين</option>
                    @foreach($affiliates as $affiliate)
                    <option value="{{ $affiliate->id }}">{{ $affiliate->name }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- زر إعادة تعيين الفلاتر -->
            @if($sector_filter || $affiliate_filter)
            <button wire:click="$set('sector_filter', ''); $set('affiliate_filter', '')"
                class="px-4 py-2.5 text-sm font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                إعادة تعيين
            </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="text-primary-400 text-sm border-b border-primary-50">
                        @if($columns['client'])
                        <x-table.th field="client_name" :sortField="$sortField" :sortDirection="$sortDirection" label="العميل" />
                        @endif
                        @if($columns['sector'])
                        <x-table.th field="sector" :sortField="$sortField" :sortDirection="$sortDirection" label="القطاع / الأنظمة" />
                        @endif
                        @if($columns['commission'])
                        <x-table.th field="commission_rate" :sortField="$sortField" :sortDirection="$sortDirection" label="العمولة" />
                        @endif
                        @if($columns['affiliate'])
                        <th class="pb-4 font-bold">الشريك (المسوق)</th>
                        @endif
                        @if($columns['date'])
                        <x-table.th field="created_at" :sortField="$sortField" :sortDirection="$sortDirection" label="تاريخ الإضافة" />
                        @endif
                        @if($columns['renewal_date'])
                        <x-table.th field="subscription_renewal_date" :sortField="$sortField" :sortDirection="$sortDirection" label="تاريخ التجديد" />
                        @endif
                        @if($columns['city_phone'])
                        <x-table.th field="city" :sortField="$sortField" :sortDirection="$sortDirection" label="المدينة / الهاتف" />
                        @endif
                        @if($columns['status'])
                        <x-table.th field="status" :sortField="$sortField" :sortDirection="$sortDirection" label="الحالة" />
                        @endif
                        @if($columns['actions'])
                        <th class="pb-4 font-bold text-left">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($leads as $lead)
                    <tr class="group hover:bg-gray-50 transition-colors duration-200">
                        @if($columns['client'])
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center text-primary-600 font-bold">
                                    {{ mb_substr($lead->client_name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.leads.show', $lead->id) }}" class="font-bold text-gray-900 block hover:text-primary-600 transition text-right">
                                        {{ $lead->client_name }}
                                    </a>
                                    <span class="text-xs text-gray-500">{{ $lead->company_name }}</span>
                                </div>
                            </div>
                        </td>
                        @endif
                        @if($columns['sector'])
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
                        @endif
                        @if($columns['commission'])
                        <td class="py-4">
                            @if($lead->commission_type === 'fixed')
                            <span class="text-sm font-bold text-green-600">{{ number_format($lead->commission_rate) }} ريال</span>
                            @else
                            <span class="text-sm font-bold text-primary-600">{{ $lead->commission_rate }}%</span>
                            @endif
                        </td>
                        @endif
                        @if($columns['affiliate'])
                        <td class="py-4">
                            @if($lead->users->count() > 0)
                            <button wire:click="openAffiliatesModal({{ $lead->id }})"
                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary-50 text-primary-700 rounded-xl text-xs font-bold hover:bg-primary-100 transition shadow-sm border border-primary-100 group">
                                <svg class="w-4 h-4 text-primary-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>عرض المسوقين ({{ $lead->users->count() }})</span>
                            </button>
                            @else
                            <span class="text-xs text-gray-400 font-medium">لا يوجد مسوق</span>
                            @endif
                        </td>
                        @endif
                        @if($columns['date'])
                        <td class="py-4">
                            <p class="text-sm font-bold text-primary-600">{{ $lead->created_at->format('Y-m-d') }}</p>
                        </td>
                        @endif
                        @if($columns['renewal_date'])
                        <td class="py-4">
                            @if($lead->subscription_renewal_date)
                                <p class="text-sm font-bold text-green-600">{{ $lead->subscription_renewal_date->format('Y-m-d') }}</p>
                                @if($lead->subscription_renewal_date->diffInDays(now()) <= 30)
                                    <span class="text-xs text-orange-600 font-medium">ينتج خلال {{ $lead->subscription_renewal_date->diffInDays(now()) }} يوم</span>
                                @endif
                            @else
                                <span class="text-sm text-gray-400">غير محدد</span>
                            @endif
                        </td>
                        @endif
                        @if($columns['city_phone'])
                        <td class="py-4">
                            <p class="text-sm font-bold text-primary-800">{{ $lead->city }}</p>
                            <p class="text-xs text-primary-400">{{ $lead->client_phone }}</p>
                        </td>
                        @endif
                        @if($columns['status'])
                        <td class="py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ \App\Models\Lead::statusBadgeClass($lead->status) }}">
                                {{ \App\Models\Lead::statusLabel($lead->status) }}
                            </span>
                        </td>
                        @endif
                        @if($columns['actions'])
                        <td class="py-4">
                            <div class="flex gap-2">
                                <button wire:click="openViewModal({{ $lead->id }})" class="p-2 text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition-all duration-300" title="عرض التفاصيل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                @if($lead->status !== 'sold')
                                <button wire:confirm="هل أنت متأكد من تغيير حالة المبيعة إلى تم البيع؟ سيتم تأكيد استحقاق العمولة للمسوقين." wire:click="updateStatus({{ $lead->id }}, 'sold', true)" class="p-2 text-green-600 bg-green-50 hover:bg-green-100 rounded-xl transition-all duration-300" title="تأكيد المبيعة والدفع">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                                @endif
                                <button wire:click="editLead({{ $lead->id }})" class="p-2 text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition-all duration-300" title="تعديل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:confirm="هل أنت متأكد من حذف هذا العميل؟ لا يمكن التراجع عن هذا الإجراء." wire:click="deleteLead({{ $lead->id }})" class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-all duration-300" title="حذف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="font-bold">لا يوجد مبيعات حالياً</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $leads->links() }}
        </div>
    </div>
    <!-- Professional View Details Modal -->
    <template x-teleport="body">
        <div x-data="{ showViewModal: $wire.entangle('showViewModal') }"
            x-show="showViewModal"
            x-on:keydown.escape.window="showViewModal = false"
            class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-primary-900/60 backdrop-blur-sm transition-opacity" @click="showViewModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    <!-- Modal Header -->
                    <div class="px-8 py-6 border-b border-primary-50 flex items-center justify-between bg-gradient-to-r from-primary-50 to-white">
                        <div>
                            <h3 class="text-2xl font-black text-primary-900">تفاصيل العميل</h3>
                            <p class="text-primary-500 text-sm font-medium">عرض المعلومات الكاملة للعميل والمسوقين</p>
                        </div>
                        <button @click="showViewModal = false" class="p-2 rounded-full hover:bg-white hover:shadow-md transition-all text-primary-400 hover:text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @if($viewLead)
                    <div class="p-8 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Client Basic Info -->
                            <div class="space-y-6">
                                <div class="bg-primary-50/50 p-6 rounded-3xl border border-primary-100">
                                    <h4 class="text-xs font-black text-primary-400 uppercase tracking-widest mb-4">معلومات العميل</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-primary-400 font-bold">الاسم الكامل</p>
                                                <p class="font-black text-primary-900 text-lg line-height-1">{{ $viewLead->client_name }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-primary-400 font-bold">رقم الهاتف</p>
                                                <p class="font-black text-primary-900 text-lg leading-none">{{ $viewLead->client_phone }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div class="overflow-hidden">
                                                <p class="text-[10px] text-primary-400 font-bold">البريد الإلكتروني</p>
                                                <p class="font-black text-primary-900 text-xs truncate">{{ $viewLead->email ?: '-' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50/50 p-6 rounded-3xl border border-gray-100">
                                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">تفاصيل العمل</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-[10px] text-gray-400 font-bold">اسم الشركة</p>
                                            <p class="font-black text-primary-900">{{ $viewLead->company_name ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 font-bold">المدينة</p>
                                            <p class="font-black text-primary-900">{{ $viewLead->city ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 font-bold">القطاع</p>
                                            <p class="font-black text-primary-900">{{ $viewLead->sector ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 font-bold">الحالة</p>
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-black border {{ \App\Models\Lead::statusBadgeClass($viewLead->status) }}">
                                                {{ \App\Models\Lead::statusLabel($viewLead->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Marketers & Systems -->
                            <div class="space-y-6">
                                <div class="bg-primary-50/50 p-6 rounded-3xl border border-primary-100">
                                    <h4 class="text-xs font-black text-primary-400 uppercase tracking-widest mb-4">المسوقون المعينون</h4>
                                    <div class="space-y-3">
                                        @forelse($viewLead->users as $marketer)
                                        <div class="flex items-center justify-between p-3 bg-white rounded-2xl border border-primary-50 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-black text-xs">
                                                    {{ substr($marketer->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="text-xs font-black text-primary-900 line-height-1">{{ $marketer->name }}</p>
                                                    <p class="text-[10px] text-primary-400 font-bold">{{ $marketer->phone ?: 'لا يوجد هاتف' }}</p>
                                                </div>
                                            </div>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] border font-black {{ $marketer->getRankBadgeColor() }}">
                                                {{ $marketer->getRankIcon() }} {{ $marketer->getRankLabel() }}
                                            </span>
                                        </div>
                                        @empty
                                        <p class="text-xs text-gray-400 text-center py-2">لا يوجد مسوقون معينون</p>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="bg-amber-50/30 p-6 rounded-3xl border border-amber-100">
                                    <h4 class="text-xs font-black text-amber-500 uppercase tracking-widest mb-4">الأنظمة المقترحة</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($viewLead->recommended_systems ?? [] as $sys)
                                        <div class="p-2 bg-white rounded-2xl border border-amber-50 shadow-sm flex flex-col items-center gap-1 group transition-all hover:shadow-md">
                                            <img src="{{ asset('images/systems/'.$sys.'.png') }}" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform">
                                            <span class="text-[9px] font-black text-amber-700 uppercase">{{ $sys }}</span>
                                        </div>
                                        @empty
                                        <p class="text-xs text-gray-400 text-center py-2 w-full">لا يوجد أنظمة مقترحة</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        @if($viewLead->notes)
                        <div class="mt-8 p-6 bg-yellow-50/50 rounded-3xl border border-yellow-100">
                            <h4 class="text-xs font-black text-yellow-600 uppercase tracking-widest mb-3">ملاحظات إضافية</h4>
                            <div class="text-sm text-yellow-800 font-medium whitespace-pre-line leading-relaxed">
                                {{ $viewLead->notes }}
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 rounded-b-[2.5rem] flex justify-end gap-3">
                        <button @click="showViewModal = false" class="px-8 py-3 bg-white border border-gray-200 text-gray-600 rounded-2xl font-bold hover:bg-gray-50 hover:shadow-sm transition-all text-sm">
                            إغلاق
                        </button>
                        <button wire:click="editLead({{ $viewLead->id }}); showViewModal = false" class="px-8 py-3 bg-primary-600 text-white rounded-2xl font-bold hover:bg-primary-700 shadow-lg shadow-primary-200 transition-all text-sm">
                            تعديل البيانات
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </template>

    <!-- Marketers Details Modal -->
    <template x-teleport="body">
        <div x-data="{ showAffiliatesModal: $wire.entangle('showAffiliatesModal') }"
            x-show="showAffiliatesModal"
            x-on:keydown.escape.window="showAffiliatesModal = false"
            class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-primary-900/60 backdrop-blur-sm transition-opacity" @click="showAffiliatesModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    <!-- Modal Header -->
                    <div class="px-8 py-6 border-b border-primary-50 flex items-center justify-between bg-gradient-to-r from-primary-50 to-white">
                        <div>
                            <h3 class="text-2xl font-black text-primary-900">المسوقين</h3>
                            <p class="text-primary-500 text-sm font-medium">عرض المسوقين وتوزيع العمولات</p>
                        </div>
                        <button @click="showAffiliatesModal = false" class="p-2 rounded-full hover:bg-white hover:shadow-md transition-all text-primary-400 hover:text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @if($viewAffiliatesLead)
                    <div class="p-8 max-h-[70vh] overflow-y-auto">
                        <div class="space-y-4">
                            @php
                            $totalCommission = $viewAffiliatesLead->commission_type === 'fixed'
                            ? $viewAffiliatesLead->commission_rate
                            : ($viewAffiliatesLead->expected_deal_value * $viewAffiliatesLead->commission_rate / 100);
                            @endphp

                            @forelse($viewAffiliatesLead->users as $marketer)
                            @php
                            $pivot = $marketer->pivot;
                            if ($pivot->fixed_amount) {
                            $baseShare = $pivot->fixed_amount;
                            $shareLabel = number_format($pivot->fixed_amount) . ' ريال (ثابت)';
                            } elseif ($pivot->commission_share) {
                            $baseShare = ($totalCommission * $pivot->commission_share) / 100;
                            $shareLabel = $pivot->commission_share . '%';
                            } else {
                            $baseShare = $totalCommission / ($viewAffiliatesLead->users->count() ?: 1);
                            $shareLabel = 'مقسم بالتساوي';
                            }
                            $finalShare = $baseShare * $marketer->commission_multiplier;
                            @endphp

                            <div class="p-4 bg-primary-50/50 rounded-3xl border border-primary-100 flex flex-col gap-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600 font-black">
                                            {{ mb_substr($marketer->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-primary-900">{{ $marketer->name }}</p>
                                            <div class="flex items-center gap-2">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] border font-black {{ $marketer->getRankBadgeColor() }}">
                                                    {{ $marketer->getRankIcon() }} {{ $marketer->getRankLabel() }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="bg-white px-2 py-1 rounded-lg border border-primary-200 text-primary-600 text-xs font-bold">
                                            {{ $shareLabel }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-primary-100/50">
                                    <div class="flex items-center gap-2 text-xs text-primary-500 font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        {{ $marketer->phone ?: 'لا يوجد هاتف' }}
                                    </div>
                                    @if($viewAffiliatesLead->status === 'sold' && $viewAffiliatesLead->is_verified)
                                    <div class="bg-green-100 text-green-700 px-3 py-1 rounded-xl font-black text-sm">
                                        {{ number_format($finalShare, 2) }} ر.س
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8 text-gray-400 font-bold">
                                لا يوجد مسوقين لهذه المبيعة
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 rounded-b-[2.5rem] flex justify-end">
                        <button @click="showAffiliatesModal = false" class="px-8 py-3 bg-white border border-gray-200 text-gray-600 rounded-2xl font-bold hover:bg-gray-50 hover:shadow-sm transition-all text-sm">
                            إغلاق
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>