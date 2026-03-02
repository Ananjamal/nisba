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
                        <th class="px-6 py-3 text-right text-xs font-black text-gray-500 uppercase tracking-widest">تفاصيل المبلغ (ريال)</th>
                        <th class="px-6 py-3 text-right text-xs font-black text-gray-500 uppercase tracking-widest">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-black text-gray-500 uppercase tracking-widest">التاريخ</th>
                        <th class="px-6 py-3 text-right text-xs font-black text-gray-500 uppercase tracking-widest">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($withdrawalRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col items-start space-y-1">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">الإجمالي</span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="font-black text-gray-500 text-sm leading-none">{{ number_format($request->amount, 2) }}</span>
                                        <span class="text-[9px] text-gray-400 font-bold">ر.س</span>
                                    </div>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-rose-400 uppercase tracking-tighter">الضريبة ({{ $request->tax_rate }}%)</span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="font-black text-rose-500 text-sm leading-none">{{ number_format($request->tax_amount, 2) }}</span>
                                        <span class="text-[9px] text-rose-400 font-bold">ر.س</span>
                                    </div>
                                </div>
                                <div class="flex flex-col pt-1 border-t border-gray-100 min-w-[80px]">
                                    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-tighter">الصافي</span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="font-black text-emerald-600 text-base leading-none">{{ number_format($request->final_amount, 2) }}</span>
                                        <span class="text-[9px] text-emerald-500 font-black">ر.س</span>
                                    </div>
                                </div>
                            </div>
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

    <x-modal name="withdrawal-request" :show="$showRequestModal" maxWidth="xl" x-on:close="$wire.set('showRequestModal', false)">
        <div class="bg-white rounded-[32px] overflow-hidden shadow-2xl border border-gray-100 text-right" dir="rtl">
            <!-- Header -->
            <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                <h3 class="text-xl font-black text-primary-900">طلب سحب عمولات</h3>
                <button wire:click="$set('showRequestModal', false)" class="text-gray-400 hover:text-gray-600 transition p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-8">
                <!-- Balance Card -->
                <div class="bg-primary-50/50 rounded-3xl p-6 border border-primary-100 mb-8 text-center group transition-all hover:bg-primary-50">
                    <p class="text-[11px] font-black text-primary-400 uppercase tracking-[0.2em] mb-2">الرصيد الكلي المتاح للسحب</p>
                    <div class="flex items-baseline justify-center gap-2">
                        <span class="text-3xl font-black text-primary-600">{{ number_format($this->getBalance(), 2) }}</span>
                        <span class="text-sm font-black text-primary-400">ر.س</span>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Amount Input -->
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-gray-700 mr-2">المبلغ المطلوب سحبه</label>
                        <div class="relative group">
                            <input type="number" wire:model.live="amount" step="0.01"
                                class="w-full pl-12 pr-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-lg font-black focus:bg-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-left"
                                dir="ltr" placeholder="0.00">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-sm font-black text-gray-400 group-focus-within:text-primary-500">ر.س</span>
                        </div>
                        @error('amount') <p class="text-rose-500 text-xs font-bold mt-1 mr-2">{{ $message }}</p> @enderror
                    </div>

                    <!-- Tax Breakdown (Moved for visibility) -->
                    <div class="p-6 bg-emerald-50 rounded-3xl border border-emerald-100 space-y-3 animate-pulse-subtle">
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-bold text-emerald-600 uppercase tracking-wider text-[11px]">المبلغ الإجمالي</span>
                            <span class="font-black text-emerald-900">{{ number_format($amount ?: 0, 2) }} ر.س</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-bold text-rose-500 uppercase tracking-wider text-[11px]">الضريبة ({{ $settings['tax_rate'] }}%)</span>
                            <span class="font-black text-rose-500">- {{ number_format($this->calculateTaxAmount(), 2) }} ر.س</span>
                        </div>
                        <div class="pt-3 border-t border-emerald-200 flex justify-between items-center">
                            <span class="font-black text-emerald-900 text-sm">المبلغ الصافي النهائي</span>
                            <span class="text-xl font-black text-emerald-600">{{ number_format($this->calculateFinalAmount(), 2) }} ر.س</span>
                        </div>
                    </div>

                    <!-- Bank Details Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-black text-gray-700 mr-2">اسم صاحب الحساب</label>
                            <input type="text" wire:model="account_holder_name"
                                class="w-full px-6 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:bg-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                                placeholder="الاسم في البنك">
                            @error('account_holder_name') <p class="text-rose-500 text-[10px] font-bold mt-1 mr-2">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-black text-gray-700 mr-2">اسم البنك</label>
                            <input type="text" wire:model="bank_name"
                                class="w-full px-6 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:bg-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                                placeholder="مثال: الراجحي">
                            @error('bank_name') <p class="text-rose-500 text-[10px] font-bold mt-1 mr-2">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- IBAN -->
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-gray-700 mr-2">رقم الآيبان (IBAN)</label>
                        <input type="text" wire:model="iban"
                            class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-base font-mono font-bold focus:bg-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-left"
                            dir="ltr" placeholder="SA...">
                        @error('iban') <p class="text-rose-500 text-xs font-bold mt-1 mr-2">{{ $message }}</p> @enderror
                    </div>

                    <!-- IBAN Proof Upload -->
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-gray-700 mr-2">إثبات الآيبان (Image)</label>
                        <div class="relative group">
                            <label for="iban_proof" class="flex flex-col items-center justify-center w-full py-6 bg-gray-50 border-2 border-dashed border-gray-200 rounded-[2rem] cursor-pointer hover:bg-white hover:border-primary-300 transition-all group-focus-within:ring-4 ring-primary-500/10">
                                @if($iban_proof)
                                <div class="flex items-center gap-3 text-emerald-600">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-sm font-black">تم اختيار الصورة</span>
                                </div>
                                @else
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">اختر صورة</p>
                                </div>
                                @endif
                                <input id="iban_proof" type="file" wire:model="iban_proof" class="hidden">
                            </label>
                        </div>
                        @error('iban_proof') <p class="text-rose-500 text-xs font-bold mt-1 mr-2">{{ $message }}</p> @enderror
                    </div>


                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-3 pt-4">
                        <button wire:click="createWithdrawalRequest" wire:loading.attr="disabled"
                            class="w-full bg-primary-600 hover:bg-primary-700 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-primary-200 active:scale-[0.98] disabled:opacity-50">
                            <span wire:loading.remove>تقديم طلب السحب</span>
                            <span wire:loading>جاري المعالجة...</span>
                        </button>
                        <button wire:click="$set('showRequestModal', false)"
                            class="w-full text-gray-400 font-bold text-sm py-2 hover:bg-gray-50 rounded-xl transition-colors">
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>

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