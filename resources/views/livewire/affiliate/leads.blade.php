<?php

use Livewire\Volt\Component;
use App\Models\Lead;
use App\Models\ReferralLink;

new class extends Component {
    public $search = '';
    public $status_filter = '';
    public $showModal = false;

    // Form fields
    public $client_name = '';
    public $company_name = '';
    public $city = '';
    public $client_phone = '';
    public $needs = '';
    public $recommended_systems = [];

    public function addLead()
    {
        $this->validate([
            'client_name' => 'required|min:3',
            'client_phone' => 'required',
            'city' => 'required',
        ]);

        auth()->user()->leads()->create([
            'client_name' => $this->client_name,
            'company_name' => $this->company_name,
            'city' => $this->city,
            'client_phone' => $this->client_phone,
            'needs' => $this->needs,
            'recommended_systems' => $this->recommended_systems,
            'status' => 'under_review',
        ]);

        $this->reset(['client_name', 'company_name', 'city', 'client_phone', 'needs', 'recommended_systems', 'showModal']);
        $this->dispatch('lead-added');
        $this->dispatch('notify', message: 'تم إضافة العميل بنجاح!');
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
                ['name' => 'قيود', 'id' => 'qoyod', 'logo' => 'https://www.qoyod.com/wp-content/uploads/2021/05/logo_qoyod.svg'],
                ['name' => 'أودو', 'id' => 'odoo', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/e/e0/Odoo_logo.svg'],
                ['name' => 'دفترة', 'id' => 'daftra', 'logo' => 'https://www.daftra.com/images/daftra-logo.png'],
            ]
        ];
    }
}; ?>

