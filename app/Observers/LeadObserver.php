<?php

namespace App\Observers;

use App\Models\Lead;
use App\Models\Commission;
use App\Services\WhatsAppService;

class LeadObserver
{
    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }
    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        // Automation: Money only increases if status is 'sold' AND it's verified by the system
        if ($lead->isDirty('is_verified') && $lead->is_verified && $lead->status === 'sold') {

            // Calculate commission if not already set (10% default if not fixed)
            if (!$lead->commission_amount) {
                $lead->commission_amount = 500.00; // This should ideally come from the API deal value
                $lead->saveQuietly();
            }

            // Create commission record
            Commission::updateOrCreate(
                ['lead_id' => $lead->id],
                [
                    'amount' => $lead->commission_amount,
                    'status' => 'pending',
                ]
            );

            // Update User Stats
            $stats = $lead->user->stats()->firstOrCreate([], [
                'clicks_count' => 0,
                'active_clients_count' => 0,
                'total_contracts_value' => 0,
                'pending_commissions' => 0
            ]);

            $stats->increment('active_clients_count');
            $stats->increment('total_contracts_value', $lead->commission_amount * 10); // Assume 10x is contract value
            $stats->increment('pending_commissions', $lead->commission_amount);

            // Multi-tier Logic: If the user has a parent, give them a percentage (e.g. 5%)
            if ($lead->user->parent) {
                $parent = $lead->user->parent;
                $parentCommission = $lead->commission_amount * 0.05; // 5% bonus for the recruiter

                Commission::create([
                    'lead_id' => $lead->id,
                    'amount' => $parentCommission,
                    'status' => 'pending',
                ]);

                $parentStats = $parent->stats()->firstOrCreate([], [
                    'clicks_count' => 0,
                    'active_clients_count' => 0,
                    'total_contracts_value' => 0,
                    'pending_commissions' => 0
                ]);
                $parentStats->increment('pending_commissions', $parentCommission);
            }

            // Trigger WhatsApp Notification (Simulated)
            if ($lead->user->phone) {
                $this->whatsapp->sendMessage(
                    $lead->user->phone,
                    "مبروك! تم تأكيد مبيعة جديدة للعميل {$lead->client_name}. عمولتك: {$lead->commission_amount} ر.س. تفقد لوحة التحكم الخاصة بك."
                );
            }
        }
    }
}
