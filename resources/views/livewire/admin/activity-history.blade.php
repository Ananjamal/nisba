<div class="space-y-8">
    @if(session()->has('message'))
    <div class="p-4 text-sm text-green-700 bg-green-100 rounded-2xl font-bold border border-green-200" role="alert">
        {{ session('message') }}
    </div>
    @endif

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Activities -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-1">إجمالي النشاطات</p>
                    <h4 class="text-2xl font-black text-gray-900">{{ number_format($statistics['total']) }}</h4>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-blue-600 font-bold bg-blue-50/50 py-1.5 px-3 rounded-xl w-fit">
                <span>سجل النظام الكامل</span>
            </div>
        </div>

        <!-- Today -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-1">نشاطات اليوم</p>
                    <h4 class="text-2xl font-black text-gray-900">{{ number_format($statistics['today']) }}</h4>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-green-600 font-bold bg-green-50/50 py-1.5 px-3 rounded-xl w-fit">
                <span>تحديث مباشر</span>
            </div>
        </div>

        <!-- This Week -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-1">هذا الأسبوع</p>
                    <h4 class="text-2xl font-black text-gray-900">{{ number_format($statistics['this_week']) }}</h4>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-purple-600 font-bold bg-purple-50/50 py-1.5 px-3 rounded-xl w-fit">
                <span>إجمالي 7 أيام</span>
            </div>
        </div>

        <!-- This Month -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-1">هذا الشهر</p>
                    <h4 class="text-2xl font-black text-gray-900">{{ number_format($statistics['this_month']) }}</h4>
                </div>
                <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-orange-600 font-bold bg-orange-50/50 py-1.5 px-3 rounded-xl w-fit">
                <span>الشهر الحالي</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <x-table.filter-bar :showDate="false">
            <x-slot name="actions">
                <div class="flex gap-2">
                    <x-table.column-toggler :columns="$columns" :labels="[
                        'date' => 'التاريخ',
                        'type' => 'النوع',
                        'causer' => 'المستخدم',
                        'subject' => 'الموضوع',
                        'description' => 'الوصف',
                        'actions' => 'العمليات'
                    ]" />

                    <button wire:click="exportToCsv" class="btn bg-white border-gray-100 text-gray-700 hover:bg-gray-50">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>تصدير CSV</span>
                    </button>

                    @if(auth()->user()->isAdmin())
                    <button wire:click="clearHistory"
                        wire:confirm="هل أنت متأكد من مسح سجل النشاطات المختار؟"
                        class="btn bg-rose-50 text-rose-600 border-rose-100 hover:bg-rose-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>مسح السجل</span>
                    </button>
                    @endif
                </div>
            </x-slot>

            <div class="flex flex-wrap items-center gap-4 mt-4 w-full border-t border-gray-50 pt-4">
                <!-- Type Filter -->
                <div class="relative min-w-[140px] group">
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10m-10 5h10" />
                        </svg>
                    </div>
                    <select wire:model.live="typeFilter" class="w-full appearance-none pl-9 pr-10 py-2.5 bg-gray-50 border border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 cursor-pointer transition-all text-sm font-bold text-gray-700">
                        <option value="all">جميع الأنواع</option>
                        @foreach($activityTypes as $type)
                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Causer Filter -->
                <div class="relative min-w-[140px] group">
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <select wire:model.live="causerFilter" class="w-full appearance-none pl-9 pr-10 py-2.5 bg-gray-50 border border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 cursor-pointer transition-all text-sm font-bold text-gray-700">
                        <option value="all">جميع المستخدمين</option>
                        @foreach($causers as $causer)
                        <option value="{{ $causer->id }}">{{ $causer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range Filter -->
                <div class="relative min-w-[140px] group">
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <select wire:model.live="dateRangeFilter" class="w-full appearance-none pl-9 pr-10 py-2.5 bg-gray-50 border border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 cursor-pointer transition-all text-sm font-bold text-gray-700">
                        <option value="1">آخر 24 ساعة</option>
                        <option value="7">آخر 7 أيام</option>
                        <option value="30">آخر 30 يوم</option>
                        <option value="90">آخر 90 يوم</option>
                        <option value="365">آخر سنة</option>
                    </select>
                </div>
            </div>
        </x-table.filter-bar>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-right border-separate border-spacing-y-3">
                <thead>
                    <tr class="text-gray-400 text-xs font-black uppercase tracking-wider">
                        @if($columns['date'])
                        <x-table.th field="created_at" :sortField="$sortField" :sortDirection="$sortDirection" label="التاريخ" class="px-6 py-2" />
                        @endif

                        @if($columns['type'])
                        <x-table.th field="type" :sortField="$sortField" :sortDirection="$sortDirection" label="النوع" class="px-6 py-2" />
                        @endif

                        @if($columns['causer'])
                        <th class="px-6 py-2 font-black">المستخدم</th>
                        @endif

                        @if($columns['subject'])
                        <th class="px-6 py-2 font-black">الموضوع</th>
                        @endif

                        @if($columns['description'])
                        <th class="px-6 py-2 font-black uppercase tracking-wider">الوصف</th>
                        @endif

                        @if($columns['actions'])
                        <th class="px-6 py-2 font-black uppercase tracking-wider">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                    <tr class="group bg-white hover:bg-primary-50/30 transition-all duration-300">
                        @if($columns['date'])
                        <td class="px-6 py-4 rounded-r-2xl border-y border-r border-gray-50 group-hover:border-primary-100">
                            <span class="text-xs font-black text-gray-500 whitespace-nowrap">{{ $activity->created_at->format('Y-m-d H:i:s') }}</span>
                        </td>
                        @endif

                        @if($columns['type'])
                        <td class="px-6 py-4 border-y border-gray-50 group-hover:border-primary-100">
                            <span class="px-3 py-1 text-[10px] font-black uppercase rounded-lg border {{ $activity->getTypeBadgeClass() }}">
                                {{ $activity->getTypeLabel() }}
                            </span>
                        </td>
                        @endif

                        @if($columns['causer'])
                        <td class="px-6 py-4 border-y border-gray-50 group-hover:border-primary-100">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-primary-50 flex items-center justify-center text-primary-600 font-black text-xs">
                                    {{ mb_substr($activity->causer?->name ?? 'S', 0, 1) }}
                                </div>
                                <span class="text-sm font-bold text-gray-700">{{ $activity->causer?->name ?? 'النظام' }}</span>
                            </div>
                        </td>
                        @endif

                        @if($columns['subject'])
                        <td class="px-6 py-4 border-y border-gray-50 group-hover:border-primary-100">
                            <span class="text-xs font-bold text-gray-500">{{ $activity->getSubjectName() }}</span>
                        </td>
                        @endif

                        @if($columns['description'])
                        <td class="px-6 py-4 border-y border-gray-50 group-hover:border-primary-100">
                            <div class="max-w-xs xl:max-w-md">
                                <p class="text-sm font-bold text-gray-700 truncate" title="{{ $activity->getFormattedDescription() }}">
                                    {{ $activity->getFormattedDescription() }}
                                </p>
                                @if($activity->ip_address)
                                <span class="text-[10px] font-black text-gray-300 uppercase tracking-tighter">IP: {{ $activity->ip_address }}</span>
                                @endif
                            </div>
                        </td>
                        @endif

                        @if($columns['actions'])
                        <td class="px-6 py-4 rounded-l-2xl border-y border-l border-gray-50 group-hover:border-primary-100">
                            <button wire:click="showActivityDetails({{ $activity->id }})" class="p-2 text-primary-400 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-all duration-300" title="التفاصيل">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-black text-gray-400 uppercase tracking-wider">لا توجد نشاطات حالياً</h3>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $activities->links() }}
        </div>
    </div>

    <!-- Activity Details Modal -->
    <x-modal name="activity-details" :show="$showDetailsModal" maxWidth="2xl">
        <div class="p-8">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-50">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">تفاصيل النشاط</h3>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">سجل العملية بالكامل</p>
                    </div>
                </div>
                <button @click="$dispatch('close')" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            @if($selectedActivity)
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-50">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">التاريخ والوقت</p>
                        <p class="text-sm font-bold text-gray-700">{{ $selectedActivity->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                    <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-50">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">نوع العملية</p>
                        <span class="px-3 py-1 text-[10px] font-black uppercase rounded-lg border {{ $selectedActivity->getTypeBadgeClass() }}">
                            {{ $selectedActivity->getTypeLabel() }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-50">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">المستخدم المسؤول</p>
                        <p class="text-sm font-bold text-gray-700">{{ $selectedActivity->causer?->name ?? 'النظام' }}</p>
                    </div>
                    <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-50">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">موضوع النشاط</p>
                        <p class="text-sm font-bold text-gray-700 text-primary-600">{{ $selectedActivity->getSubjectName() }}</p>
                    </div>
                </div>

                <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-50">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">الوصف الكامل</p>
                    <p class="text-sm font-bold text-gray-700 leading-relaxed">{{ $selectedActivity->getFormattedDescription() }}</p>
                </div>

                <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-50">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">بيانات الاتصال</p>
                    <div class="flex items-center gap-4 mt-2">
                        <div class="px-3 py-1 bg-white border border-gray-100 rounded-lg text-xs font-bold text-gray-500">
                            IP: {{ $selectedActivity->ip_address }}
                        </div>
                    </div>
                    @if($selectedActivity->user_agent)
                    <p class="text-[10px] font-bold text-gray-300 mt-2 leading-tight">{{ $selectedActivity->user_agent }}</p>
                    @endif
                </div>

                @if($changes = $selectedActivity->getChangesSummary())
                <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-50">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">تفاصيل التغييرات</p>
                    <div class="space-y-3">
                        @foreach($changes as $change)
                        <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black text-primary-600 uppercase">{{ $change['field'] }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-rose-50/50 p-2 rounded-lg border border-rose-50">
                                    <p class="text-[9px] font-black text-rose-400 uppercase mb-0.5 whitespace-nowrap">القيمة القديمة</p>
                                    <p class="text-xs font-bold text-rose-700 truncate @if(empty($change['old'])) italic text-rose-300 @endif">
                                        {{ $change['old'] ?? 'فارغ' }}
                                    </p>
                                </div>
                                <div class="bg-green-50/50 p-2 rounded-lg border border-green-50">
                                    <p class="text-[9px] font-black text-green-400 uppercase mb-0.5 whitespace-nowrap">القيمة الجديدة</p>
                                    <p class="text-xs font-bold text-green-700 truncate @if(empty($change['new'])) italic text-green-300 @endif">
                                        {{ $change['new'] ?? 'فارغ' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            <div class="mt-8 flex justify-end">
                <button @click="$dispatch('close')" class="btn btn-primary w-full md:w-auto px-12">
                    إغلاق
                </button>
            </div>
        </div>
    </x-modal>
</div>