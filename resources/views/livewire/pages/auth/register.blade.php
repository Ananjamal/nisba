<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $promotion_plan = '';
    public bool $terms = false;
    public ?int $parent_id = null;

    public function mount()
    {
        if (request()->has('ref')) {
            $this->parent_id = (int) request()->query('ref');
        }
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'promotion_plan' => ['nullable', 'string', 'max:1000'],
            'terms' => ['accepted'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'affiliate';
        $validated['status'] = 'active';
        $validated['parent_id'] = $this->parent_id;

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full max-w-2xl mx-auto relative">
    <!-- Background Decor -->
    <div class="absolute -top-20 -left-20 w-72 h-72 bg-primary-500/20 rounded-full blur-[80px] animate-pulse"></div>
    <div class="absolute -bottom-20 -right-20 w-72 h-72 bg-yellow-400/20 rounded-full blur-[80px] animate-pulse delay-1000"></div>

    <div class="bg-white/90 backdrop-blur-xl p-8 md:p-12 rounded-[2.5rem] shadow-2xl border border-white/50 relative z-10">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-black text-primary-900 mb-2">انضم كشريك نجاح 🚀</h1>
            <p class="text-gray-500 font-medium">ابدأ رحلتك في تحقيق العوائد مع برنامج نسبة</p>
        </div>

        <form wire:submit="register" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">الاسم الكامل</label>
                    <input wire:model="name" id="name" type="text" name="name" required autofocus
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                        placeholder="الاسم الثلاثي">
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">رقم الهاتف</label>
                    <input wire:model="phone" id="phone" type="text" name="phone" required
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                        placeholder="05xxxxxxxx">
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-2">البريد الإلكتروني</label>
                <input wire:model="email" id="email" type="email" name="email" required
                    class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                    placeholder="example@nisba.com">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">كلمة المرور</label>
                    <input wire:model="password" id="password" type="password" name="password" required
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                        placeholder="••••••••">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">تأكيد كلمة المرور</label>
                    <input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                        placeholder="••••••••">
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 col-span-2" />
            </div>

            <!-- Promotion Plan -->
            <div>
                <label for="promotion_plan" class="block text-sm font-bold text-gray-700 mb-2">كيف تخطط للترويج؟ <span class="text-gray-400 text-xs font-normal">(اختياري)</span></label>
                <textarea wire:model="promotion_plan" id="promotion_plan" rows="3"
                    class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400 resize-none"
                    placeholder="هل لديك حسابات تواصل؟ موقع إلكتروني؟"></textarea>
                <x-input-error :messages="$errors->get('promotion_plan')" class="mt-2" />
            </div>

            <!-- Commission Terms -->
            <div class="bg-blue-50/50 p-6 rounded-2xl border border-blue-100 space-y-3">
                <div class="flex items-center gap-2 text-primary-700">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-black">شروط استحقاق العمولة:</span>
                </div>
                <ul class="text-xs font-medium text-primary-600 space-y-2 list-disc list-inside marker:text-primary-400">
                    <li>أن يسدد العميل كامل المبلغ للخدمة المشترك بها.</li>
                    <li>مرور 15 يوماً من تاريخ اشتراك العميل (فترة الضمان).</li>
                    <li>أن يكون العميل جديداً ولم يسبق له التواصل معنا.</li>
                </ul>
            </div>

            <!-- Terms Condition -->
            <div class="flex items-start gap-3 px-2">
                <div class="flex items-center h-5">
                    <input wire:model="terms" type="checkbox" id="terms" class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </div>
                <label for="terms" class="text-sm font-medium text-gray-600">
                    أوافق على <a href="#" class="text-primary-600 font-bold hover:underline">الشروط والأحكام</a> وسياسة الخصوصية الخاصة ببرنامج الشركاء.
                </label>
            </div>
            <x-input-error :messages="$errors->get('terms')" class="mt-2 block px-2" />

            <div class="pt-4">
                <button type="submit" class="w-full py-4 bg-primary-900 text-white rounded-2xl font-black text-lg shadow-xl shadow-primary-900/20 hover:bg-primary-800 hover:-translate-y-1 transition-all duration-300">
                    تقديم طلب الانضمام
                </button>
            </div>

            <div class="text-center pt-6 border-t border-gray-100">
                <p class="text-sm font-medium text-gray-500">لدي حساب بالفعل؟ <a href="{{ route('login') }}" class="font-bold text-primary-600 hover:text-primary-800 hover:underline transition">تسجيل الدخول</a></p>
            </div>
        </form>
    </div>
</div>