<div>
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">إدارة تجديدات الاشتراكات</h2>
            <button wire:click="openCreateRenewalModal"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                إنشاء تجديد جديد
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">إجمالي التجديدات</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $statistics['total'] }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-yellow-600 font-medium">في الانتظار</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ $statistics['pending'] }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium">مكتمل</p>
                        <p class="text-2xl font-bold text-green-900">{{ $statistics['completed'] }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium">قريباً</p>
                        <p class="text-2xl font-bold text-purple-900">{{ $statistics['upcoming'] }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-red-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600 font-medium">منتهي</p>
                        <p class="text-2xl font-bold text-red-900">{{ $statistics['expired'] }}</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <input type="text" wire:model.live="search" placeholder="البحث بالاسم أو الشركة" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">جميع الحالات</option>
                    <option value="pending">في الانتظار</option>
                    <option value="completed">مكتمل</option>
                    <option value="failed">فشل</option>
                    <option value="cancelled">ملغي</option>
                </select>
            </div>
            
            <div>
                <select wire:model.live="dateRangeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">جميع الفترات</option>
                    <option value="upcoming">قريباً (30 يوم)</option>
                    <option value="expired">منتهي</option>
                    <option value="this_month">هذا الشهر</option>
                    <option value="next_month">الشهر القادم</option>
                </select>
            </div>
            
            <div>
                <div class="flex gap-2">
                    @if(!empty($selectedRenewals))
                        <button wire:click="showBulkRenewalModal = true" 
                                class="flex-1 bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 transition text-sm">
                            تجديد المحددين
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Renewals Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded">
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            العميل
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            تاريخ التجديد
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المبلغ
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الحالة
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المسوق
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($renewals as $renewal)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selectedRenewals" value="{{ $renewal->id }}" class="rounded">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $renewal->lead->client_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $renewal->lead->company_name ?? '---' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm text-gray-900">{{ $renewal->renewal_date->format('Y-m-d') }}</div>
                                    <div class="text-xs {{ $this->getDaysBadgeClass($renewal->getDaysUntilRenewal()) }} px-2 py-1 rounded-full inline-block">
                                        {{ $this->getDaysLabel($renewal->getDaysUntilRenewal()) }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($renewal->renewal_amount, 2) }} ريال
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $renewal->getStatusBadgeClass() }}">
                                    {{ $renewal->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $renewal->lead->users->first()->name ?? '---' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                <div class="flex gap-2">
                                    @if($renewal->status === 'pending')
                                        <button wire:click="openRenewalModal({{ $renewal->id }})" 
                                                class="text-blue-600 hover:text-blue-900">
                                            تجديد
                                        </button>
                                        <button wire:click="sendRenewalReminder({{ $renewal->id }})" 
                                                class="text-yellow-600 hover:text-yellow-900">
                                            تذكير
                                        </button>
                                    @endif
                                    <button wire:click="cancelRenewal({{ $renewal->id }})" 
                                            class="text-red-600 hover:text-red-900">
                                        إلغاء
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                لا توجد تجديدات حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {{ $renewals->links() }}
    </div>

    <!-- Renewal Modal -->
    @if($showRenewalModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="showRenewalModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">تجديد الاشتراك</h3>
                    
                    @if($selectedRenewal)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">
                                <strong>العميل:</strong> {{ $selectedRenewal->lead->client_name }}<br>
                                <strong>تاريخ الانتهاء الحالي:</strong> {{ $selectedRenewal->lead->subscription_renewal_date->format('Y-m-d') }}
                            </p>
                        </div>
                    @endif

                    <form wire:submit.prevent="processRenewal">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">مبلغ التجديد</label>
                            <input type="number" wire:model="renewalAmount" step="0.01" min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('renewalAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الانتهاء الجديد</label>
                            <input type="date" wire:model="newExpiryDate" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('newExpiryDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                            <textarea wire:model="renewalNotes" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            @error('renewalNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                                تأكيد التجديد
                            </button>
                            <button type="button" wire:click="showRenewalModal = false" 
                                    class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Renewal Modal -->
    @if($showBulkRenewalModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="showBulkRenewalModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">تجديد جماعي</h3>
                    
                    <form wire:submit.prevent="bulkRenewal">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">عدد الأيام للإضافة</label>
                            <input type="number" wire:model="bulkRenewalDays" min="1" max="365" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('bulkRenewalDays') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ الموحد</label>
                            <input type="number" wire:model="bulkRenewalAmount" step="0.01" min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('bulkRenewalAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                                تجديد الكل
                            </button>
                            <button type="button" wire:click="showBulkRenewalModal = false" 
                                    class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Renewal Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="showCreateModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">إنشاء تجديد جديد</h3>

                    <form wire:submit.prevent="createRenewal">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">العميل</label>
                            <select wire:model.live="createLeadId"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">اختر العميل</option>
                                @foreach($leadsForRenewal as $lead)
                                    <option value="{{ $lead->id }}">
                                        {{ $lead->client_name }}{{ $lead->company_name ? ' - '.$lead->company_name : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('createLeadId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ التجديد</label>
                            <input type="date" wire:model="createRenewalDate"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('createRenewalDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ</label>
                            <input type="number" wire:model="createRenewalAmount" step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('createRenewalAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                            <textarea wire:model="createRenewalNotes" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            @error('createRenewalNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                                إنشاء
                            </button>
                            <button type="button" wire:click="showCreateModal = false"
                                    class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
