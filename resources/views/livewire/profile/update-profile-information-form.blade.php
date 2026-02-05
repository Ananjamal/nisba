<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section x-data @profile-updated.window="$dispatch('toast', {message: 'تم تحديث الملف الشخصي بنجاح', type: 'success'})">
    <form wire:submit="updateProfileInformation" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-group">
                <label for="name" class="form-label">{{ __('الاسم') }}</label>
                <input wire:model="name" id="name" type="text" class="form-input" required autofocus autocomplete="name" />
                <x-input-error class="text-sm text-red-500 mt-1" :messages="$errors->get('name')" />
            </div>

            <div class="form-group">
                <label for="email" class="form-label">{{ __('البريد الإلكتروني') }}</label>
                <input wire:model="email" id="email" type="email" class="form-input" required autocomplete="username" />
                <x-input-error class="text-sm text-red-500 mt-1" :messages="$errors->get('email')" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-secondary">
                        {{ __('بريدك الإلكتروني غير مفعل.') }}

                        <button wire:click.prevent="sendVerification" class="text-primary-600 hover:text-primary-800 underline focus:outline-none">
                            {{ __('إضغط هنا لإعادة إرسال رابط التفعيل.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm text-green-600">
                        {{ __('تم إرسال رابط تفعيل جديد إلى بريدك الإلكتروني.') }}
                    </p>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="btn btn-primary">
                {{ __('حفظ التغييرات') }}
            </button>

            <x-action-message class="text-sm text-green-600" on="profile-updated">
                {{ __('تم الحفظ.') }}
            </x-action-message>
        </div>
    </form>
</section>