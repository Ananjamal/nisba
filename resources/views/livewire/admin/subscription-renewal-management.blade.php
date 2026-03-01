<div>
    @if (session()->has('message'))
    <div class="p-4 text-sm text-green-700 bg-green-100 rounded-2xl font-bold border border-green-200" role="alert">
        {{ session('message') }}
    </div>
    @endif
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <x-table.filter-bar :statusOptions="[
            'all' => 'جميع الحالات',
            'pending' => 'في الانتظار',
            'completed' => 'مكتمل',
            'failed' => 'فشل',
            'cancelled' => 'ملغي'
        ]" search="search" statusFilter="statusFilter">

            <x-slot name="actions">
                <div class="flex gap-2">
                    <x-table.column-toggler :columns="$columns" :labels="[
                    'client' => 'العميل',
                    'renewal_date' => 'تاريخ التجديد',
                    'amount' => 'المبلغ',
                    'status' => 'الحالة',
                    'marketer' => 'المسوق',
                    'actions' => 'العمليات'
                ]" />

                    <button wire:click="openCreateRenewalModal" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        إنشاء تجديد جديد
                    </button>
                </div>
            </x-slot>
        </x-table.filter-bar>

        <!-- فلاتر إضافية -->
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <!-- فلتر نوع التجديد -->
            <div class="relative min-w-[180px] group">
                <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none z-10">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <select wire:model.live="renewalTypeFilter" class="w-full appearance-none pl-9 pr-10 py-2.5 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-sm transition-all text-sm font-bold text-gray-700 hover:border-gray-300">
                    <option value="">جميع الأنواع</option>
                    <option value="manual">يدوي</option>
                    <option value="automatic">تلقائي</option>
                </select>
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- فلتر نطاق التاريخ -->
            <div class="relative min-w-[180px] group">
                <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none z-10">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <select wire:model.live="dateRangeFilter" class="w-full appearance-none pl-9 pr-10 py-2.5 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-sm transition-all text-sm font-bold text-gray-700 hover:border-gray-300">
                    <option value="">جميع الفترات</option>
                    <option value="upcoming">قريباً (30 يوم)</option>
                    <option value="expired">منتهي</option>
                    <option value="this_month">هذا الشهر</option>
                    <option value="next_month">الشهر القادم</option>
                </select>
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- زر إعادة تعيين الفلاتر -->
            @if($statusFilter !== 'all' || $renewalTypeFilter !== 'all' || $dateRangeFilter !== 'all' || $search)
            <button wire:click="$set('statusFilter', 'all'); $set('renewalTypeFilter', 'all'); $set('dateRangeFilter', 'all'); $set('search', '')"
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
                        @if($columns['renewal_date'])
                        <x-table.th field="renewal_date" :sortField="$sortField" :sortDirection="$sortDirection" label="تاريخ التجديد" />
                        @endif
                        @if($columns['amount'])
                        <x-table.th field="renewal_amount" :sortField="$sortField" :sortDirection="$sortDirection" label="المبلغ" />
                        @endif
                        @if($columns['status'])
                        <x-table.th field="status" :sortField="$sortField" :sortDirection="$sortDirection" label="الحالة" />
                        @endif
                        @if($columns['marketer'])
                        <th class="pb-4 font-bold">المسوق</th>
                        @endif
                        @if($columns['actions'])
                        <th class="pb-4 font-bold text-left">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($renewals as $renewal)
                    <tr class="group hover:bg-gray-50 transition-colors duration-200">
                        @if($columns['client'])
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center text-primary-600 font-bold">
                                    {{ mb_substr($renewal->lead->client_name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.leads.show', $renewal->lead->id) }}" class="font-bold text-gray-900 block hover:text-primary-600 transition text-right">
                                        {{ $renewal->lead->client_name }}
                                    </a>
                                    <span class="text-xs text-gray-500">{{ $renewal->lead->company_name ?? '---' }}</span>
                                </div>
                            </div>
                        </td>
                        @endif
                        @if($columns['renewal_date'])
                        <td class="py-4">
                            <div>
                                <div class="text-sm font-bold text-primary-900">{{ $renewal->renewal_date->format('Y-m-d') }}</div>
                                <div class="text-xs {{ $this->getDaysBadgeClass($renewal->getDaysUntilRenewal()) }} px-2 py-1 rounded-full inline-block">
                                    {{ $this->getDaysLabel($renewal->getDaysUntilRenewal()) }}
                                </div>
                            </div>
                        </td>
                        @endif
                        @if($columns['amount'])
                        <td class="py-4 text-sm text-gray-900">
                            <span class="font-bold text-green-600">{{ number_format($renewal->renewal_amount, 2) }} ريال</span>
                        </td>
                        @endif
                        @if($columns['status'])
                        <td class="py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $renewal->getStatusBadgeClass() }}">
                                {{ $renewal->getStatusLabel() }}
                            </span>
                        </td>
                        @endif
                        @if($columns['marketer'])
                        <td class="py-4 text-sm text-gray-900">
                            {{ $renewal->lead->users->first()->name ?? '---' }}
                        </td>
                        @endif
                        @if($columns['actions'])
                        <td class="py-4">
                            <div class="flex gap-2">
                                @if($renewal->status === 'pending')
                                    <button wire:click="openRenewalModal({{ $renewal->id }})" 
                                            class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-all duration-300" title="تجديد">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                    <button wire:click="sendRenewalReminder({{ $renewal->id }})" 
                                            class="p-2 text-yellow-600 bg-yellow-50 hover:bg-yellow-100 rounded-xl transition-all duration-300" title="تذكير">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </button>
                                @endif
                                <button wire:click="cancelRenewal({{ $renewal->id }})" 
                                        class="p-2 text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-all duration-300" title="إلغاء">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="font-bold">لا توجد تجديدات حالياً</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $renewals->links() }}
        </div>
    </div>

    <!-- Professional Create Renewal Modal -->
    <template x-teleport="body">
        <div x-data="{ showCreateModal: $wire.entangle('showCreateModal') }"
            x-show="showCreateModal"
            x-on:keydown.escape.window="showCreateModal = false"
            class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-primary-900/60 backdrop-blur-sm transition-opacity" @click="showCreateModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl max-h-[85vh] flex flex-col transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    <!-- Modal Header -->
                    <div class="px-8 py-6 border-b border-primary-50 flex items-center justify-between bg-gradient-to-r from-primary-50 to-white">
                        <div>
                            <h3 class="text-2xl font-black text-primary-900">إنشاء تجديد جديد</h3>
                            <p class="text-primary-500 text-sm font-medium">إضافة تجديد اشتراك للعميل</p>
                        </div>
                        <button @click="showCreateModal = false" class="p-2 rounded-full hover:bg-white hover:shadow-md transition-all text-primary-400 hover:text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="createRenewal" class="flex flex-col flex-1 min-h-0">
                        <div class="p-8 flex-1 overflow-y-auto">
                            <div class="max-w-xl mx-auto">
                                <div class="grid grid-cols-1 gap-8">
                                    <div class="space-y-6">
                                    <div class="bg-primary-50/50 p-6 rounded-3xl border border-primary-100">
                                        <h4 class="text-xs font-black text-primary-400 uppercase tracking-widest mb-4">معلومات العميل</h4>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-base font-bold text-gray-800 mb-2">العميل</label>
                                                <select wire:model.live="createLeadId"
                                                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 text-base font-semibold text-gray-800">
                                                    <option value="">اختر العميل</option>
                                                    @forelse($leadsForRenewal as $lead)
                                                        <option value="{{ $lead->id }}">
                                                            {{ $lead->client_name }}{{ $lead->company_name ? ' - '.$lead->company_name : '' }}
                                                        </option>
                                                    @empty
                                                        <option value="" disabled>لا يوجد عملاء</option>
                                                    @endforelse
                                                </select>
                                                @error('createLeadId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-base font-bold text-gray-800 mb-2">تاريخ التجديد</label>
                                                <input type="date" wire:model="createRenewalDate"
                                                       class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 text-base font-semibold text-gray-800">
                                                @error('createRenewalDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-base font-bold text-gray-800 mb-2">المبلغ</label>
                                                <input type="number" wire:model="createRenewalAmount" step="0.01" min="0"
                                                       class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 text-base font-semibold text-gray-800"
                                                       placeholder="0.00">
                                                @error('createRenewalAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50/50 p-6 rounded-3xl border border-gray-100">
                                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">معلومات إضافية</h4>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-base font-bold text-gray-800 mb-2">ملاحظات</label>
                                                <textarea wire:model="createRenewalNotes" rows="4"
                                                          class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 text-base font-semibold text-gray-800"
                                                          placeholder="أي ملاحظات إضافية..."></textarea>
                                                @error('createRenewalNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-8 py-6 bg-gray-50/95 backdrop-blur border-t border-gray-100 rounded-b-[2.5rem] flex justify-end gap-3 sticky bottom-0">
                            <button type="button" @click="showCreateModal = false" class="px-8 py-3 bg-white border border-gray-200 text-gray-600 rounded-2xl font-bold hover:bg-gray-50 hover:shadow-sm transition-all text-base">
                                إلغاء
                            </button>
                            <button type="submit" class="btn btn-primary">
                                إنشاء التجديد
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Professional Renewal Modal -->
    <template x-teleport="body">
        <div x-data="{ showRenewalModal: $wire.entangle('showRenewalModal') }"
            x-show="showRenewalModal"
            x-on:keydown.escape.window="showRenewalModal = false"
            class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-primary-900/60 backdrop-blur-sm transition-opacity" @click="showRenewalModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl max-h-[85vh] flex flex-col transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    <!-- Modal Header -->
                    <div class="px-8 py-6 border-b border-primary-50 flex items-center justify-between bg-gradient-to-r from-primary-50 to-white">
                        <div>
                            <h3 class="text-2xl font-black text-primary-900">تجديد الاشتراك</h3>
                            <p class="text-primary-500 text-sm font-medium">معالجة تجديد اشتراك العميل</p>
                        </div>
                        <button @click="showRenewalModal = false" class="p-2 rounded-full hover:bg-white hover:shadow-md transition-all text-primary-400 hover:text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @if($selectedRenewal)
                    <form wire:submit.prevent="processRenewal" class="flex flex-col flex-1 min-h-0">
                        <div class="p-8 flex-1 overflow-y-auto">
                            <div class="bg-primary-50/50 p-6 rounded-3xl border border-primary-100 mb-6">
                                <h4 class="text-xs font-black text-primary-400 uppercase tracking-widest mb-4">معلومات العميل</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-primary-400 font-bold">العميل</p>
                                            <p class="font-black text-primary-900 text-lg">{{ $selectedRenewal->lead->client_name }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-primary-400 font-bold">تاريخ الانتهاء الحالي</p>
                                            <p class="font-black text-primary-900 text-lg">{{ $selectedRenewal->lead->subscription_renewal_date?->format('Y-m-d') ?? '---' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-6">
                                    <div class="bg-gray-50/50 p-6 rounded-3xl border border-gray-100">
                                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">تفاصيل التجديد</h4>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-base font-bold text-gray-800 mb-2">مبلغ التجديد</label>
                                                <input type="number" wire:model="renewalAmount" step="0.01" min="0"
                                                       class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 text-base font-semibold text-gray-800">
                                                @error('renewalAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-base font-bold text-gray-800 mb-2">تاريخ الانتهاء الجديد</label>
                                                <input type="date" wire:model="newExpiryDate"
                                                       class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 text-base font-semibold text-gray-800">
                                                @error('newExpiryDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div class="bg-amber-50/30 p-6 rounded-3xl border border-amber-100">
                                        <h4 class="text-xs font-black text-amber-500 uppercase tracking-widest mb-4">معلومات إضافية</h4>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-base font-bold text-gray-800 mb-2">ملاحظات</label>
                                                <textarea wire:model="renewalNotes" rows="4"
                                                          class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 text-base font-semibold text-gray-800"
                                                          placeholder="أي ملاحظات إضافية..."></textarea>
                                                @error('renewalNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-8 py-6 bg-gray-50/95 backdrop-blur border-t border-gray-100 rounded-b-[2.5rem] flex justify-end gap-3 sticky bottom-0">
                            <button type="button" @click="showRenewalModal = false" class="px-8 py-3 bg-white border border-gray-200 text-gray-600 rounded-2xl font-bold hover:bg-gray-50 hover:shadow-sm transition-all text-base">
                                إلغاء
                            </button>
                            <button type="submit" class="btn btn-primary">
                                تأكيد التجديد
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </template>

    <!-- Professional Bulk Renewal Modal -->
    <template x-teleport="body">
        <div x-data="{ showBulkRenewalModal: $wire.entangle('showBulkRenewalModal') }"
            x-show="showBulkRenewalModal"
            x-on:keydown.escape.window="showBulkRenewalModal = false"
            class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-primary-900/60 backdrop-blur-sm transition-opacity" @click="showBulkRenewalModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg max-h-[85vh] flex flex-col transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    <!-- Modal Header -->
                    <div class="px-8 py-6 border-b border-primary-50 flex items-center justify-between bg-gradient-to-r from-primary-50 to-white">
                        <div>
                            <h3 class="text-2xl font-black text-primary-900">تجديد جماعي</h3>
                            <p class="text-primary-500 text-sm font-medium">تجديد التجديدات المحددة دفعة واحدة</p>
                        </div>
                        <button @click="showBulkRenewalModal = false" class="p-2 rounded-full hover:bg-white hover:shadow-md transition-all text-primary-400 hover:text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="bulkRenewal" class="flex flex-col flex-1 min-h-0">
                        <div class="p-8 flex-1 overflow-y-auto">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-base font-bold text-gray-800 mb-2">عدد الأيام للإضافة</label>
                                    <input type="number" wire:model="bulkRenewalDays" min="1" max="365"
                                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 text-base font-semibold text-gray-800">
                                    @error('bulkRenewalDays') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-base font-bold text-gray-800 mb-2">المبلغ الموحد</label>
                                    <input type="number" wire:model="bulkRenewalAmount" step="0.01" min="0"
                                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 text-base font-semibold text-gray-800">
                                    @error('bulkRenewalAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-8 py-6 bg-gray-50/95 backdrop-blur border-t border-gray-100 rounded-b-[2.5rem] flex justify-end gap-3 sticky bottom-0">
                            <button type="button" @click="showBulkRenewalModal = false" class="px-8 py-3 bg-white border border-gray-200 text-gray-600 rounded-2xl font-bold hover:bg-gray-50 hover:shadow-sm transition-all text-base">
                                إلغاء
                            </button>
                            <button type="submit" class="px-8 py-3 bg-green-600 text-white rounded-2xl font-bold hover:bg-green-700 shadow-lg shadow-green-200 transition-all text-base">
                                تجديد الكل
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
