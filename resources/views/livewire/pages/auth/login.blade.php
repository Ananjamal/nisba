<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="space-y-8">
    <div class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-gray-200/50 border border-gray-100">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-black text-blue-900 mb-2">تسجيل الدخول</h1>
            <p class="text-gray-400 font-bold text-sm italic">أدخل بيانات حسابك للمتابعة</p>
        </div>

        <form wire:submit="login" class="space-y-6">
            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-2">البريد الإلكتروني</label>
                <div class="relative">
                    <input wire:model="form.email" id="email" type="email" name="email" required autofocus
                        class="w-full px-5 py-4 bg-[#f8fafc] border-none rounded-2xl text-gray-700 font-bold focus:ring-2 focus:ring-yellow-400/20 transition placeholder:text-gray-300"
                        placeholder="your@email.com">
                </div>
                <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <div class="flex justify-between mb-2">
                    <label for="password" class="block text-sm font-bold text-gray-700">كلمة المرور</label>
                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-gray-400 hover:text-yellow-500 transition">نسيت كلمة المرور؟</a>
                </div>
                <div class="relative">
                    <input wire:model="form.password" id="password" type="password" name="password" required
                        class="w-full px-5 py-4 bg-[#f8fafc] border-none rounded-2xl text-gray-700 font-bold focus:ring-2 focus:ring-yellow-400/20 transition placeholder:text-gray-300"
                        placeholder="••••••••">
                    <div class="absolute inset-y-0 left-4 flex items-center text-gray-400 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-5 bg-blue-900 text-white rounded-2xl font-black text-xl shadow-xl shadow-blue-100 hover:bg-blue-800 transition">
                    تسجيل الدخول
                </button>
            </div>

            <div class="text-center pt-4">
                <p class="text-sm font-bold text-gray-400">
                    ليس لديك حساب؟
                    <a href="{{ route('register') }}" class="text-[#0061ff] hover:underline">إنشاء حساب</a>
                </p>
            </div>
        </form>
    </div>

    <!-- Bottom Banner -->
    <div class="bg-gradient-to-l from-blue-900 to-blue-800 p-8 rounded-[2.5rem] shadow-xl text-center relative overflow-hidden group">
        <div class="relative z-10 flex flex-col items-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 rounded-xl text-white text-xs font-bold mb-4 backdrop-blur-sm border border-white/10">
                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"></path>
                </svg>
                {{ __('عميل جديد؟') }}
            </div>
            <h2 class="text-2xl font-black text-white mb-2 italic">ابدأ رحلتك الآن وحقق دخلاً إضافياً</h2>
            <p class="text-white/60 font-bold mb-8 text-sm">انضم الآن واحصل على %30 عمولة على كل توصية ناجحة</p>
            <a href="{{ route('register') }}" class="w-full py-4 bg-yellow-400 text-black rounded-2xl font-black text-lg hover:bg-yellow-500 transition shadow-lg shadow-yellow-400/20">
                سجل كشريك نجاح
            </a>
        </div>
        <!-- Decorative elements -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-yellow-400/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-600/10 rounded-full blur-3xl"></div>
    </div>
</div>