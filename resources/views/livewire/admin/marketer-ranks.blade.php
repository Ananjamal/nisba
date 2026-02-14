<div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">إدارة رتب المسوقين</h2>
            <p class="text-gray-500 mt-1">تحكم في رتب المسوقين ومضاعفات العمولة الخاصة بهم</p>
        </div>
        <div class="flex items-center gap-4">
            @foreach($allRanks as $rank)
            <div class="{{ str_replace('bg-', 'bg-opacity-50 bg-', $rank->color) }} p-3 rounded-2xl border flex items-center gap-3">
                <span class="text-2xl">{{ $rank->icon }}</span>
                <span class="text-sm font-bold">{{ $rank->getLabelAttribute() ?? $rank->name }}: {{ number_format($rank->commission_multiplier, 1) }}x</span>
            </div>
            @endforeach
        </div>
    </div>

    @if (session()->has('message'))
    <div class="p-4 text-sm text-green-700 bg-green-100 rounded-2xl font-bold border border-green-200" role="alert">
        {{ session('message') }}
    </div>
    @endif

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="flex-1 relative group">
                <input type="text" wire:model.live="search" placeholder="بحث بالاسم أو البريد..." class="w-full pl-4 pr-10 py-3 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-bold text-gray-900 placeholder:text-gray-400 hover:border-gray-300 shadow-sm">
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400 group-focus-within:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <div class="relative min-w-[200px] group">
                <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none">
                    <span class="text-xl">🎖️</span>
                </div>
                <select wire:model.live="rank_filter" class="w-full appearance-none pl-4 pr-10 py-3 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-sm transition-all font-bold text-gray-700 hover:border-gray-300">
                    <option value="">جميع الرتب</option>
                    @foreach($allRanks as $rank)
                    <option value="{{ $rank->name }}">{{ $rank->label }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button wire:click="exportExcel"
                    class="group flex items-center justify-center p-3 bg-white border border-gray-100 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all duration-300 shadow-sm"
                    title="تصدير Excel">
                    <svg class="w-5 h-5 text-green-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </button>
                <button wire:click="exportPdf"
                    class="group flex items-center justify-center p-3 bg-white border border-gray-100 rounded-xl hover:border-red-500 hover:bg-red-50 transition-all duration-300 shadow-sm"
                    title="تصدير PDF">
                    <svg class="w-5 h-5 text-red-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z M12 11h4m-4 4h4m-4-8h4" />
                    </svg>
                </button>
            </div>



            @if($search || $rank_filter || $sector_filter)
            <button wire:click="$set('search', ''); $set('rank_filter', ''); $set('sector_filter', '')"
                class="px-4 py-3 bg-gray-50 text-gray-500 rounded-xl font-bold hover:bg-gray-100 transition-all flex items-center justify-center gap-2 border border-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>إعادة ضبط</span>
            </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="text-gray-500 text-sm border-b border-gray-100">
                        <x-table.th field="name" :sortField="$sortField" :sortDirection="$sortDirection" label="المسوق" />
                        <x-table.th field="phone" :sortField="$sortField" :sortDirection="$sortDirection" label="رقم الهاتف" />
                        <x-table.th field="rank" :sortField="$sortField" :sortDirection="$sortDirection" label="الرتبة" />
                        <x-table.th field="commission_multiplier" :sortField="$sortField" :sortDirection="$sortDirection" label="مضاعف العمولة" />
                        <th class="pb-4 font-semibold text-start">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @foreach($marketers as $marketer)
                    <tr class="group hover:bg-gray-50 transition-all duration-300 border-b border-gray-50 last:border-0">
                        <td class="py-5">
                            <button wire:click="openViewModal({{ $marketer->id }})" class="font-bold text-gray-900 hover:text-primary-600 transition-colors">{{ $marketer->name }}</button>
                            <div class="text-xs text-gray-400 font-bold">{{ $marketer->email }}</div>
                        </td>
                        <td class="py-5 font-bold text-gray-600">
                            {{ $marketer->phone ?: '-' }}
                        </td>
                        <td class="py-5">
                            <div x-data="{ open: false }" class="relative inline-block text-right">
                                <button @click="open = !open"
                                    class="inline-flex items-center gap-2.5 px-4 py-2 rounded-xl text-xs font-black border shadow-sm transition-all hover:shadow-md hover:bg-gray-50 active:scale-95 {{ $marketer->getRankBadgeColor() }}">
                                    <span>{{ $marketer->getRankIcon() }} {{ $marketer->getRankLabel() }}</span>
                                    <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="open"
                                    @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                    class="absolute right-0 z-[60] mt-3 w-56 origin-top-right rounded-2xl bg-white shadow-2xl border border-gray-100 py-2 focus:outline-none"
                                    style="display: none;">

                                    <div class="px-4 py-2 border-b border-gray-50 mb-1">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">اختر الرتبة الجديدة</p>
                                    </div>

                                    @foreach($allRanks as $rank)
                                    <button wire:click="updateRank({{ $marketer->id }}, '{{ $rank->name }}'); open = false"
                                        class="flex items-center gap-3 w-full px-4 py-3 text-sm font-bold text-right transition-all hover:bg-primary-50 group {{ $marketer->rank === $rank->name ? 'bg-primary-50/50 text-primary-600' : 'text-gray-600 hover:text-primary-600' }}">
                                        <span class="text-xl group-hover:scale-110 transition-transform">{{ $rank->icon }}</span>
                                        <span class="flex-1">{{ $marketer->getRankLabel($rank->name) }}</span>
                                        @if($marketer->rank === $rank->name)
                                        <div class="w-2 h-2 rounded-full bg-primary-600 shadow-sm shadow-primary-200"></div>
                                        @endif
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                        <td class="py-5">
                            <div class="flex items-center gap-2">
                                <input type="number" step="0.01"
                                    class="w-24 px-3 py-1.5 text-sm font-bold text-center border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    value="{{ $marketer->commission_multiplier }}"
                                    placeholder="المعامل"
                                    onchange="@this.updateMultiplier({{ $marketer->id }}, this.value)">
                                <span class="text-xs font-bold text-gray-400">x</span>
                            </div>
                        </td>
                        <td class="py-5 text-left">
                            <button wire:click="openViewModal({{ $marketer->id }})" class="p-2 text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition-all duration-300" title="عرض التفاصيل">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $marketers->links() }}
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
                            <h3 class="text-2xl font-black text-primary-900">تفاصيل المسوق</h3>
                            <p class="text-primary-500 text-sm font-medium">عرض المعلومات الكاملة للمسوق والأداء</p>
                        </div>
                        <button @click="showViewModal = false" class="p-2 rounded-full hover:bg-white hover:shadow-md transition-all text-primary-400 hover:text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @if($viewUser)
                    <div class="p-8 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- User Basic Info -->
                            <div class="space-y-6">
                                <div class="bg-primary-50/50 p-6 rounded-3xl border border-primary-100">
                                    <h4 class="text-xs font-black text-primary-400 uppercase tracking-widest mb-4">المعلومات الشخصية</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600 font-bold">
                                                {{ substr($viewUser->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-primary-400 font-bold">الاسم الكامل</p>
                                                <p class="font-black text-primary-900 text-lg line-height-1">{{ $viewUser->name }}</p>
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
                                                <p class="font-black text-primary-900 text-lg leading-none">{{ $viewUser->phone ?: 'غير متوفر' }}</p>
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
                                                <p class="font-black text-primary-900 text-xs truncate">{{ $viewUser->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50/50 p-6 rounded-3xl border border-gray-100">
                                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">تفاصيل إضافية</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-[10px] text-gray-400 font-bold">القطاع</p>
                                            <p class="font-black text-primary-900">{{ $viewUser->sector ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 font-bold">تاريخ الانضمام</p>
                                            <p class="font-black text-primary-900">{{ $viewUser->created_at->format('Y-m-d') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Performance & Financial -->
                            <div class="space-y-6">
                                <div class="bg-primary-50/50 p-6 rounded-3xl border border-primary-100">
                                    <h4 class="text-xs font-black text-primary-400 uppercase tracking-widest mb-4">مستوى الأداء</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between p-3 bg-white rounded-2xl border border-primary-50 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-2xl shadow-sm border border-gray-100">
                                                    {{ $viewUser->getRankIcon() }}
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-primary-400 font-bold">المستوى الحالي</p>
                                                    <span class="px-2 py-0.5 rounded text-xs font-black {{ $viewUser->getRankBadgeColor() }}">
                                                        {{ $viewUser->getRankLabel() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="p-3 bg-white rounded-2xl border border-primary-50 shadow-sm text-center">
                                                <p class="text-[10px] text-primary-400 font-bold mb-1">عدد العملاء</p>
                                                <p class="text-xl font-black text-primary-900">{{ $viewUser->leads_count }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-amber-50/30 p-6 rounded-3xl border border-amber-100">
                                    <h4 class="text-xs font-black text-amber-500 uppercase tracking-widest mb-4">المعلومات المالية</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <p class="text-[10px] text-amber-500 font-bold">اسم البنك</p>
                                            <p class="font-black text-primary-900">{{ $viewUser->bank_name ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-amber-500 font-bold">اسم صاحب الحساب</p>
                                            <p class="font-black text-primary-900">{{ $viewUser->account_holder_name ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-amber-500 font-bold">رقم الآيبان</p>
                                            <p class="font-mono text-sm font-bold text-primary-900 dir-ltr text-left">{{ $viewUser->iban ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 rounded-b-[2.5rem] flex justify-end gap-3">
                        <button @click="showViewModal = false" class="px-8 py-3 bg-white border border-gray-200 text-gray-600 rounded-2xl font-bold hover:bg-gray-50 hover:shadow-sm transition-all text-sm">
                            إغلاق
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>