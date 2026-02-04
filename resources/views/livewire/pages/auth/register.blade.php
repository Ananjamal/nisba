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

<div class="space-y-8">
    <div class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-gray-200/50 border border-gray-100">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-black text-blue-900 mb-2">انضم كشريك</h1>
            <p class="text-gray-400 font-bold text-sm italic">املأ النموذج للتقديم على برنامج الشراكة</p>
        </div>

        <form wire:submit="register" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">الاسم الكامل</label>
                    <input wire:model="name" id="name" type="text" name="name" required autofocus
                        class="w-full px-5 py-4 bg-[#f8fafc] border-none rounded-2xl text-gray-700 font-bold focus:ring-2 focus:ring-yellow-400/20 transition placeholder:text-gray-300"
                        placeholder="أحمد محمد">
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">رقم الهاتف</label>
                    <input wire:model="phone" id="phone" type="text" name="phone" required
                        class="w-full px-5 py-4 bg-[#f8fafc] border-none rounded-2xl text-gray-700 font-bold focus:ring-2 focus:ring-yellow-400/20 transition placeholder:text-gray-300"
                        placeholder="+966 5X XXX XXXX">
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-2">البريد الإلكتروني</label>
                <input wire:model="email" id="email" type="email" name="email" required
                    class="w-full px-5 py-4 bg-[#f8fafc] border-none rounded-2xl text-gray-700 font-bold focus:ring-2 focus:ring-yellow-400/20 transition placeholder:text-gray-300"
                    placeholder="your@email.com">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">كلمة المرور</label>
                    <div class="relative">
                        <input wire:model="password" id="password" type="password" name="password" required
                            class="w-full px-5 py-4 bg-[#f8fafc] border-none rounded-2xl text-gray-700 font-bold focus:ring-2 focus:ring-yellow-400/20 transition placeholder:text-gray-300"
                            placeholder="••••••••">
                        <div class="absolute inset-y-0 left-4 flex items-center text-gray-400 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">تأكيد كلمة المرور</label>
                    <div class="relative">
                        <input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required
                            class="w-full px-5 py-4 bg-[#f8fafc] border-none rounded-2xl text-gray-700 font-bold focus:ring-2 focus:ring-yellow-400/20 transition placeholder:text-gray-300"
                            placeholder="••••••••">
                        <div class="absolute inset-y-0 left-4 flex items-center text-gray-400 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 col-span-2" />
            </div>

            <!-- Promotion Plan -->
            <div>
                <label for="promotion_plan" class="block text-sm font-bold text-gray-700 mb-2">كيف تخطط للترويج؟</label>
                <textarea wire:model="promotion_plan" id="promotion_plan" rows="4"
                    class="w-full px-5 py-4 bg-[#f8fafc] border-none rounded-3xl text-gray-700 font-bold focus:ring-2 focus:ring-yellow-400/20 transition placeholder:text-gray-300 text-sm"
                    placeholder="أخبرنا عن خطتك التسويقية (مثال: وسائل التواصل الاجتماعي، موقع إلكتروني، إلخ)"></textarea>
                <x-input-error :messages="$errors->get('promotion_plan')" class="mt-2" />
            </div>

            <!-- Commission Terms -->
            <div class="bg-[#f0f9ff] p-6 rounded-[2rem] border border-[#e0f2fe] space-y-3">
                <div class="flex items-center gap-2 text-[#0369a1]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-black italic">شروط استحقاق العمولة:</span>
                </div>
                <p class="text-xs font-bold text-[#0c4a6e] leading-relaxed">لتضمن عمولتك، يجب تحقيق الشروط التالية:</p>
                <ul class="text-[11px] font-bold text-gray-500 space-y-2 list-disc list-inside ms-2">
                    <li>أن يسدد العميل كامل المبلغ</li>
                    <li>مرور 15 يوماً من تاريخ اشتراك العميل أو تم تركيب النظام للعميل</li>
                    <li>ألا يكون العميل قد تواصل مع شركتنا مسبقاً قبل إحالتك له</li>
                </ul>
            </div>

            <!-- Terms Condition -->
            <div class="flex items-center gap-3 px-2">
                <input wire:model="terms" type="checkbox" id="terms" class="w-5 h-5 rounded-lg border-gray-200 text-[#0061ff] focus:ring-[#0061ff]/10">
                <label for="terms" class="text-sm font-bold text-blue-400">
                    أوافق على <a href="#" class="text-blue-900 underline">الشروط والأحكام</a> وسياسة الشركة المتبعة في برنامج الشراكة
                </label>
                <x-input-error :messages="$errors->get('terms')" class="mt-2 block" />
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full py-5 bg-blue-900 text-white rounded-2xl font-black text-xl shadow-xl shadow-blue-100 hover:bg-blue-800 transition">
                    تقديم الطلب
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm font-bold text-gray-400">لدي حساب بالفعل؟ <a href="{{ route('login') }}" class="text-[#0061ff]">تسجيل الدخول</a></p>
            </div>
        </form>
    </div>
</div>