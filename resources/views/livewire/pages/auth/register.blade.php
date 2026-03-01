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
    public array $promotion_plan = [];
    public string $promotion_plan_single = '';
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
            'promotion_plan_single' => ['required', 'string', 'in:social_media,website,ads,influencers,email,content'],
            'terms' => ['accepted'],
        ]);

        // Convert single selection to array for compatibility
        $validated['promotion_plan'] = [$validated['promotion_plan_single']];
        unset($validated['promotion_plan_single']);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'affiliate';
        $validated['status'] = 'active';
        $validated['parent_id'] = $this->parent_id;

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full max-w-xl mx-auto relative">
    <!-- Background Decor -->
    <div class="absolute -top-10 -left-10 w-48 h-48 bg-primary-500/10 rounded-full blur-[60px] animate-pulse"></div>
    <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-yellow-400/10 rounded-full blur-[60px] animate-pulse delay-1000"></div>

    <div class="bg-white/90 backdrop-blur-xl p-8 md:p-10 rounded-[2.5rem] shadow-2xl border border-white/50 relative z-10">
        <div class="text-center mb-8">
            <x-application-logo class="justify-center mb-6 text-primary-900" />
            <h1 class="text-2xl font-black text-primary-900 mb-2">انضم كشريك نجاح 🚀</h1>
            <p class="text-sm text-gray-500 font-medium">ابدأ رحلتك في تحقيق العوائد مع برنامج {{ config('app.name', 'حليف') }}</p>
        </div>

        <form wire:submit="register" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-bold text-gray-700 mb-1.5">الاسم الكامل</label>
                    <input wire:model="name" id="name" type="text" name="name" required autofocus
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                        placeholder="الاسم الثلاثي">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-gray-700 mb-1.5">رقم الهاتف</label>
                    <input wire:model="phone" id="phone" type="text" name="phone" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                        placeholder="05xxxxxxxx">
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold text-gray-700 mb-1.5">البريد الإلكتروني</label>
                <input wire:model="email" id="email" type="email" name="email" required
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                    placeholder="example@email.com">
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold text-gray-700 mb-1.5">كلمة المرور</label>
                    <input wire:model="password" id="password" type="password" name="password" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                        placeholder="••••••••">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-700 mb-1.5">تأكيد كلمة المرور</label>
                    <input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                        placeholder="••••••••">
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1 col-span-2" />
            </div>

            <!-- Promotion Plan - Simple Dropdown with Separate Display -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">كيف تخطط للترويج؟</label>
                
                <!-- Simple Dropdown -->
                <select wire:model="promotion_plan_single" id="promotion_plan_dropdown" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all">
                    <option value="">اختر طريقة الترويج...</option>
                    <option value="social_media">منصات التواصل الاجتماعي</option>
                    <option value="website">موقع إلكتروني / مدونة</option>
                    <option value="ads">إعلانات مدفوعة</option>
                    <option value="influencers">التسويق عبر المؤثرين</option>
                    <option value="email">التسويق عبر البريد الإلكتروني</option>
                    <option value="content">إنشاء محتوى</option>
                </select>

                <!-- Separate Display Area for Selected Item -->
                @if(!empty($promotion_plan_single))
                    <div class="mt-3 p-3 bg-primary-50 border border-primary-100 rounded-lg">
                        <p class="text-xs font-bold text-primary-700 mb-1">طريقة الترويج المختارة:</p>
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800 border border-primary-200">
                                @php
                                    $labels = [
                                        'social_media' => 'منصات التواصل الاجتماعي',
                                        'website' => 'موقع إلكتروني / مدونة',
                                        'ads' => 'إعلانات مدفوعة',
                                        'influencers' => 'التسويق عبر المؤثرين',
                                        'email' => 'التسويق عبر البريد الإلكتروني',
                                        'content' => 'إنشاء محتوى'
                                    ];
                                @endphp
                                {{ $labels[$promotion_plan_single] ?? $promotion_plan_single }}
                            </span>
                            <button type="button" wire:click="$set('promotion_plan_single', '')" class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                <x-input-error :messages="$errors->get('promotion_plan_single')" class="mt-1" />
            </div>

            <!-- Terms Condition -->
            <div class="flex items-start gap-2 px-1">
                <input wire:model="terms" type="checkbox" id="terms" class="w-4 h-4 mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <label for="terms" class="text-xs font-medium text-gray-600 leading-normal">
                    أوافق على <a href="#" class="text-primary-600 font-bold hover:underline">الشروط والأحكام</a> وسياسة الخصوصية.
                </label>
            </div>
            <x-input-error :messages="$errors->get('terms')" class="mt-1 block px-1" />

            <div class="pt-2">
                <button type="submit" class="w-full py-3.5 bg-primary-900 text-white rounded-xl font-black text-base shadow-xl shadow-primary-900/10 hover:bg-primary-800 hover:-translate-y-0.5 transition-all duration-300">
                    تقديم طلب الانضمام
                </button>
            </div>

            <div class="text-center pt-4 border-t border-gray-100">
                <p class="text-xs font-medium text-gray-500">لدي حساب بالفعل؟ <a href="{{ route('login') }}" class="font-bold text-primary-600 hover:text-primary-800 hover:underline transition">تسجيل الدخول</a></p>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioButtons = document.querySelectorAll('input[name="promotion_type"]');
    const checkboxOptions = document.getElementById('checkbox-options');
    const dropdownOption = document.getElementById('dropdown-option');

    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value == 'checkboxes') {
                checkboxOptions.classList.remove('hidden');
                dropdownOption.classList.add('hidden');
            } else {
                checkboxOptions.classList.add('hidden');
                dropdownOption.classList.remove('hidden');
            }
        });
    });

    // Add some interactive animations
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, select');

    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('scale-[1.02]');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('scale-[1.02]');
        });
    });

    // Add floating animation to background elements
    const bgElements = document.querySelectorAll('.absolute');
    bgElements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.5}s`;
    });
});
</script>
@endpush