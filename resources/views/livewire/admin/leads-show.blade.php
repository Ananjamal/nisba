<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Lead;

new #[Layout('layouts.admin')] class extends Component {
    public Lead $lead;

    public function mount(Lead $lead)
    {
        $this->lead = $lead->load('users');
    }
} ?>

<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center gap-4 mb-2">
        <a href="{{ route('admin.leads') }}" class="w-10 h-10 rounded-xl bg-white border border-primary-50 flex items-center justify-center text-primary-600 hover:bg-primary-50 transition shadow-sm" title="العودة">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-black text-primary-900">{{ $lead->client_name }}</h2>
            <p class="text-primary-400 font-bold">{{ $lead->company_name }}</p>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Basic Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-primary-100">
                <h3 class="text-lg font-black text-primary-900 mb-6 flex items-center gap-2 text-right">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    بيانات العميل الأساسية
                </h3>
                <div class="grid grid-cols-2 gap-8 text-right">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">الهاتف</p>
                        <p class="font-bold text-primary-900">{{ $lead->client_phone }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">البريد الإلكتروني</p>
                        <p class="font-bold text-primary-900">{{ $lead->email ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">المنطقة</p>
                        <p class="font-bold text-primary-900">{{ $lead->region ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">المدينة</p>
                        <p class="font-bold text-primary-900">{{ $lead->city ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">القطاع</p>
                        <p class="font-bold text-primary-900">{{ $lead->sector ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">تاريخ الإضافة</p>
                        <p class="font-bold text-primary-900">{{ $lead->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Recommended Systems -->
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-primary-100">
                <h3 class="text-lg font-black text-primary-900 mb-6 flex items-center gap-2 text-right">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    الأنظمة المقترحة
                </h3>
                <div class="flex flex-wrap gap-2 justify-end">
                    @forelse($lead->recommended_systems ?? [] as $sysId)
                    <span class="px-4 py-2 bg-primary-50 text-primary-700 rounded-xl text-sm font-bold border border-primary-100 uppercase">
                        {{ $sysId }}
                    </span>
                    @empty
                    <p class="text-gray-400 text-sm">لا توجد أنظمة مقترحة.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Status & Actions -->
        <div class="space-y-6">
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-primary-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 text-center">حالة العميل الحالية</p>
                <div class="text-center group">
                    <span class="inline-block px-6 py-2 rounded-full text-sm font-black mb-4
                        @if($lead->status === 'sold') bg-green-100 text-green-700
                        @elseif($lead->status === 'cancelled') bg-red-100 text-red-700
                        @elseif($lead->status === 'contacting') bg-primary-100 text-primary-700
                        @else bg-primary-50 text-primary-700 @endif">
                        {{ [
                            'under_review' => 'تحت المراجعة',
                            'contacting' => 'جاري التواصل',
                            'sold' => 'تم البيع',
                            'cancelled' => 'ملغي'
                        ][$lead->status] ?? $lead->status }}
                    </span>
                </div>

                <div class="pt-6 border-t border-primary-50">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 text-right">المسوقين المرتبطين</p>
                    <div class="space-y-3">
                        @foreach($lead->users as $u)
                        <a href="{{ route('admin.affiliates.show', $u->id) }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-primary-50 transition border border-transparent hover:border-primary-100 text-right">
                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 text-xs font-bold">
                                {{ mb_substr($u->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-primary-900">{{ $u->name }}</p>
                                <p class="text-[10px] text-primary-400">{{ $u->phone }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Commission Card -->
            <div class="bg-primary-900 p-8 rounded-[2rem] shadow-xl text-white">
                <p class="text-[10px] font-black text-primary-300 uppercase tracking-widest mb-2 text-right">تفاصيل العمولة</p>
                <div class="flex justify-between items-end">
                    <div>
                        <h4 class="text-3xl font-black">{{ $lead->commission_rate }}</h4>
                        <p class="text-xs text-primary-200">{{ $lead->commission_type === 'fixed' ? 'ريال سعودي (ثابت)' : 'نسبة مئوية (%)' }}</p>
                    </div>
                    <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>