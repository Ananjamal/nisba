<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <form wire:submit="updatePassword" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="form-group">
                <label for="update_password_current_password" class="form-label">{{ __('كلمة المرور الحالية') }}</label>
                <input wire:model="current_password" id="update_password_current_password" type="password" class="form-input" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('current_password')" class="text-sm text-red-500 mt-1" />
            </div>

            <div class="form-group">
                <label for="update_password_password" class="form-label">{{ __('كلمة المرور الجديدة') }}</label>
                <input wire:model="password" id="update_password_password" type="password" class="form-input" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="text-sm text-red-500 mt-1" />
            </div>

            <div class="form-group">
                <label for="update_password_password_confirmation" class="form-label">{{ __('تأكيد كلمة المرور') }}</label>
                <input wire:model="password_confirmation" id="update_password_password_confirmation" type="password" class="form-input" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="text-sm text-red-500 mt-1" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="btn btn-primary">
                {{ __('تحديث كلمة المرور') }}
            </button>

            <x-action-message class="text-sm text-green-600" on="password-updated">
                {{ __('تم التحديث بنجاح.') }}
            </x-action-message>
        </div>
    </form>
</section>