<div class="space-y-10 py-4">
    <!-- Header & Actions -->
    <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-8 px-2">
        <div class="space-y-4">
            <span class="badge-primary !px-4 !py-1.5">{{ __('إدارة المبيعات') }}</span>
            <h3 class="heading-md !mb-0">{{ __('قائمة العملاء المحتملين') }}</h3>
            <p class="text-body !text-sm">{{ __('تابع نمو أعمالك وراقب رحلة كل عميل نحو النجاح والبيع.') }}</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-4">
            <div class="relative w-full sm:w-80 group">
                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-deep-blue-400 group-focus-within:text-cyber-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" wire:model.live="search" placeholder="بحث بالاسم، الشركة، الهاتف..."
                    class="input-modern !py-4 !pr-12">
            </div>

            <button @click="$wire.set('showModal', true)" class="btn-primary w-full sm:w-auto !py-4 !px-8">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('إضافة عميل جديد') }}
            </button>
        </div>
    </div>

    <!-- Filters & Visualization -->
    <div class="flex flex-wrap items-center gap-3 px-2">
        <button wire:click="$set('status_filter', '')" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $status_filter === '' ? 'bg-deep-blue-900 text-white shadow-soft' : 'bg-white text-deep-blue-400 border border-deep-blue-100 hover:border-cyber-200 hover:text-cyber-600' }}">
            {{ __('الكل') }}
        </button>
        <button wire:click="$set('status_filter', 'under_review')" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $status_filter === 'under_review' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20' : 'bg-white text-amber-500 border border-amber-100/50 hover:bg-amber-50' }}">
            {{ __('تحت المراجعة') }}
        </button>
        <button wire:click="$set('status_filter', 'contacting')" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $status_filter === 'contacting' ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/20' : 'bg-white text-primary-600 border border-primary-100/50 hover:bg-primary-50' }}">
            {{ __('جاري التواصل') }}
        </button>
        <button wire:click="$set('status_filter', 'sold')" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $status_filter === 'sold' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'bg-white text-emerald-500 border border-emerald-100/50 hover:bg-emerald-50' }}">
            {{ __('تم البيع') }}
        </button>
    </div>

    <!-- Table Section -->
    <div class="neo-card overflow-hidden !bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="py-5 px-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('العميل والمنشأة') }}</th>
                        <th class="py-5 px-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('قنوات التواصل') }}</th>
                        <th class="py-5 px-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('الحالة الحالية') }}</th>
                        <th class="py-5 px-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('الموقع والجغرافيا') }}</th>
                        <th class="py-5 px-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('القيمة المقدرة') }}</th>
                        <th class="py-5 px-8 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('الملف') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-slate-50/50 transition-all duration-300 group">
                        <td class="py-6 px-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center font-black text-slate-400 group-hover:bg-primary-600 group-hover:text-white group-hover:border-primary-600 transition-all duration-500 shadow-sm">
                                    {{ mb_substr($lead->client_name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="block text-sm font-black text-slate-900 mb-0.5 group-hover:text-primary-700 transition-colors">{{ $lead->client_name }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 bg-primary-400 rounded-full"></span>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest opacity-60">{{ $lead->company_name ?: __('فرد') }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-6 px-8">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-black text-slate-900 tracking-wider" dir="ltr">{{ $lead->client_phone }}</span>
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ __('مكالمة / واتساب') }}</span>
                            </div>
                        </td>
                        <td class="py-6 px-8">
                            @php
                            $statusConfig = [
                            'under_review' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100/50', 'label' => 'تحت المراجعة'],
                            'contacting' => ['bg' => 'bg-primary-50', 'text' => 'text-primary-600', 'border' => 'border-primary-100/50', 'label' => 'جاري التواصل'],
                            'sold' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100/50', 'label' => 'تم البيع'],
                            'cancelled' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-100/50', 'label' => 'ملغي']
                            ];
                            $config = $statusConfig[$lead->status] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-500', 'border' => 'border-slate-100', 'label' => $lead->status];
                            @endphp
                            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full {{ $config['bg'] }} {{ $config['text'] }} border {{ $config['border'] }} shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                                <span class="text-[9px] font-black uppercase tracking-[0.2em]">{{ $config['label'] }}</span>
                            </div>
                        </td>
                        <td class="py-6 px-8">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-xs font-black text-slate-500 uppercase tracking-widest">{{ $lead->city }}</span>
                            </div>
                        </td>
                        <td class="py-6 px-8">
                            <div class="flex items-baseline gap-1">
                                <span class="text-sm font-black text-slate-900">0.00</span>
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">ر.س</span>
                            </div>
                        </td>
                        <td class="py-6 px-8 text-left">
                            <button class="w-10 h-10 rounded-xl bg-slate-50 text-slate-300 hover:bg-slate-900 group/btn hover:text-white transition-all duration-300 shadow-sm flex items-center justify-center">
                                <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-24 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-24 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center text-slate-200 mb-8 border border-slate-100/50 animate-float">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-black text-slate-900 tracking-tighter mb-2">{{ __('لا توجد سجلات حالياً') }}</h4>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">{{ __('ابدأ بإضافة أول عميل محتمل لتبدأ رحلة الربح الفاخرة') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Dialog -->
    <template x-teleport="body">
        <div x-show="$wire.showModal"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 overflow-y-auto px-4" style="display: none; z-index: 1000 !important;">
            <div class="flex items-center justify-center min-h-screen">
                <div @click="$wire.set('showModal', false)" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity"></div>

                <div class="bg-white rounded-[3.5rem] shadow-premium relative w-full max-w-4xl overflow-hidden border border-white"
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-y-32 scale-90"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    <!-- Decorative Backgrounds -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full blur-[100px] -mr-32 -mt-32 opacity-30"></div>
                    <div class="absolute bottom-0 left-0 w-80 h-80 bg-indigo-100 rounded-full blur-[120px] -ml-40 -mb-40 opacity-30"></div>

                    <div class="relative flex flex-col h-[85vh]">
                        <!-- Modal Header -->
                        <div class="p-12 pb-8 flex items-center justify-between border-b border-slate-50">
                            <div>
                                <h3 class="text-3xl font-black text-slate-900 tracking-tighter">{{ __('إضافة عميل محتمل جديد') }}</h3>
                                <p class="text-xs text-slate-400 font-bold mt-2 uppercase tracking-[0.2em]">{{ __('أدخل تفاصيل العميل بعناية لضمان متابعة دقيقة') }}</p>
                            </div>
                            <button @click="$wire.set('showModal', false)" class="w-14 h-14 flex items-center justify-center bg-slate-50 rounded-2xl text-slate-400 hover:text-slate-900 transition-all hover:rotate-90 duration-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form wire:submit.prevent="addLead" class="flex-1 overflow-y-auto p-12 space-y-12">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4 px-2">{{ __('الاسم الكامل للعميل') }}</label>
                                    <input type="text" wire:model="client_name" placeholder="أدخل اسم العميل كما هو..."
                                        class="w-full px-8 py-5 bg-slate-50 border-none rounded-[1.5rem] text-sm font-black text-slate-900 focus:ring-4 focus:ring-primary-500/10 focus:bg-white outline-none transition-all placeholder:text-slate-300">
                                    @error('client_name') <span class="text-rose-500 text-[10px] font-black mt-3 block px-4">{{ $message }}</span> @enderror
                                </div>

                                <div class="space-y-4">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4 px-2">{{ __('اسم الشركة / المؤسسة') }}</label>
                                    <input type="text" wire:model="company_name" placeholder="اختياري..."
                                        class="w-full px-8 py-5 bg-slate-50 border-none rounded-[1.5rem] text-sm font-black text-slate-900 focus:ring-4 focus:ring-primary-500/10 focus:bg-white outline-none transition-all placeholder:text-slate-300">
                                </div>

                                <div class="space-y-4">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4 px-2">{{ __('رقم جوال العميل') }}</label>
                                    <input type="number" wire:model="client_phone" placeholder="05xxxxxxxx" dir="ltr"
                                        class="w-full px-8 py-5 bg-slate-50 border-none rounded-[1.5rem] text-sm font-black text-slate-900 focus:ring-4 focus:ring-primary-500/10 focus:bg-white outline-none transition-all font-mono placeholder:text-slate-300 tracking-wider">
                                    @error('client_phone') <span class="text-rose-500 text-[10px] font-black mt-3 block px-4">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4 px-2">{{ __('المدينة أو المنطقة') }}</label>
                                    <div class="relative">
                                        <select wire:model="city" class="w-full px-8 py-5 bg-slate-50 border-none rounded-[1.5rem] text-sm font-black text-slate-900 focus:ring-4 focus:ring-primary-500/10 focus:bg-white outline-none transition-all appearance-none cursor-pointer">
                                            <option value="">{{ __('اختر المدينة...') }}</option>
                                            <option value="الرياض">الرياض</option>
                                            <option value="جدة">جدة</option>
                                            <option value="الدمام">الدمام</option>
                                            <option value="مكة">مكة المكرمة</option>
                                            <option value="المدينة">المدينة المنورة</option>
                                            <option value="أخرى">أخرى</option>
                                        </select>
                                        <div class="absolute inset-y-0 left-8 flex items-center pointer-events-none text-slate-300">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                    @error('city') <span class="text-rose-500 text-[10px] font-black mt-3 block px-4">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4 px-2">{{ __('وصف الاحتياجات والمتطلبات') }}</label>
                                    <textarea wire:model="needs" rows="3" placeholder="ما الذي يبحث عنه العميل بالتحديد؟"
                                        class="w-full px-8 py-6 bg-slate-50 border-none rounded-[1.75rem] text-sm font-black text-slate-900 focus:ring-4 focus:ring-primary-500/10 focus:bg-white outline-none transition-all placeholder:text-slate-300 resize-none leading-relaxed"></textarea>
                                </div>
                            </div>

                            <div class="space-y-8">
                                <div class="flex items-center gap-4 px-2">
                                    <div class="w-1.5 h-6 bg-primary-500 rounded-full"></div>
                                    <label class="block text-[10px] font-black text-slate-900 uppercase tracking-[0.3em]">{{ __('الأنظمة والخدمات المقترحة') }}</label>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                    @foreach($available_systems as $system)
                                    <button type="button"
                                        wire:click="toggleSystem('{{ $system['id'] }}')"
                                        class="group relative flex flex-col items-center justify-center p-8 border border-slate-100 rounded-[2.5rem] transition-all duration-500 {{ in_array($system['id'], $recommended_systems) ? 'bg-primary-50 border-primary-200 shadow-xl shadow-primary-500/10' : 'bg-white hover:border-primary-100 hover:bg-slate-50/50 hover:-translate-y-1' }}">
                                        <div class="relative h-14 mb-5 flex items-center justify-center">
                                            <img src="{{ $system['logo'] }}" class="h-10 transition-all duration-700 {{ in_array($system['id'], $recommended_systems) ? 'scale-125' : 'grayscale opacity-20 group-hover:grayscale-0 group-hover:opacity-100' }}" alt="{{ $system['name'] }}">
                                        </div>
                                        <span class="text-[10px] font-black tracking-[0.2em] uppercase transition-colors duration-500 {{ in_array($system['id'], $recommended_systems) ? 'text-primary-700' : 'text-slate-400 group-hover:text-slate-600' }}">{{ $system['name'] }}</span>

                                        @if(in_array($system['id'], $recommended_systems))
                                        <div class="absolute top-4 right-4 w-6 h-6 bg-primary-600 text-white rounded-full flex items-center justify-center shadow-lg shadow-primary-500/40 animate-bounce-subtle">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        @endif
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </form>

                        <!-- Modal Footer -->
                        <div class="p-10 px-12 bg-slate-50/50 backdrop-blur-md border-t border-slate-100 flex flex-col sm:flex-row gap-4 items-center justify-between">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest max-w-xs text-center sm:text-right">
                                {{ __('يرجى ملاحظة أن إضافة العميل تعني الموافقة على شروط سياسة الخصوصية وحماية البيانات.') }}
                            </p>
                            <div class="flex items-center gap-4 w-full sm:w-auto">
                                <button type="button" @click="$wire.set('showModal', false)" class="flex-1 sm:flex-none px-10 py-5 rounded-2xl text-xs font-black text-slate-400 hover:text-slate-900 transition-colors uppercase tracking-widest">
                                    {{ __('إلغاء') }}
                                </button>
                                <button type="button" wire:click="addLead" class="flex-1 sm:flex-none btn-luxury !px-12 !py-5 !rounded-2xl !text-xs shadow-2xl shadow-primary-600/20">
                                    {{ __('حفظ وإرسال الملف') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>