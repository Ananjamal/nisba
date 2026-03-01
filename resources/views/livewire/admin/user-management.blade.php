<div>
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">إدارة المسوقين</h2>
            <button wire:click="$dispatch('open-modal', { component: 'admin.create-user' })" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                إضافة مسوق جديد
            </button>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <input type="text" wire:model.live="search" placeholder="البحث بالاسم، البريد، أو الهاتف" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">جميع الحالات</option>
                    <option value="active">نشط</option>
                    <option value="pending">في انتظار التفعيل</option>
                    <option value="suspended">موقوف</option>
                    <option value="inactive">خامل</option>
                </select>
            </div>
            
            <div>
                <select wire:model.live="roleFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">جميع الأدوار</option>
                    <option value="admin">مدير</option>
                    <option value="affiliate">مسوق</option>
                </select>
            </div>
            
            <div>
                <div class="flex gap-2">
                    @if(!empty($selectedUsers))
                        <button wire:click="bulkStatusUpdate('active')" 
                                class="flex-1 bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 transition text-sm">
                            تفعيل المحددين
                        </button>
                        <button wire:click="bulkStatusUpdate('suspended')" 
                                class="flex-1 bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 transition text-sm">
                            إيقاف المحددين
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Deletion Requests -->
        @if($deletionRequests->count() > 0)
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h3 class="text-lg font-semibold text-red-800 mb-3">طلبات الحذف المعلقة</h3>
                <div class="space-y-2">
                    @foreach($deletionRequests as $request)
                        <div class="flex items-center justify-between p-3 bg-white rounded border">
                            <div class="flex items-center gap-3">
                                <img src="{{ $request->user->profile_image_url }}" alt="{{ $request->user->name }}" 
                                     class="w-10 h-10 rounded-full">
                                <div>
                                    <p class="font-medium">{{ $request->user->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $request->reason }}</p>
                                    <p class="text-xs text-gray-500">طلب بواسطة: {{ $request->requestedBy->name }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="approveDeletion({{ $request->id }})" 
                                        class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                    موافقة
                                </button>
                                <button wire:click="rejectDeletion({{ $request->id }})" 
                                        class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                    رفض
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Users Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded">
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستخدم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدور</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">كود الإحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selectedUsers" value="{{ $user->id }}" class="rounded">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" 
                                         class="w-10 h-10 rounded-full ml-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $this->getStatusBadgeClass($user->status) }}">
                                    {{ $this->getStatusLabel($user->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $this->getRoleLabel($user->role) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $user->referral_code ?? 'غير محدد' }}</span>
                                    @if(!$user->referral_code)
                                        <button wire:click="generateReferralCode({{ $user->id }})" 
                                                class="text-blue-600 hover:text-blue-800 text-xs">
                                            توليد
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <!-- Profile Image Upload -->
                                    <div class="relative">
                                        <input type="file" wire:model="profileImage.{{ $user->id }}" 
                                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                               accept="image/*">
                                        <button class="text-blue-600 hover:text-blue-800 text-xs">
                                            تحديث الصورة
                                        </button>
                                    </div>
                                    
                                    <!-- Status Actions -->
                                    @if($user->status === 'active')
                                        <button wire:click="updateUserStatus({{ $user->id }}, 'suspended')" 
                                                class="text-red-600 hover:text-red-800 text-xs">
                                            إيقاف
                                        </button>
                                    @else
                                        <button wire:click="updateUserStatus({{ $user->id }}, 'active')" 
                                                class="text-green-600 hover:text-green-800 text-xs">
                                            تفعيل
                                        </button>
                                    @endif
                                    
                                    <!-- Activity Log -->
                                    <button wire:click="$dispatch('open-modal', { component: 'admin.activity-log', userId: {{ $user->id }} })" 
                                            class="text-gray-600 hover:text-gray-800 text-xs">
                                        السجل
                                    </button>
                                    
                                    <!-- Delete Request -->
                                    <button wire:click="confirmUserDeletion({{ $user->id }})" 
                                            class="text-red-600 hover:text-red-800 text-xs">
                                        طلب حذف
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
            {{ $users->links() }}
        </div>
    </div>

    <!-- Deletion Confirmation Modal -->
    @if($showDeletionModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="showDeletionModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">تأكيد طلب حذف المستخدم</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            هل أنت متأكد من طلب حذف المستخدم "{{ $userToDelete->name }}"؟
                            سيتم إرسال الطلب للموافقة قبل التنفيذ.
                        </p>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">سبب الحذف</label>
                            <textarea wire:model="deletionReason" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                      placeholder="يرجى توضيح سبب طلب الحذف..."></textarea>
                            @error('deletionReason')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button wire:click="requestUserDeletion" 
                                class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 mr-3 hover:bg-red-700">
                            تأكيد
                        </button>
                        <button wire:click="showDeletionModal = false" 
                                class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400">
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if(session()->has('message'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif
</div>
