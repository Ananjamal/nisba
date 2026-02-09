<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Lead;

new #[Layout('layouts.admin')] class extends Component {
    public User $user;

    public function mount(User $user)
    {
        $this->user = $user->load(['stats', 'leads']);
    }
} ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-2">
        <a href="{{ route('admin.affiliates') }}" class="w-10 h-10 rounded-xl bg-white border border-primary-50 flex items-center justify-center text-primary-600 hover:bg-primary-50 transition shadow-sm" title="العودة">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-black text-primary-900">{{ $user->name }}</h2>
            <p class="text-primary-400 font-bold uppercase tracking-wider text-xs">{{ $user->role === 'admin' ? 'مدير النظام' : 'شريك تسويقي' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 rounded-3xl border border-primary-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 flex items-center justify-center text-primary-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">إجمالي العملاء</p>
                        <h4 class="text-2xl font-black text-primary-900">{{ $user->leads->count() }}</h4>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-primary-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 flex items-center justify-center text-primary-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">مبيعات محققة</p>
                        <h4 class="text-2xl font-black text-primary-900">{{ $user->leads()->where('status', 'sold')->count() }}</h4>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-primary-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">رصيد العمولات</p>
                        <h4 class="text-2xl font-black text-primary-900">{{ number_format($user->stats->pending_commissions ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>

            <!-- Associated Leads Table -->
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-primary-100">
                <h3 class="text-lg font-black text-primary-900 mb-6 flex items-center gap-2 text-right">قائمة العملاء المرتبطين</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="text-primary-400 text-[10px] font-black uppercase tracking-widest border-b border-primary-50">
                                <th class="pb-3 px-4">العميل</th>
                                <th class="pb-3 px-4 text-center">الحالة</th>
                                <th class="pb-3 px-4">تاريخ الإضافة</th>
                                <th class="pb-3 px-4 text-left">التفاصيل</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary-50">
                            @foreach($user->leads as $lead)
                            <tr class="hover:bg-primary-50/20 transition group">
                                <td class="py-4 px-4">
                                    <p class="font-bold text-primary-900 text-sm">{{ $lead->client_name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $lead->company_name }}</p>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black
                                        @if($lead->status === 'sold') bg-green-50 text-green-700
                                        @elseif($lead->status === 'cancelled') bg-red-50 text-red-700
                                        @else bg-primary-50 text-primary-700 @endif border border-current opacity-70">
                                        {{ [
                                            'under_review' => 'مراجعة',
                                            'contacting' => 'تواصل',
                                            'sold' => 'مبيعة',
                                            'cancelled' => 'ملغي'
                                        ][$lead->status] ?? $lead->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-xs font-bold text-gray-500">{{ $lead->created_at->format('Y-m-d') }}</td>
                                <td class="py-4 px-4 text-left">
                                    <a href="{{ route('admin.leads.show', $lead->id) }}" class="p-2 bg-primary-50 text-primary-600 rounded-lg hover:bg-primary-600 hover:text-white transition inline-block">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6 text-right">
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-primary-100">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 rounded-3xl bg-primary-900 text-white flex items-center justify-center text-3xl font-black mx-auto mb-4 shadow-xl">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                    <h3 class="text-xl font-black text-primary-900">{{ $user->name }}</h3>
                    <p class="text-primary-400 font-bold text-sm">{{ $user->email }}</p>
                </div>

                <div class="space-y-6 pt-6 border-t border-primary-50">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">الهاتف</p>
                        <p class="font-bold text-primary-900">{{ $user->phone ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">القطاع / التخصص</p>
                        <p class="font-bold text-primary-900">{{ $user->sector ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">تاريخ الانضمام</p>
                        <p class="font-bold text-primary-900">{{ $user->created_at->format('Y-m-d') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-primary-900 text-white p-8 rounded-[2rem] shadow-xl">
                <p class="text-[10px] font-black text-primary-300 uppercase tracking-widest mb-4">المرتبة الحالية</p>
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center">
                        <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-black uppercase">{{ $user->rank ?: 'BRONZE' }}</h4>
                        <p class="text-xs text-primary-300">تقدير الأداء لهذا الشهر</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>