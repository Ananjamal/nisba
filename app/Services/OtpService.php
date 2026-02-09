<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OtpService
{
    /**
     * Generate and send OTP to the user.
     *
     * @param User $user
     * @return void
     */
    public function sendOtp(User $user)
    {
        $throttleKey = 'otp-send:' . $user->id;

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'form.otp' => "يرجى الانتظار {$seconds} ثانية قبل محاولة إرسال الرمز مرة أخرى.",
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // Save to user
        $user->forceFill([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        // Send OTP via Email (or SMS later)
        Log::info("OTP for user {$user->email} / {$user->phone}: {$otp}");

        // TODO: Implement actual Email/SMS sending
    }

    /**
     * Verify the provided OTP.
     *
     * @param User $user
     * @param string $otp
     * @return bool
     */
    public function verifyOtp(User $user, string $otp): bool
    {
        if (! $user->otp_code || ! $user->otp_expires_at) {
            return false;
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return false;
        }

        if ($user->otp_code !== $otp) {
            return false;
        }

        // Clear OTP after successful verification
        $user->forceFill([
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        return true;
    }
}
