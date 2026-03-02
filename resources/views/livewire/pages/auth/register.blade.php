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
    public bool $terms = false;
    public ?int $parent_id = null;

    public function mount()
    {
        if (request()->has('ref')) {
            $this->parent_id = (int) request()->query('ref');
        }
    }

    public function togglePromotionPlan(string $option): void
    {
        if (in_array($option, $this->promotion_plan)) {
            $this->promotion_plan = array_diff($this->promotion_plan, [$option]);
        } else {
            $this->promotion_plan[] = $option;
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
            'promotion_plan' => ['required', 'array', 'min:1'],
            'promotion_plan.*' => ['string', 'in:social_media,website,ads,influencers,email,content'],
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

            <!-- Promotion Plan - Multi-select Tags -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-gray-700 mb-2">كيف تخطط للترويج؟ <span class="text-gray-400 font-medium">(يمكنك اختيار أكثر من واحدة)</span></label>

                <div class="flex flex-wrap gap-2 p-4 bg-gray-50 border border-gray-100 rounded-[1.5rem] focus-within:bg-white focus-within:ring-4 focus-within:ring-primary-100 focus-within:border-primary-500 transition-all min-h-[100px]">
                    @php
                    $options = [
                    'social_media' => ['label' => 'منصات التواصل الاجتماعي', 'icon' => '📱'],
                    'website' => ['label' => 'موقع إلكتروني / مدونة', 'icon' => '🌐'],
                    'ads' => ['label' => 'إعلانات مدفوعة', 'icon' => '📣'],
                    'influencers' => ['label' => 'التسويق عبر المؤثرين', 'icon' => '🤝'],
                    'email' => ['label' => 'البريد الإلكتروني', 'icon' => '📧'],
                    'content' => ['label' => 'إنشاء محتوى', 'icon' => '✍️']
                    ];
                    @endphp

                    @foreach($options as $value => $data)
                    <button type="button"
                        wire:click="togglePromotionPlan('{{ $value }}')"
                        class="group relative px-4 py-2.5 rounded-2xl text-xs font-black transition-all duration-300 flex items-center gap-2 border-2 
                            {{ in_array($value, $promotion_plan) 
                                ? 'bg-primary-900 text-white border-primary-900 shadow-lg shadow-primary-900/20 scale-105' 
                                : 'bg-white text-gray-600 border-gray-100 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700' 
                            }}">
                        <span>{{ $data['icon'] }}</span>
                        {{ $data['label'] }}

                        @if(in_array($value, $promotion_plan))
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                        @endif
                    </button>
                    @endforeach
                </div>

                <x-input-error :messages="$errors->get('promotion_plan')" class="mt-1" />
            </div>

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