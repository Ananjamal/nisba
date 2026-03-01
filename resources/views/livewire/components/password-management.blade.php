<div class="bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">إدارة كلمة المرور</h2>
    
    <!-- User Password Change -->
    <div class="mb-6 p-4 border rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">تغيير كلمة المرور</h3>
        
        @if(!$showPasswordForm)
            <button wire:click="showPasswordForm = true" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                تغيير كلمة المرور
            </button>
        @else
            <form wire:submit.prevent="updatePassword" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور الحالية</label>
                    <input type="password" wire:model="currentPassword" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('currentPassword')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور الجديدة</label>
                    <input type="password" wire:model="newPassword" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('newPassword')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تأكيد كلمة المرور الجديدة</label>
                    <input type="password" wire:model="confirmPassword" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('confirmPassword')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" 
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        تحديث كلمة المرور
                    </button>
                    <button type="button" wire:click="showPasswordForm = false" 
                            class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                        إلغاء
                    </button>
                </div>
            </form>
        @endif
    </div>
    
    <!-- Forgot Password -->
    <div class="mb-6 p-4 border rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">نسيت كلمة المرور</h3>
        
        @if(!$showResetForm)
            <button wire:click="showResetForm = true" 
                    class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition">
                إرسال رابط إعادة التعيين
            </button>
        @else
            <form wire:submit.prevent="sendResetLink" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" wire:model="email" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500"
                           placeholder="أدخل بريدك الإلكتروني">
                    @error('email')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" 
                            class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition">
                        إرسال الرابط
                    </button>
                    <button type="button" wire:click="showResetForm = false" 
                            class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                        إلغاء
                    </button>
                </div>
            </form>
        @endif
    </div>
    
    <!-- Admin Password Reset (Only for Admins) -->
    @if(auth()->user()->isAdmin())
        <div class="p-4 border rounded-lg bg-red-50">
            <h3 class="text-lg font-semibold text-red-900 mb-4">إعادة تعيين كلمة المرور (للمديرين فقط)</h3>
            <p class="text-sm text-red-700 mb-4">
                يمكنك إعادة تعيين كلمة المرور لأي مستخدم مباشرة من لوحة التحكم.
            </p>
            <div class="text-sm text-red-600">
                ملاحظة: استخدم هذه الميزة بحذر، حيث أنها تقوم بإنشاء كلمة مرور مؤقتة.
            </div>
        </div>
    @endif
    
    <!-- Password Requirements -->
    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
        <h4 class="font-semibold text-blue-900 mb-2">متطلبات كلمة المرور:</h4>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>• يجب أن تكون 8 أحرف على الأقل</li>
            <li>• يفضل أن تحتوي على أحرف كبيرة وصغيرة</li>
            <li>• يفضل أن تحتوي على أرقام ورموز</li>
            <li>• تجنب استخدام كلمات مرور سهلة التخمين</li>
        </ul>
    </div>
</div>
