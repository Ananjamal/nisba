<div>
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">إدارة العملاء</h2>
            <button wire:click="$dispatch('open-modal', { component: 'affiliate.create-lead' })" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                إضافة عميل جديد
            </button>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <input type="text" wire:model.live="search" placeholder="البحث بالاسم، الشركة، أو الهاتف" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">جميع الحالات</option>
                    <option value="under_review">قيد المراجعة</option>
                    <option value="contacting">جاري التواصل</option>
                    <option value="sold">تم البيع</option>
                    <option value="cancelled">ملغي</option>
                </select>
            </div>
            
            <div>
                <select wire:model.live="cityFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع المدن</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->name }}">{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <div class="text-sm text-gray-600">
                    إجمالي النتائج: {{ $leads->total() }}
                </div>
            </div>
        </div>

        <!-- Leads Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">العميل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المدينة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الخدمات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التجديد</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($leads as $lead)
                        <tr class="{{ $lead->is_duplicate ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $lead->client_name }}</div>
                                    @if($lead->company_name)
                                        <div class="text-sm text-gray-500">{{ $lead->company_name }}</div>
                                    @endif
                                    <div class="text-sm text-gray-500">{{ $lead->client_phone }}</div>
                                    @if($lead->email)
                                        <div class="text-sm text-gray-500">{{ $lead->email }}</div>
                                    @endif
                                    <div class="text-xs text-gray-400 font-mono">{{ $lead->unique_id }}</div>
                                    @if($lead->is_duplicate)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                            تكرار
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $lead->city }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($lead->services as $service)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                                              style="background-color: {{ $service->color }}20; color: {{ $service->color }}; border: 1px solid {{ $service->color }};">
                                            {{ $service->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $this->getStatusBadgeClass($lead->status) }}">
                                    {{ $this->getStatusLabel($lead->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($lead->subscription_renewal_date)
                                    <div class="space-y-1">
                                        <div>{{ $lead->subscription_renewal_date->format('Y-m-d') }}</div>
                                        @if($this->isNearRenewal($lead))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                قريب التجديد
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">غير محدد</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    @if($lead->is_duplicate && auth()->user()->isAdmin())
                                        <button wire:click="approveDuplicateLead" 
                                                class="text-green-600 hover:text-green-800 text-xs">
                                            اعتماد
                                        </button>
                                        <button wire:click="rejectDuplicateLead" 
                                                class="text-red-600 hover:text-red-800 text-xs">
                                            رفض
                                        </button>
                                    @endif
                                    
                                    @if($lead->subscription_renewal_date)
                                        <button wire:click="$dispatch('open-modal', { component: 'affiliate.renew-subscription', leadId: {{ $lead->id }} })" 
                                                class="text-blue-600 hover:text-blue-800 text-xs">
                                            تجديد
                                        </button>
                                    @endif
                                    
                                    <button wire:click="$dispatch('open-modal', { component: 'affiliate.edit-lead', leadId: {{ $lead->id }} })" 
                                            class="text-gray-600 hover:text-gray-800 text-xs">
                                        تعديل
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $leads->links() }}
        </div>
    </div>

    <!-- Duplicate Approval Modal -->
    @if($showDuplicateModal && $currentLead)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="showDuplicateModal = false">
            <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">اكتشاف عملاء محتملين مكررين</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- New Lead -->
                        <div class="border rounded-lg p-4">
                            <h4 class="font-semibold text-green-700 mb-3">العميل الجديد</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>الاسم:</strong> {{ $currentLead->client_name }}</p>
                                <p><strong>الهاتف:</strong> {{ $currentLead->client_phone }}</p>
                                <p><strong>البريد:</strong> {{ $currentLead->email ?? 'غير محدد' }}</p>
                                <p><strong>الشركة:</strong> {{ $currentLead->company_name ?? 'غير محدد' }}</p>
                                <p><strong>المدينة:</strong> {{ $currentLead->city }}</p>
                                <p><strong>المعرّف:</strong> {{ $currentLead->unique_id }}</p>
                            </div>
                        </div>
                        
                        <!-- Existing Duplicates -->
                        <div class="border rounded-lg p-4">
                            <h4 class="font-semibold text-red-700 mb-3">العملاء الموجودون</h4>
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($duplicateLeads as $duplicate)
                                    <div class="border-l-4 border-red-500 pl-3 text-sm">
                                        <p><strong>الاسم:</strong> {{ $duplicate->client_name }}</p>
                                        <p><strong>الهاتف:</strong> {{ $duplicate->client_phone }}</p>
                                        <p><strong>المعرّف:</strong> {{ $duplicate->unique_id }}</p>
                                        <p><strong>الحالة:</strong> {{ $this->getStatusLabel($duplicate->status) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات الاعتماد/الرفض</label>
                        <textarea wire:model="approvalNotes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="يرجى توضيح سبب القرار..."></textarea>
                        @error('approvalNotes')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="items-center px-4 py-3 flex gap-3">
                        <button wire:click="approveDuplicateLead" 
                                class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md hover:bg-green-700">
                            اعتماد العميل الجديد
                        </button>
                        <button wire:click="rejectDuplicateLead" 
                                class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md hover:bg-red-700">
                            رفض العميل الجديد
                        </button>
                        <button wire:click="showDuplicateModal = false" 
                                class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md hover:bg-gray-400">
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
