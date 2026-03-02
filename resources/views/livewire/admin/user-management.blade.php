<div class="space-y-8" x-data="{ selectAll: @entangle('selectAll'), selectedUsers: @entangle('selectedUsers') }">
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <x-table.filter-bar :statusOptions="['active' => 'نشط', 'pending' => 'في انتظار التفعيل', 'suspended' => 'موقوف', 'inactive' => 'خامل']" :showDate="false">
            <x-slot name="actions">
                <div class="flex gap-2">
                    <x-table.column-toggler :columns="$columns" :labels="[
                        'user' => 'المستخدم',
                        'status' => 'الحالة',
                        'role' => 'الدور',
                        'referral_code' => 'كود الإحالة',
                        'actions' => 'الإجراءات'
                    ]" />

                    <button wire:click="$dispatch('open-modal', { component: 'admin.create-user' })" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>إضافة مستخدم</span>
                    </button>
                </div>
            </x-slot>

            <div class="relative min-w-[140px] group">
                <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <select wire:model.live="role_filter"
                    class="w-full appearance-none pl-9 pr-10 py-2.5 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 cursor-pointer shadow-sm transition-all text-sm font-bold text-gray-700 hover:border-gray-300 hover:text-gray-900">
                    <option value="all">جميع الأدوار</option>
                    <option value="admin">مدير</option>
                    <option value="affiliate">مسوق</option>
                </select>
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            @if(!empty($selectedUsers))
            <div class="flex gap-2">
                <button wire:click="bulkStatusUpdate('active')"
                    class="px-4 py-2.5 bg-green-50 text-green-700 rounded-xl hover:bg-green-100 transition-all border border-green-100 text-sm font-bold">
                    تفعيل المحددين
                </button>
                <button wire:click="bulkStatusUpdate('suspended')"
                    class="px-4 py-2.5 bg-rose-50 text-rose-700 rounded-xl hover:bg-rose-100 transition-all border border-rose-100 text-sm font-bold">
                    إيقاف المحددين
                </button>
            </div>
            @endif
        </x-table.filter-bar>

        <!-- Pending Deletion Requests -->
        @if($deletionRequests->count() > 0)
        <div class="mb-8 p-6 bg-rose-50 border border-rose-100 rounded-2xl">
            <h3 class="text-base font-black text-rose-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                طلبات الحذف المعلقة
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($deletionRequests as $request)
                <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-rose-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full border-2 border-rose-50 p-0.5">
                            <img src="{{ $request->user->profile_image_url }}" alt="{{ $request->user->name }}" class="w-full h-full rounded-full object-cover">
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $request->user->name }}</p>
                            <p class="text-xs text-rose-600 font-medium">{{ $request->reason }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">طلب بواسطة: {{ $request->requestedBy->name }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="approveDeletion({{ $request->id }})" class="p-2 bg-green-50 text-green-600 hover:bg-green-100 rounded-xl transition-colors" title="موافقة">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <button wire:click="rejectDeletion({{ $request->id }})" class="p-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl transition-colors" title="رفض">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Pending Lead Deletion Requests -->
        @if($leadDeletionRequests->count() > 0)
        <div class="mb-8 p-6 bg-amber-50 border border-amber-100 rounded-2xl">
            <h3 class="text-base font-black text-amber-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                طلبات حذف العملاء المعلقة
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($leadDeletionRequests as $request)
                <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-amber-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-black">
                            {{ mb_substr($request->lead->client_name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $request->lead->client_name }}</p>
                            <p class="text-xs text-amber-600 font-medium">{{ $request->reason }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">طلب بواسطة: {{ $request->requestedBy->name }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="approveLeadDeletion({{ $request->id }})" class="p-2 bg-green-50 text-green-600 hover:bg-green-100 rounded-xl transition-colors" title="موافقة">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <button wire:click="rejectLeadDeletion({{ $request->id }})" class="p-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl transition-colors" title="رفض">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-right border-separate border-spacing-y-3">
                <thead>
                    <tr class="text-gray-400 text-xs font-black uppercase tracking-wider">
                        <th class="px-6 py-2">
                            <div class="flex items-center justify-center">
                                <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded-lg border-gray-200 text-primary-600 focus:ring-primary-500/20 transition-all cursor-pointer">
                            </div>
                        </th>
                        @if($columns['user'])
                        <x-table.th field="name" :sortField="$sortField" :sortDirection="$sortDirection" label="المستخدم" class="px-6 py-2" />
                        @endif
                        @if($columns['status'])
                        <x-table.th field="status" :sortField="$sortField" :sortDirection="$sortDirection" label="الحالة" class="px-6 py-2" />
                        @endif
                        @if($columns['role'])
                        <x-table.th field="role" :sortField="$sortField" :sortDirection="$sortDirection" label="الدور" class="px-6 py-2" />
                        @endif
                        @if($columns['referral_code'])
                        <th class="px-6 py-2 font-black">كود الإحالة</th>
                        @endif
                        @if($columns['actions'])
                        <th class="px-6 py-2 font-black">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="group bg-white hover:bg-primary-50/30 transition-all duration-300">
                        <td class="px-6 py-4 border-y border-r border-gray-50 rounded-r-2xl first:border-r">
                            <div class="flex items-center justify-center">
                                <input type="checkbox" wire:model.live="selectedUsers" value="{{ $user->id }}" class="w-4 h-4 rounded-lg border-gray-200 text-primary-600 focus:ring-primary-500/20 transition-all cursor-pointer">
                            </div>
                        </td>

                        @if($columns['user'])
                        <td class="px-6 py-4 border-y border-gray-50">
                            <div class="flex items-center gap-4">
                                <div class="relative group/avatar">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-50 p-0.5 transition-transform group-hover/avatar:scale-105 duration-300 shadow-sm">
                                        <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" class="w-full h-full rounded-2xl object-cover">
                                    </div>
                                    <div class="absolute -bottom-1 -left-1 w-4 h-4 rounded-full border-2 border-white {{ $user->status === 'active' ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                                </div>
                                <div class="flex flex-col text-start">
                                    <span class="text-sm font-black text-gray-900 leading-none">{{ $user->name }}</span>
                                    <span class="text-xs text-gray-400 font-bold mt-1.5">{{ $user->email }}</span>
                                    @if($user->phone)
                                    <span class="text-[10px] text-primary-500 font-black mt-2 bg-primary-50 px-2 py-0.5 rounded-full w-fit">{{ $user->phone }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @endif

                        @if($columns['status'])
                        <td class="px-6 py-4 border-y border-gray-50">
                            <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider border {{ $this->getStatusBadgeClass($user->status) }}">
                                {{ $this->getStatusLabel($user->status) }}
                            </span>
                        </td>
                        @endif

                        @if($columns['role'])
                        <td class="px-6 py-4 border-y border-gray-50">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full {{ $user->role === 'admin' ? 'bg-purple-500' : 'bg-blue-500' }}"></div>
                                <span class="text-xs font-bold text-gray-700">{{ $this->getRoleLabel($user->role) }}</span>
                            </div>
                        </td>
                        @endif

                        @if($columns['referral_code'])
                        <td class="px-6 py-4 border-y border-gray-50">
                            <div class="flex items-center gap-2">
                                @if($user->referral_code)
                                <span class="font-mono text-[11px] font-bold bg-gray-50 px-2.5 py-1.5 rounded-lg border border-gray-100 text-gray-600">{{ $user->referral_code }}</span>
                                @else
                                <button wire:click="generateReferralCode({{ $user->id }})" class="text-[10px] font-black text-primary-600 hover:text-primary-800 bg-primary-50 px-3 py-1.5 rounded-lg transition-colors border border-primary-100">توليد كود</button>
                                @endif
                            </div>
                        </td>
                        @endif

                        @if($columns['actions'])
                        <td class="px-6 py-4 border-y border-l border-gray-50 rounded-l-2xl">
                            <div class="flex items-center gap-1.5 justify-end">
                                <!-- Status Action -->
                                @if($user->status === 'active')
                                <button wire:click="updateUserStatus({{ $user->id }}, 'suspended')" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-all" title="إيقاف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                </button>
                                @else
                                <button wire:click="updateUserStatus({{ $user->id }}, 'active')" class="p-2 text-green-500 hover:bg-green-50 rounded-xl transition-all" title="تفعيل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                @endif

                                <!-- Edit -->
                                <button wire:click="$dispatch('open-modal', { component: 'admin.update-user', userId: {{ $user->id }} })" class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-xl transition-all" title="تعديل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                <!-- Activity Log -->
                                <button wire:click="$dispatch('open-modal', { component: 'admin.activity-log', userId: {{ $user->id }} })" class="p-2 text-primary-500 hover:bg-primary-50 rounded-xl transition-all" title="السجل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>

                                <!-- Delete -->
                                <button wire:click="confirmUserDeletion({{ $user->id }})" class="p-2 text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="حذف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Deletion Confirmation Modal -->
    <x-modal name="confirm-user-deletion" :show="$showDeletionModal" maxWidth="md">
        <div class="p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-gray-900">تأكيد طلب حذف المستخدم</h3>
                    <p class="text-xs font-bold text-gray-400 mt-1">سيتم إرسال الطلب للموافقة قبل التنفيذ</p>
                </div>
            </div>

            <div class="space-y-6">
                <p class="text-sm font-bold text-gray-600 leading-relaxed">
                    هل أنت متأكد من طلب حذف المستخدم <span class="text-rose-600 font-black">"{{ $userToDelete?->name }}"</span>؟
                </p>

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">سبب الحذف</label>
                    <textarea wire:model="deletionReason" rows="3"
                        class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all text-sm font-bold text-gray-700 placeholder:text-gray-300"
                        placeholder="يرجى توضيح سبب طلب الحذف..."></textarea>
                    @error('deletionReason')
                    <span class="text-rose-500 text-[10px] font-black mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button wire:click="requestUserDeletion"
                        class="flex-1 py-3 bg-rose-600 text-white text-sm font-black rounded-2xl hover:bg-rose-700 transition-all shadow-sm shadow-rose-200">
                        تأكيد الطلب
                    </button>
                    <button @click="$dispatch('close')"
                        class="flex-1 py-3 bg-gray-50 text-gray-600 text-sm font-black rounded-2xl hover:bg-gray-100 transition-all">
                        تراجع
                    </button>
                </div>
            </div>
        </div>
    </x-modal>
</div>