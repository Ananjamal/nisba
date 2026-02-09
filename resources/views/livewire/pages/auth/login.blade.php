<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public bool $otpRequired = false;
    public int $resendCountdown = 0;
    public string $loginType = '';

    /**
     * Handle an incoming authentication request.
     */
    public function login(\App\Services\OtpService $otpService): void
    {
        if ($this->otpRequired) {
            $this->validate([
                'form.otp' => 'required|numeric|digits:6',
            ]);

            $user = \App\Models\User::where($this->loginType, $this->form->login)->first();

            if (! $user || ! $otpService->verifyOtp($user, $this->form->otp)) {
                $this->addError('form.otp', 'رمز التحقق غير صحيح أو منتهي الصلاحية');
                return;
            }

            $this->completeLogin($user);
        } else {
            $this->validate();

            $user = $this->form->validateCredentials();
            $this->loginType = filter_var($this->form->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            if ($this->loginType === 'email') {
                $this->completeLogin($user);
            } else {
                $otpService->sendOtp($user);
                $this->otpRequired = true;
                $this->resendCountdown = 60;
                $this->js("setTimeout(() => document.getElementById('otp').focus(), 100)");
            }
        }
    }

    public function resendOtp(\App\Services\OtpService $otpService): void
    {
        if ($this->resendCountdown > 0) return;

        $user = \App\Models\User::where($this->loginType, $this->form->login)->first();
        if ($user) {
            $otpService->sendOtp($user);
            $this->resendCountdown = 60;
            $this->dispatch('toast', type: 'success', message: 'تم إعادة إرسال رمز التحقق');
        }
    }

    private function completeLogin($user): void
    {
        Auth::login($user, $this->form->remember);
        Session::regenerate();

        if ($user->hasRole('admin')) {
            $this->redirect(route('admin.dashboard', absolute: false), navigate: true);
        } else {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        }
    }
}; ?>

<div class="w-full max-w-md mx-auto relative">
    <!-- Background Decor -->
    <div class="absolute -top-20 -right-20 w-64 h-64 bg-primary-500/20 rounded-full blur-[80px] animate-pulse"></div>
    <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-yellow-400/20 rounded-full blur-[80px] animate-pulse delay-1000"></div>

    <div class="bg-white/90 backdrop-blur-xl p-10 rounded-[2.5rem] shadow-2xl border border-white/50 relative z-10">
        <div class="text-center mb-10">
            <x-application-logo class="justify-center mb-6 text-primary-900" />
            <h1 class="text-3xl font-black text-primary-900 mb-2">مرحباً بعودتك! 👋</h1>
            <p class="text-gray-500 font-medium">أدخل بياناتك للدخول إلى لوحة التحكم</p>
        </div>

        <form wire:submit="login" class="space-y-6">
            @if ($otpRequired)
            <div class="animate-fade-in-up">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-primary-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">أدخل رمز التحقق</h3>
                    <p class="text-sm text-gray-500">تم إرسال رمز التحقق إلى {{ $form->login }}</p>
                </div>

                <div>
                    <label for="otp" class="block text-sm font-bold text-gray-700 mb-2">رمز التحقق (OTP)</label>
                    <div class="relative group">
                        <input wire:model="form.otp" id="otp" type="text" name="otp" required autofocus
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold text-center tracking-widest text-2xl focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-300"
                            placeholder="******" maxlength="6">
                    </div>
                    <x-input-error :messages="$errors->get('form.otp')" class="mt-2" />
                </div>

                <div class="text-center mt-6 flex flex-col gap-3">
                    <div x-data="{ countdown: @entangle('resendCountdown') }" x-init="setInterval(() => { if(countdown > 0) countdown-- }, 1000)">
                        <button type="button"
                            wire:click="resendOtp"
                            x-bind:disabled="countdown > 0"
                            class="text-sm font-bold text-primary-600 hover:text-primary-800 disabled:text-gray-400 disabled:cursor-not-allowed transition">
                            <span x-show="countdown == 0">إعادة إرسال الرمز</span>
                            <span x-show="countdown > 0">إعادة الإرسال خلال <span x-text="countdown"></span> ثانية</span>
                        </button>
                    </div>
                    <button type="button" wire:click="$set('otpRequired', false)" class="text-sm text-gray-500 hover:text-primary-600 underline">
                        العودة لتسجيل الدخول
                    </button>
                </div>
            </div>
            @else
            <div>
                <label for="login" class="block text-sm font-bold text-gray-700 mb-2">البريد الإلكتروني أو رقم الهاتف</label>
                <div class="relative group">
                    <input wire:model="form.login" id="login" type="text" name="login" required autofocus
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                        placeholder="example@haleef.com او 05xxxxxxxx">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                        </svg>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('form.login')" class="mt-2" />
            </div>

            <div>
                <div class="flex justify-between mb-2">
                    <label for="password" class="block text-sm font-bold text-gray-700">كلمة المرور</label>
                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-primary-600 hover:text-primary-800 transition">نسيت كلمة المرور؟</a>
                </div>
                <div class="relative group">
                    <input wire:model="form.password" id="password" type="password" name="password" required
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:bg-white focus:ring-4 focus:ring-primary-100 focus:border-primary-500 transition-all placeholder:text-gray-400"
                        placeholder="••••••••">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            </div>
            @endif

            <div class="flex items-center">
                <label for="remember" class="inline-flex items-center">
                    <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                    <span class="ms-2 text-sm text-gray-600 font-medium">{{ __('تذكرني') }}</span>
                </label>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-4 bg-primary-900 text-white rounded-2xl font-black text-lg shadow-xl shadow-primary-900/20 hover:bg-primary-800 hover:-translate-y-1 transition-all duration-300">
                    تسجيل الدخول
                </button>
            </div>

            <div class="text-center pt-6 border-t border-gray-100">
                <p class="text-sm font-medium text-gray-500">
                    ليس لديك حساب؟
                    <a href="{{ route('register') }}" class="font-bold text-primary-600 hover:text-primary-800 hover:underline transition">أنشئ حساب شريك جديد</a>
                </p>
            </div>
        </form>
    </div>
</div>