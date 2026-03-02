<div class="p-8">
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-50">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight">تعديل بيانات المستخدم</h3>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">تحديث معلومات الحساب والصلاحيات</p>
            </div>
        </div>
        <button @click="$dispatch('close')" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-xl transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block px-1">الاسم الكامل</label>
                <input type="text" wire:model="name"
                    class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-bold text-gray-700 placeholder:text-gray-300"
                    placeholder="أدخل الاسم الكامل">
                @error('name') <span class="text-rose-500 text-[10px] font-black mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block px-1">البريد الإلكتروني</label>
                <input type="email" wire:model="email"
                    class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-bold text-gray-700 placeholder:text-gray-300"
                    placeholder="example@nisba.com">
                @error('email') <span class="text-rose-500 text-[10px] font-black mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Phone -->
            <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block px-1">رقم الهاتف</label>
                <input type="text" wire:model="phone"
                    class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-bold text-gray-700 placeholder:text-gray-300"
                    placeholder="05xxxxxxx">
                @error('phone') <span class="text-rose-500 text-[10px] font-black mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Role -->
            <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block px-1">الدور الوظيفي</label>
                <div class="relative">
                    <select wire:model="role"
                        class="w-full appearance-none px-4 py-3 bg-gray-50 border border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-bold text-gray-700 cursor-pointer">
                        <option value="admin">مدير</option>
                        <option value="affiliate">مسوق</option>
                        <option value="super-admin">مدير عام</option>
                    </select>
                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                @error('role') <span class="text-rose-500 text-[10px] font-black mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Status -->
            <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block px-1">حالة الحساب</label>
                <div class="relative">
                    <select wire:model="statusToken"
                        class="w-full appearance-none px-4 py-3 bg-gray-50 border border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-bold text-gray-700 cursor-pointer">
                        <option value="active">نشط</option>
                        <option value="inactive">خامل</option>
                        <option value="suspended">موقوف</option>
                        <option value="pending">قيد الانتظار</option>
                    </select>
                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                @error('statusToken') <span class="text-rose-500 text-[10px] font-black mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="pt-6">
            <button type="submit"
                class="w-full py-4 bg-primary-600 text-white text-sm font-black rounded-2xl hover:bg-primary-700 transition-all shadow-lg shadow-primary-200 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>حفظ التعديلات</span>
            </button>
        </div>
    </form>
</div>