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
            <!-- Lifecycle Timeline -->
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-primary-100">
                <h3 class="text-lg font-black text-primary-900 mb-6 flex items-center gap-2 text-right">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    دورة حياة العميل
                </h3>
                <div class="space-y-4">
                    @php
                        $statuses = \App\Models\Lead::lifecycleStatuses();
                        $currentOrder = $statuses[$lead->status]['order'] ?? 1;
                    @endphp
                    @foreach($statuses as $key => $info)
                        @php
                            $isCurrent = $key === $lead->status;
                            $isPast = $info['order'] < $currentOrder;
                            $isFuture = $info['order'] > $currentOrder;
                        @endphp
                        <div class="flex items-center gap-3 {{ $isCurrent ? 'bg-primary-50 -mx-4 px-4 py-2 rounded-xl border border-primary-200' : '' }}">
                            <div class="flex-shrink-0">
                                @if($isCurrent)
                                    <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white text-lg">
                                        {{ $info['icon'] }}
                                    </div>
                                @elseif($isPast)
                                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-700 text-lg">
                                        {{ $info['icon'] }}
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-lg">
                                        {{ $info['icon'] }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 text-right">
                                <p class="font-bold text-sm {{ $isCurrent ? 'text-primary-900' : ($isPast ? 'text-green-700' : 'text-gray-400') }}">
                                    {{ $info['label'] }}
                                </p>
                                @if($isCurrent)
                                    <p class="text-xs text-primary-600">الحالة الحالية</p>
                                @elseif($isPast)
                                    <p class="text-xs text-green-600">تم إنجازها</p>
                                @else
                                    <p class="text-xs text-gray-400">قادمة</p>
                                @endif
                            </div>
                            @if($isPast)
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-primary-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 text-center">حالة العميل الحالية</p>
                <div class="text-center group">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ \App\Models\Lead::statusBadgeClass($lead->status) }}">
                        {{ \App\Models\Lead::statusLabel($lead->status) }}
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