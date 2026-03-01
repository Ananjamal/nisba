<div>
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">إدارة السحوبات المالية</h2>
            <button wire:click="showRequestModal = true" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                طلب سحب جديد
            </button>
        </div>

        <!-- System Settings Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 bg-blue-50 rounded-lg">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-900">{{ $settings['tax_rate'] }}%</div>
                <div class="text-sm text-blue-700">نسبة الضريبة</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-900">{{ number_format($settings['min_amount'], 2) }} ريال</div>
                <div class="text-sm text-blue-700">الحد الأدنى للسحب</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-900">{{ number_format($settings['max_amount'], 2) }} ريال</div>
                <div class="text-sm text-blue-700">الحد الأقصى للسحب</div>
            </div>
        </div>

        <!-- Withdrawal Requests Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المبلغ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الضريبة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الصافي</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($withdrawalRequests as $request)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ number_format($request->amount, 2) }} ريال</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ number_format($request->tax_amount, 2) }} ريال</div>
                                <div class="text-xs text-gray-500">{{ $request->tax_rate }}%</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-green-600">{{ number_format($request->final_amount, 2) }} ريال</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $this->getStatusBadgeClass($request->status) }}">
                                    {{ $this->getStatusLabel($request->status) }}
                                </span>
                                @if($request->delegated_to)
                                    <div class="text-xs text-blue-600 mt-1">مفوّض إلى: {{ $request->delegatedTo->name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $request->created_at->format('Y-m-d') }}</div>
                                @if($request->admin_approved_at)
                                    <div class="text-xs text-green-600">معتمد: {{ $request->admin_approved_at->format('Y-m-d') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    @if($request->status === 'pending')
                                        <button wire:click="requestDelegation({{ $request->id }})" 
                                                class="text-blue-600 hover:text-blue-800 text-xs">
                                            تفويض
                                        </button>
                                        <button wire:click="cancelWithdrawalRequest({{ $request->id }})" 
                                                class="text-red-600 hover:text-red-800 text-xs">
                                            إلغاء
                                        </button>
                                    @endif
                                    
                                    @if($request->notes)
                                        <button wire:click="$dispatch('show-notes', { notes: '{{ $request->notes }}' })" 
                                                class="text-gray-600 hover:text-gray-800 text-xs">
                                            ملاحظات
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $withdrawalRequests->links() }}
        </div>
    </div>

    <!-- New Withdrawal Request Modal -->
    @if($showRequestModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="showRequestModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">طلب سحب جديد</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ (ريال)</label>
                            <input type="number" wire:model.live="amount" step="0.01" min="{{ $settings['min_amount'] }}" max="{{ $settings['max_amount'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('amount')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                            <div class="text-xs text-gray-500 mt-1">
                                الحد الأدنى: {{ number_format($settings['min_amount'], 2) }} ريال | 
                                الحد الأقصى: {{ number_format($settings['max_amount'], 2) }} ريال
                            </div>
                        </div>
                        
                        @if($amount)
                            <div class="p-3 bg-gray-50 rounded">
                                <div class="flex justify-between text-sm">
                                    <span>المبلغ الإجمالي:</span>
                                    <span>{{ number_format($amount, 2) }} ريال</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>الضريبة ({{ $settings['tax_rate'] }}%):</span>
                                    <span>{{ number_format($this->calculateTaxAmount(), 2) }} ريال</span>
                                </div>
                                <div class="flex justify-between font-bold text-green-600">
                                    <span>المبلغ الصافي:</span>
                                    <span>{{ number_format($this->calculateFinalAmount(), 2) }} ريال</span>
                                </div>
                            </div>
                        @endif
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات (اختياري)</label>
                            <textarea wire:model="notes" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="أي ملاحظات إضافية..."></textarea>
                        </div>
                    </div>
                    
                    <div class="items-center px-4 py-3">
                        <button wire:click="createWithdrawalRequest" 
                                class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-24 mr-3 hover:bg-blue-700">
                            إرسال
                        </button>
                        <button wire:click="showRequestModal = false" 
                                class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400">
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delegation Modal -->
    @if($showDelegationModal && $selectedWithdrawal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="showDelegationModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">تفويض طلب السحب</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500 mb-4">
                            طلب السحب رقم: {{ $selectedWithdrawal->id }}<br>
                            المبلغ: {{ number_format($selectedWithdrawal->amount, 2) }} ريال
                        </p>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">تفويض إلى</label>
                                <select wire:model="delegateToUserId" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">اختر المستخدم</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('delegateToUserId')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">سبب التفويض</label>
                                <textarea wire:model="delegationNotes" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="يرجى توضيح سبب طلب التفويض..."></textarea>
                                @error('delegationNotes')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="items-center px-4 py-3">
                        <button wire:click="submitDelegation" 
                                class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-24 mr-3 hover:bg-blue-700">
                            تفويض
                        </button>
                        <button wire:click="showDelegationModal = false" 
                                class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400">
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
