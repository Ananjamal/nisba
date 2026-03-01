<div>
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">سجل النشاطات</h2>
            <div class="flex gap-2">
                <button wire:click="exportToCsv" 
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    تصدير CSV
                </button>
                @if(auth()->user()->hasRole('super-admin'))
                    <button wire:click="clearHistory" 
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                        مسح السجل
                    </button>
                @endif
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">إجمالي النشاطات</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $statistics['total'] }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium">اليوم</p>
                        <p class="text-2xl font-bold text-green-900">{{ $statistics['today'] }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium">هذا الأسبوع</p>
                        <p class="text-2xl font-bold text-purple-900">{{ $statistics['this_week'] }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-orange-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-orange-600 font-medium">هذا الشهر</p>
                        <p class="text-2xl font-bold text-orange-900">{{ $statistics['this_month'] }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div>
                <input type="text" wire:model.live="search" placeholder="البحث في النشاطات" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <select wire:model.live="typeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">جميع الأنواع</option>
                    @foreach($activityTypes as $type)
                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <select wire:model.live="causerFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">جميع المستخدمين</option>
                    @foreach($causers as $causer)
                        <option value="{{ $causer->id }}">{{ $causer->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <select wire:model.live="subjectFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">جميع الكائنات</option>
                    @foreach($subjectTypes as $type)
                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <select wire:model.live="dateRangeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="1">آخر 24 ساعة</option>
                    <option value="7">آخر 7 أيام</option>
                    <option value="30">آخر 30 يوم</option>
                    <option value="90">آخر 90 يوم</option>
                    <option value="365">آخر سنة</option>
                </select>
            </div>
        </div>

        <!-- Activities Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            التاريخ
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            النوع
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المستخدم
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الموضوع
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الوصف
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($activities as $activity)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $activity->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $activity->getTypeBadgeClass() }}">
                                    {{ $activity->getTypeLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $activity->causer?->name ?? 'نظام' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $activity->getSubjectName() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div>
                                    <p>{{ $activity->getFormattedDescription() }}</p>
                                    @if($activity->ip_address)
                                        <p class="text-xs text-gray-500">IP: {{ $activity->ip_address }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                <button wire:click="showActivityDetails({{ $activity->id }})" 
                                        class="text-blue-600 hover:text-blue-900">
                                    تفاصيل
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                لا توجد نشاطات حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {{ $activities->links() }}
    </div>

    <!-- Activity Details Modal -->
    @if($showDetailsModal && $selectedActivity)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="showDetailsModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">تفاصيل النشاط</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <strong>التاريخ:</strong> {{ $selectedActivity->created_at->format('Y-m-d H:i:s') }}
                        </div>
                        
                        <div>
                            <strong>النوع:</strong> 
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $selectedActivity->getTypeBadgeClass() }}">
                                {{ $selectedActivity->getTypeLabel() }}
                            </span>
                        </div>
                        
                        <div>
                            <strong>المستخدم:</strong> {{ $selectedActivity->causer?->name ?? 'نظام' }}
                        </div>
                        
                        <div>
                            <strong>الموضوع:</strong> {{ $selectedActivity->getSubjectName() }}
                        </div>
                        
                        <div>
                            <strong>الوصف:</strong> {{ $selectedActivity->getFormattedDescription() }}
                        </div>
                        
                        <div>
                            <strong>عنوان IP:</strong> {{ $selectedActivity->ip_address }}
                        </div>
                        
                        @if($selectedActivity->user_agent)
                            <div>
                                <strong>المتصفح:</strong> 
                                <p class="text-xs text-gray-600 mt-1">{{ $selectedActivity->user_agent }}</p>
                            </div>
                        @endif
                        
                        @if($changes = $selectedActivity->getChangesSummary())
                            <div>
                                <strong>التغييرات:</strong>
                                <div class="mt-2 space-y-1">
                                    @foreach($changes as $change)
                                        <div class="text-sm bg-gray-50 p-2 rounded">
                                            <strong>{{ $change['field'] }}:</strong>
                                            <br>
                                            <span class="text-red-600">من: {{ $change['old'] ?? 'لا شيء' }}</span>
                                            <br>
                                            <span class="text-green-600">إلى: {{ $change['new'] ?? 'لا شيء' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6">
                        <button wire:click="showDetailsModal = false" 
                                class="w-full bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">
                            إغلاق
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
