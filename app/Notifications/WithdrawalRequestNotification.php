<?php

namespace App\Notifications;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WithdrawalRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public WithdrawalRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'طلب سحب جديد',
            'message' => 'تم تقديم طلب سحب جديد بقيمة ' . number_format($this->request->amount, 2) . ' ر.س للعميل: ' . ($this->request->client_name ?: 'عام'),
            'withdrawal_request_id' => $this->request->id,
            'amount' => $this->request->amount,
            'user_name' => $this->request->user->name,
            'icon' => 'withdrawal',
            'url' => route('admin.payouts'),
        ];
    }
}
