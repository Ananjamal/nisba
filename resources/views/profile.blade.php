<x-app-layout>
    <div class="space-y-6">
        <!-- Account Info Card -->
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="section-title">معلومات الحساب</h3>
                <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                </svg>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div>
                        <p class="text-xs text-secondary mb-1">حالة الحساب</p>
                        <span class="badge badge-active">نشط</span>
                    </div>
                    <div>
                        <p class="text-xs text-secondary mb-1">نسبة العمولة</p>
                        <p class="text-xl font-bold text-primary-900">20%</p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary mb-1">تاريخ التسجيل</p>
                        <p class="text-sm font-semibold text-primary-900">{{ auth()->user()->created_at->format('Y/m/d') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Data Form -->
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <h3 class="section-title">بيانات الحساب</h3>
                </div>
                <p class="section-subtitle mt-1">يمكنك تعديل بياناتك مباشرة</p>
            </div>
            <div class="card-body">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <!-- Bank Account Form -->
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <h3 class="section-title">المعلومات البنكية</h3>
                </div>
            </div>
            <div class="card-body">
                <livewire:profile.update-bank-account-form />
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="card">
            <div class="card-header flex items-center gap-2">
                <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <h3 class="section-title">إعدادات الإشعارات</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="flex items-center justify-between p-4 bg-bg-main rounded-lg">
                    <div>
                        <p class="font-semibold text-primary-900">الرسائل</p>
                        <p class="text-xs text-secondary">تلقي إشعارات عند وصول رسائل جديدة</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-900"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between p-4 bg-bg-main rounded-lg">
                    <div>
                        <p class="font-semibold text-primary-900">حالة العملاء</p>
                        <p class="text-xs text-secondary">تلقي إشعارات عند تغيير حالة العملاء</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-900"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between p-4 bg-bg-main rounded-lg">
                    <div>
                        <p class="font-semibold text-primary-900">طلبات الصرف</p>
                        <p class="text-xs text-secondary">تلقي إشعارات متعلقة بطلبات الصرف</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-900"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Security -->
        <div class="card">
            <div class="card-header">
                <h3 class="section-title">الأمان وكلمة المرور</h3>
            </div>
            <div class="card-body">
                <livewire:profile.update-password-form />
            </div>
        </div>
    </div>
</x-app-layout>