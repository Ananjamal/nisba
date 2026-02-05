<?php

namespace App\Notifications;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WithdrawalStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public WithdrawalRequest $request,
        public string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusText = match ($this->status) {
            'approved', 'completed', 'paid' => 'تمت الموافقة على',
            'rejected' => 'تم رفض',
            default => 'تم تحديث حالة'
        };

        $icon = match ($this->status) {
            'approved', 'completed', 'paid' => 'success',
            'rejected' => 'error',
            default => 'info'
        };

        return [
            'title' => 'تحديث طلب السحب',
            'message' => $statusText . ' طلب السحب بقيمة ' . number_format($this->request->amount, 2) . ' ر.س',
            'withdrawal_request_id' => $this->request->id,
            'amount' => $this->request->amount,
            'status' => $this->status,
            'icon' => $icon,
            'url' => route('dashboard'),
        ];
    }
}
