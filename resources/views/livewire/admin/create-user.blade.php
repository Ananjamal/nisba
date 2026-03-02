<div class="p-8">
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-50">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight">إضافة مستخدم جديد</h3>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">إنشاء حساب إداري أو مسوق جديد</p>
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
            <!-- Password -->
            <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block px-1">كلمة المرور</label>
                <input type="password" wire:model="password"
                    class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-bold text-gray-700 placeholder:text-gray-300"
                    placeholder="••••••••">
                @error('password') <span class="text-rose-500 text-[10px] font-black mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block px-1">تأكيد كلمة المرور</label>
                <input type="password" wire:model="password_confirmation"
                    class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-bold text-gray-700 placeholder:text-gray-300"
                    placeholder="••••••••">
            </div>
        </div>

        <div class="pt-6">
            <button type="submit"
                class="w-full py-4 bg-primary-600 text-white text-sm font-black rounded-2xl hover:bg-primary-700 transition-all shadow-lg shadow-primary-200 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span>إنشاء الحساب الآن</span>
            </button>
        </div>
    </form>
</div>