<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewLeadNotification extends Notification
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'عميل محتمل جديد',
            'message' => 'تم إضافة عميل محتمل جديد: ' . $this->lead->client_name,
            'lead_id' => $this->lead->id,
            'client_name' => $this->lead->client_name,
            'client_phone' => $this->lead->client_phone,
            'icon' => 'lead',
            'url' => route('admin.leads'),
        ];
    }
}
