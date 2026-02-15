<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Lead;

new class extends Component {
    use WithFileUploads;
    public $search = '';
    public $status_filter = '';
    public $sector_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $showModal = false;

    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Form fields
    public $client_name = '';
    public $company_name = '';
    public $city = '';
    public $client_phone = '';
    public $email = '';
    public $region = '';
    public $sector = '';
    public $needs = '';
    public $recommended_systems = [];

    public $leadId = null;

    // New Service fields
    public $new_service_name = '';
    public $new_service_id = '';
    public $new_service_image = null;
    public $show_add_service = false;

    // New Sector fields
    public $new_sector_name_input = '';
    public $show_add_sector = false;

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
            'new_service_image' => 'nullable|image|max:1024', // 1MB Max
        ]);

        $available = json_decode(\App\Models\Setting::get('available_systems', '[]'), true) ?: [
            ['name' => 'قيود', 'id' => 'qoyod'],
            ['name' => 'دفترة', 'id' => 'daftra'],
        ];

        // Check if ID already exists
        foreach ($available as $sys) {
            if ($sys['id'] === $this->new_service_id) {
                $this->addError('new_service_id', 'هذا المعرف موجود بالفعل');
                return;
            }
        }

        // Handle Image Upload
        if ($this->new_service_image) {
            $path = public_path('images/systems');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $this->new_service_image->storeAs('images/systems', $this->new_service_id . '.png', 'public_uploads');
        }

        $available[] = [
            'name' => $this->new_service_name,
            'id' => $this->new_service_id
        ];

        \App\Models\Setting::set('available_systems', json_encode($available));

        $this->recommended_systems[] = $this->new_service_id;
        $this->new_service_name = '';
        $this->new_service_id = '';
        $this->new_service_image = null;
        $this->show_add_service = false;

        $this->dispatch('toast', type: 'success', message: 'تم إضافة الخدمة الجديدة بنجاح');
    }

    public function saveLead()
    {
        $this->validate([
            'client_name' => 'required|min:3',
            'client_phone' => 'required',
            'city' => 'required',
            'email' => 'nullable|email',
            'sector' => 'nullable|string|max:255',
        ]);

        if ($this->leadId) {
            $lead = auth()->user()->leads()->find($this->leadId);
            $lead->update([
                'client_name' => $this->client_name,
                'company_name' => $this->company_name,
                'city' => $this->city,
                'client_phone' => $this->client_phone,
                'email' => $this->email,
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
                'email' => $this->email,
                'sector' => $this->sector,
                'needs' => $this->needs,
                'recommended_systems' => $this->recommended_systems,
                'status' => 'under_review',
            ]);
            $message = 'تم إضافة العميل بنجاح!';
        }

        $this->reset(['leadId', 'client_name', 'company_name', 'city', 'client_phone', 'email', 'region', 'sector', 'needs', 'recommended_systems', 'showModal']);
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
        $this->email = $lead->email;
        $this->sector = $lead->sector;
        $this->needs = $lead->needs;
        $this->recommended_systems = $lead->recommended_systems ?? [];

        // Determine region from city
        $regionsWithCities = \App\Services\SaudiGeoService::getRegionsWithCities();
        foreach ($regionsWithCities as $regionName => $cities) {
            if (in_array($lead->city, $cities)) {
                $this->region = $regionName;
                break;
            }
        }

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
        $this->reset(['leadId', 'client_name', 'company_name', 'city', 'client_phone', 'email', 'region', 'sector', 'needs', 'recommended_systems']);
        $this->showModal = true;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSector($value)
    {
        if ($value === 'أخرى') {
            $this->show_add_sector = true;
            $this->sector = '';
        }
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
                        ->orWhere('client_phone', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status_filter, function ($query) {
                $query->where('status', $this->status_filter);
            })
            ->when($this->sector_filter, function ($query) {
                $query->where('sector', $this->sector_filter);
            })
            ->when($this->date_from, fn($query) => $query->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to, fn($query) => $query->whereDate('created_at', '<=', $this->date_to))
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        return [
            'leads' => $leads,
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

<div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden">
    <!-- Decorative depth elements -->
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-slate-100 rounded-full blur-3xl opacity-50"></div>

    <!-- Header -->
    <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">سجل العملاء</h3>
            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mt-1">إجمالي {{ count($leads) }} سجلات مسجلة</p>
        </div>
        <button @click="$wire.openModal()"
            class="inline-flex items-center justify-center gap-2 px-6 py-4 bg-primary-900 hover:bg-primary-950 text-white rounded-2xl font-black shadow-xl shadow-primary-200/50 transition-all transform active:scale-95">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>إضافة عميل جديد</span>
        </button>
    </div>

    <!-- Enhanced Filters -->
    <div class="relative flex flex-col gap-3 mb-6 bg-white p-4 rounded-3xl border border-slate-200 shadow-sm">
        <div class="relative w-full group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-primary-600">
                <svg class="h-5 w-5 text-slate-400 group-focus-within:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" wire:model.live="search"
                class="w-full pr-4 pl-11 py-3.5 bg-slate-50 border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-100 focus:border-primary-300 transition-all font-bold text-sm text-slate-900 placeholder-slate-400"
                placeholder="بحث بالاسم، الشركة، الهاتف، البريد...">
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2">
            <!-- Status Filter -->
            <div class="flex items-center gap-1.5 bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
                <button wire:click="$set('status_filter', '')"
                    class="px-3 py-2 rounded-xl text-[10px] font-black tracking-widest uppercase transition-all {{ $status_filter === '' ? 'bg-primary-900 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-200' }}">
                    الكل
                </button>
                <button wire:click="$set('status_filter', 'under_review')"
                    class="px-3 py-2 rounded-xl text-[10px] font-black tracking-widest uppercase transition-all {{ $status_filter === 'under_review' ? 'bg-primary-900 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-200' }}">
                    المراجعة
                </button>
                <button wire:click="$set('status_filter', 'sold')"
                    class="px-3 py-2 rounded-xl text-[10px] font-black tracking-widest uppercase transition-all {{ $status_filter === 'sold' ? 'bg-primary-900 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-200' }}">
                    مباع
                </button>
                <button wire:click="$set('status_filter', 'contacting')"
                    class="px-3 py-2 rounded-xl text-[10px] font-black tracking-widest uppercase transition-all {{ $status_filter === 'contacting' ? 'bg-primary-900 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-200' }}">
                    تواصل
                </button>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <!-- Sector Filter -->
                <div class="relative min-w-[180px] group">
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none z-10">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Date From Filter -->
                <div class="relative min-w-[160px]">
                    <input type="date" wire:model.live="date_from"
                        class="w-full px-4 py-2.5 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 shadow-sm transition-all text-sm font-bold text-gray-700"
                        placeholder="من تاريخ">
                </div>

                <!-- Date To Filter -->
                <div class="relative min-w-[160px]">
                    <input type="date" wire:model.live="date_to"
                        class="w-full px-4 py-2.5 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 shadow-sm transition-all text-sm font-bold text-gray-700"
                        placeholder="إلى تاريخ">
                </div>

                <!-- Reset Filters Button -->
                @if($sector_filter || $date_from || $date_to)
                <button wire:click="$set('sector_filter', ''); $set('date_from', ''); $set('date_to', '')"
                    class="px-3 py-2.5 text-sm font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    إعادة تعيين
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="relative overflow-x-auto bg-white rounded-3xl border border-slate-200 shadow-sm">
        <table class="w-full text-right border-collapse">
            <thead>
                <tr class="text-slate-400 text-xs font-black uppercase tracking-widest bg-slate-50/50">
                    <th class="py-5 pr-6 text-right">
                        <button wire:click="sortBy('client_name')" class="flex items-center gap-2 hover:text-primary-600 transition-colors">
                            <span>اسم العميل</span>
                            @if($sortField === 'client_name')
                            <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            @endif
                        </button>
                    </th>
                    <th class="py-5 text-right">
                        <button wire:click="sortBy('sector')" class="flex items-center gap-2 hover:text-primary-600 transition-colors">
                            <span>القطاع / الأنظمة</span>
                            @if($sortField === 'sector')
                            <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            @endif
                        </button>
                    </th>
                    <th class="py-5 text-right">
                        <button wire:click="sortBy('city')" class="flex items-center gap-2 hover:text-primary-600 transition-colors">
                            <span>المدينة</span>
                            @if($sortField === 'city')
                            <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            @endif
                        </button>
                    </th>
                    <th class="py-5 text-right">معلومات العمولة</th>
                    <th class="py-5 text-right">رقم الهاتف</th>
                    <th class="py-5 text-right">
                        <button wire:click="sortBy('created_at')" class="flex items-center gap-2 hover:text-primary-600 transition-colors">
                            <span>تاريخ الإضافة</span>
                            @if($sortField === 'created_at')
                            <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            @endif
                        </button>
                    </th>
                    <th class="py-5 text-right">
                        <button wire:click="sortBy('status')" class="flex items-center gap-2 hover:text-primary-600 transition-colors">
                            <span>الحالة</span>
                            @if($sortField === 'status')
                            <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            @endif
                        </button>
                    </th>
                    <th class="py-5 text-left pl-6">العمليات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($leads as $lead)
                <tr class="group hover:bg-slate-50/80 transition-all duration-300">
                    <td class="py-5 pr-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-extrabold text-base shadow-inner group-hover:bg-primary-900 group-hover:text-white transition-all duration-500">
                                {{ substr($lead->client_name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-black text-slate-900 text-base leading-none mb-1.5">{{ $lead->client_name }}</p>
                                @if($lead->company_name)
                                <p class="text-xs text-slate-400 font-black uppercase tracking-wider">{{ $lead->company_name }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-5">
                        <div class="space-y-1.5">
                            @if($lead->sector)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-gray-50 text-gray-600 border border-gray-100">
                                {{ $lead->sector }}
                            </span>
                            @endif
                            @if($lead->recommended_systems)
                            <div class="flex gap-1.5 flex-wrap">
                                @foreach($lead->recommended_systems as $sysId)
                                <div class="relative w-8 h-8 rounded-xl bg-gradient-to-br from-white to-gray-50 border border-primary-100 p-1 group-hover:scale-110 group-hover:shadow-md transition-all duration-300 hover:border-primary-300">
                                    <img src="{{ asset('images/systems/'.$sysId.'.png') }}"
                                        class="w-full h-full object-contain"
                                        title="{{ $sysId }}"
                                        alt="{{ $sysId }}"
                                        onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($sysId) }}&background=f1f5f9&color=64748b'">
                                    <div class="absolute inset-0 bg-primary-500/0 group-hover:bg-primary-500/5 rounded-xl transition-colors"></div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-5">
                        <span class="text-base font-black text-primary-900">{{ $lead->city }}</span>
                    </td>
                    <td class="py-5">
                        @php
                        $userPivot = $lead->users->where('id', auth()->id())->first()?->pivot;
                        $totalCommission = $lead->commission_type === 'fixed'
                        ? $lead->commission_rate
                        : ($lead->expected_deal_value * $lead->commission_rate / 100);

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

                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                @if($lead->commission_type === 'fixed')
                                <span class="text-base font-black text-emerald-600">{{ number_format($totalCommission) }} ريال</span>
                                @elseif($lead->commission_rate)
                                <span class="text-base font-black text-primary-600">{{ $lead->commission_rate }}%</span>
                                @else
                                <span class="text-gray-300 text-sm">-</span>
                                @endif

                                @if($isShared)
                                <div class="w-5 h-5 rounded-full bg-amber-50 flex items-center justify-center text-amber-500" title="عمولة مشتركة">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                    </svg>
                                </div>
                                @endif
                            </div>

                            @if($lead->status === 'sold' && $lead->is_verified)
                            <div class="bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100 flex items-center justify-between">
                                <span class="text-xs text-emerald-600 font-black">نصيبك</span>
                                <span class="text-sm font-black text-emerald-700">{{ number_format($finalShare, 2) }} ر.س</span>
                            </div>
                            @elseif($totalCommission > 0)
                            <div class="bg-primary-50 px-3 py-1.5 rounded-xl border border-primary-100 flex items-center justify-between">
                                <span class="text-xs text-primary-500 font-bold uppercase tracking-tighter">المتوقع</span>
                                <span class="text-sm font-black text-primary-900">{{ number_format($finalShare, 2) }} ر.س</span>
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-5">
                        <span class="text-base font-black text-primary-900 tabular-nums" dir="ltr">{{ $lead->client_phone }}</span>
                    </td>
                    <td class="py-5">
                        <div class="text-base font-black text-primary-900">{{ $lead->created_at->format('Y/m/d') }}</div>
                        <div class="text-xs text-primary-300 font-bold">{{ $lead->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="py-5">
                        @php
                        $statusConfig = [
                        'under_review' => ['class' => 'bg-amber-50 text-amber-600 border-amber-100', 'label' => 'قيد المراجعة'],
                        'sold' => ['class' => 'bg-emerald-50 text-emerald-600 border-emerald-100', 'label' => 'مباع'],
                        'contacting' => ['class' => 'bg-blue-50 text-blue-600 border-blue-100', 'label' => 'جاري التواصل'],
                        'cancelled' => ['class' => 'bg-rose-50 text-rose-600 border-rose-100', 'label' => 'ملغي']
                        ];
                        $config = $statusConfig[$lead->status] ?? ['class' => 'bg-gray-50 text-gray-500 border-gray-100', 'label' => $lead->status];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-black border {{ $config['class'] }}">
                            {{ $config['label'] }}
                        </span>
                    </td>
                    <td class="py-5 pl-4">
                        <div class="relative flex justify-end" x-data="{ open: false, showDeleteModal: false, deletingId: null }">
                            <button @click="open = !open" @click.away="open = false" class="text-primary-300 hover:text-primary-900 transition-colors p-2 rounded-xl hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>
                            <div x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                class="absolute left-0 mt-10 w-48 bg-white rounded-2xl shadow-2xl border border-primary-50 z-20 py-2"
                                style="display: none;">
                                <button wire:click="editLead({{ $lead->id }})" class="block w-full text-right px-4 py-3 text-sm font-black text-primary-900 hover:bg-primary-50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </div>
                                        تعديل البيانات
                                    </div>
                                </button>
                                <button @click="deletingId = {{ $lead->id }}; showDeleteModal = true; open = false" class="block w-full text-right px-4 py-3 text-sm font-black text-rose-600 hover:bg-rose-50 transition-colors border-t border-gray-50">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </div>
                                        حذف السجل
                                    </div>
                                </button>
                            </div>

                            <!-- Professional Delete Confirmation Modal -->
                            <template x-teleport="body">
                                <div x-show="showDeleteModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
                                    <div class="fixed inset-0 bg-primary-900/60 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>

                                    <div class="flex min-h-full items-center justify-center p-4">
                                        <div class="relative w-full max-w-sm transform overflow-hidden rounded-[2.5rem] bg-white p-10 shadow-2xl transition-all text-center">
                                            <div class="mb-8">
                                                <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-3xl bg-rose-50 text-rose-600 mb-6 group">
                                                    <svg class="h-12 w-12 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                </div>
                                                <h3 class="text-3xl font-black text-primary-900 mb-4">متأكد من الحذف؟</h3>
                                                <p class="text-primary-500 font-medium leading-relaxed">سيتم مسح بيانات العميل بالكامل، لا يمكن العودة بعد تأكيد هذا الإجراء.</p>
                                            </div>

                                            <div class="flex flex-col gap-4">
                                                <button
                                                    @click="$wire.deleteLead(deletingId); showDeleteModal = false"
                                                    class="w-full py-5 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-black shadow-xl shadow-rose-200 transition-all transform active:scale-95">
                                                    تأكيد الحذف النهائي
                                                </button>
                                                <button
                                                    @click="showDeleteModal = false"
                                                    class="w-full py-5 bg-primary-50 text-primary-900 rounded-2xl font-black hover:bg-primary-100 transition-all">
                                                    إلغاء الأمر
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
                    <td colspan="10" class="py-20 text-center">
                        <div class="flex flex-col items-center justify-center max-w-xs mx-auto">
                            <div class="w-24 h-24 bg-primary-50 rounded-[2rem] flex items-center justify-center text-primary-200 mb-6">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-black text-primary-900 mb-2">لا توجد بيانات</h4>
                            <p class="text-primary-400 text-sm font-medium">ابدأ بإضافة أول عميل لك الآن من خلال زر الإضافة بالأعلى</p>
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

                            <div class="form-group">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" wire:model="email" class="form-input" placeholder="example@domain.com" dir="ltr">
                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                                <select wire:model.live="sector" class="form-input flex-1">
                                    <option value="">اختر القطاع</option>
                                    @foreach($available_sectors as $s)
                                    <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                                @error('sector') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                @if($show_add_sector)
                                <div class="mt-2 p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-3 animate-in fade-in slide-in-from-top-2">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-gray-500 uppercase">اسم القطاع الجديد</label>
                                        <input type="text" wire:model="new_sector_name_input" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-primary-500 text-sm font-bold" placeholder="مثال: التصنيع">
                                        @error('new_sector_name_input') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="$wire.show_add_sector = false" class="px-3 py-1.5 text-[10px] font-bold text-gray-500 hover:text-gray-700">إلغاء</button>
                                        <button type="button" wire:click="addNewSector" class="px-3 py-1.5 bg-primary-900 text-white rounded-lg text-[10px] font-black shadow-sm">تأكيد</button>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">المنطقة *</label>
                                <select wire:model.live="region" class="form-select">
                                    <option value="">اختر المنطقة</option>
                                    @foreach($regions as $regionName)
                                    <option value="{{ $regionName }}">{{ $regionName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">المدينة *</label>
                                <select wire:model="city" class="form-select" {{ !$region ? 'disabled' : '' }}>
                                    <option value="">{{ $region ? 'اختر المدينة' : 'اختر المنطقة أولاً' }}</option>
                                    @if($region && isset($regionsWithCities[$region]))
                                    @foreach($regionsWithCities[$region] as $cityName)
                                    <option value="{{ $cityName }}">{{ $cityName }}</option>
                                    @endforeach
                                    @endif
                                </select>
                                @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">الاحتياجات</label>
                            <textarea wire:model="needs" class="form-textarea" rows="3" placeholder="وصف احتياجات العميل"></textarea>
                        </div>

                        <div class="form-group">
                            <div class="flex items-center justify-between mb-2 mr-1">
                                <label class="form-label !mb-0">الخدمة المقترحة</label>
                                <!-- <button type="button" wire:click="$toggle('show_add_service')" class="text-primary-600 hover:text-primary-900 flex items-center gap-1 text-[10px] font-black group">
                                    <svg class="w-4 h-4 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                                    </svg>
                                    إضافة خدمة جديدة
                                </button> -->
                            </div>
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

                                <!-- Add New Service (+) Card -->
                                <button type="button"
                                    wire:click="$toggle('show_add_service')"
                                    class="relative group flex flex-col items-center justify-center p-4 rounded-xl border-2 border-dashed border-gray-200 hover:border-primary-400 hover:bg-primary-50/30 transition-all duration-200 min-h-[120px]">
                                    <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 mb-3 group-hover:bg-primary-100 group-hover:text-primary-600 transition-colors shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </div>
                                    <span class="font-black text-[10px] text-gray-500 uppercase tracking-widest group-hover:text-primary-900 transition-colors">إضافة أخرى</span>
                                </button>
                            </div>

                            <!-- New Service Input Form -->
                            @if($show_add_service)
                            <div class="mt-4 p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-4 animate-in fade-in slide-in-from-top-2">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-gray-500 uppercase">اسم الخدمة</label>
                                        <input type="text" wire:model="new_service_name" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-primary-500 text-sm font-bold" placeholder="مثال: زوهو">
                                        @error('new_service_name') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-gray-500 uppercase">المعرف (انجليزي)</label>
                                        <input type="text" wire:model="new_service_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-primary-500 text-sm font-bold" placeholder="zoho" dir="ltr">
                                        @error('new_service_id') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-span-2 space-y-2">
                                        <label class="text-[10px] font-black text-gray-500 uppercase">شعار الخدمة (اختياري)</label>
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden bg-white">
                                                @if($new_service_image)
                                                <img src="{{ $new_service_image->temporaryUrl() }}" class="w-full h-full object-contain">
                                                @else
                                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                @endif
                                            </div>
                                            <input type="file" wire:model="new_service_image" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                                        </div>
                                        @error('new_service_image') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="$wire.show_add_service = false" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">إلغاء</button>
                                    <button type="button" wire:click="addNewService" class="px-4 py-2 bg-primary-900 text-white rounded-lg text-xs font-black shadow-lg shadow-primary-200">تأكيد الإضافة</button>
                                </div>
                            </div>
                            @endif
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