<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $subscriptionRenewal;

    public function __construct($subscriptionRenewal)
    {
        $this->subscriptionRenewal = $subscriptionRenewal;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $daysUntilRenewal = $this->subscriptionRenewal->getDaysUntilRenewal();
        $lead = $this->subscriptionRenewal->lead;

        return (new MailMessage)
            ->subject('تنبيه تجديد الاشتراك - ' . $lead->client_name)
            ->markdown('emails.subscription-renewal', [
                'user' => $notifiable,
                'subscriptionRenewal' => $this->subscriptionRenewal,
                'lead' => $lead,
                'daysUntilRenewal' => $daysUntilRenewal,
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'تنبيه تجديد الاشتراك',
            'message' => 'الاشتراك للعميل ' . $this->subscriptionRenewal->lead->client_name . ' يحتاج للتجديد خلال ' . $this->subscriptionRenewal->getDaysUntilRenewal() . ' يوم',
            'lead_id' => $this->subscriptionRenewal->lead_id,
            'renewal_id' => $this->subscriptionRenewal->id,
            'renewal_date' => $this->subscriptionRenewal->renewal_date->format('Y-m-d'),
        ];
    }
}
