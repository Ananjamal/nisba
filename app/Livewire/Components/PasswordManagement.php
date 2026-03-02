<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class PasswordManagement extends Component
{
    public $currentPassword;
    public $newPassword;
    public $confirmPassword;
    public $email;
    public $showPasswordForm = false;
    public $showResetForm = false;

    protected $rules = [
        'currentPassword' => 'required|string',
        'newPassword' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        'email' => 'required|email|exists:users,email',
    ];

    protected $messages = [
        'currentPassword.required' => 'كلمة المرور الحالية مطلوبة',
        'newPassword.required' => 'كلمة المرور الجديدة مطلوبة',
        'newPassword.min' => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل',
        'newPassword.confirmed' => 'تأكيد كلمة المرور لا يتطابق',
        'newPassword.regex' => 'كلمة المرور يجب أن تحتوي على حرف كبير، حرف صغير، رقم، ورمز خاص',
        'email.required' => 'البريد الإلكتروني مطلوب',
        'email.email' => 'البريد الإلكتروني غير صحيح',
        'email.exists' => 'هذا البريد الإلكتروني غير مسجل',
    ];

    public function updatePassword()
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'كلمة المرور الحالية غير صحيحة');
            return;
        }

        // Update user password using the proper Laravel approach
        $user->update([
            'password' => Hash::make($this->newPassword),
        ]);

        // Log activity if the method exists
        if (method_exists($user, 'logActivity')) {
            $user->logActivity('تم تغيير كلمة المرور');
        }

        $this->reset(['currentPassword', 'newPassword', 'confirmPassword', 'showPasswordForm']);
        $this->dispatch('show-message', 'تم تغيير كلمة المرور بنجاح');
    }

    public function sendResetLink()
    {
        $this->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->reset(['email', 'showResetForm']);
            $this->dispatch('show-message', 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني');
        } else {
            $this->addError('email', 'حدث خطأ أثناء إرسال رابط إعادة التعيين');
        }
    }

    public function adminResetPassword($userId, $sendEmail = false)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (!method_exists($currentUser, 'isAdmin') || !$currentUser->isAdmin()) {
            $this->dispatch('show-message', 'غير مصرح لك بهذا الإجراء', 'error');
            return;
        }

        $user = User::findOrFail($userId);

        // Generate secure temporary password
        $newPassword = $this->generateSecurePassword();
        $passwordExpiry = now()->addHours(24);

        $user->update([
            'password' => Hash::make($newPassword),
            'password_changed_at' => now(),
            'password_expiry' => $passwordExpiry,
            'must_change_password' => true,
        ]);

        if (method_exists($user, 'logActivity')) {
            $user->logActivity('تم إعادة تعيين كلمة المرور بواسطة الإدارة');
        }

        // Log admin action
        if (method_exists($currentUser, 'logActivity')) {
            $currentUser->logActivity("قام بإعادة تعيين كلمة المرور للمستخدم: {$user->name} ({$user->email})");
        }

        if ($sendEmail && $user->email) {
            $this->sendPasswordResetEmail($user, $newPassword);
            $this->dispatch('show-message', 'تم إعادة تعيين كلمة المرور وإرسالها عبر البريد الإلكتروني');
        } else {
            $this->dispatch('show-message', "تم إعادة تعيين كلمة المرور: {$newPassword}");
        }
    }

    public function adminForcePasswordChange($userId)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (!method_exists($currentUser, 'isAdmin') || !$currentUser->isAdmin()) {
            $this->dispatch('show-message', 'غير مصرح لك بهذا الإجراء', 'error');
            return;
        }

        $user = User::findOrFail($userId);

        $user->update([
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        if (method_exists($user, 'logActivity')) {
            $user->logActivity('تم فرض تغيير كلمة المرور بواسطة الإدارة');
        }

        $this->dispatch('show-message', 'تم فرض تغيير كلمة المرور على المستخدم');
    }

    private function generateSecurePassword()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, 12);
    }

    private function sendPasswordResetEmail($user, $password)
    {
        // This would typically use Laravel's notification system
        // For now, we'll just log it
        \Log::info("Password reset email would be sent to {$user->email} with password: {$password}");
    }

    public function checkPasswordStrength($password)
    {
        $strength = 0;

        // Length check
        if (strlen($password) >= 8) $strength++;
        if (strlen($password) >= 12) $strength++;

        // Character variety checks
        if (preg_match('/[a-z]/', $password)) $strength++;
        if (preg_match('/[A-Z]/', $password)) $strength++;
        if (preg_match('/[0-9]/', $password)) $strength++;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength++;

        return $strength;
    }

    public function render()
    {
        $layout = request()->routeIs('admin.*') ? 'layouts.admin' : 'layouts.app';
        return view('livewire.components.password-management')->layout($layout);
    }
}
