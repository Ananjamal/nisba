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
     * Handle the Lead "created" event.
     */
    public function created(Lead $lead): void
    {
        // Notify all admin users about new lead
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewLeadNotification($lead));
        }
    }
    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        // Automation: Money only increases if status is 'sold' AND it's verified by the system
        if ($lead->isDirty('is_verified') && $lead->is_verified && $lead->status === 'sold') {

            // Default base commission if not set
            $baseCommission = $lead->commission_amount ?: 500.00;

            // Loop through all marketers assigned to this lead
            foreach ($lead->users as $marketer) {
                // Apply rank multiplier
                $finalAmount = $baseCommission * $marketer->commission_multiplier;

                // Create commission record for this specific marketer
                Commission::updateOrCreate(
                    ['lead_id' => $lead->id, 'user_id' => $marketer->id],
                    [
                        'amount' => $finalAmount,
                        'status' => 'pending',
                    ]
                );

                // Update Marketer Stats
                $stats = $marketer->stats()->firstOrCreate([], [
                    'clicks_count' => 0,
                    'active_clients_count' => 0,
                    'total_contracts_value' => 0,
                    'pending_commissions' => 0
                ]);

                $stats->increment('active_clients_count');
                $stats->increment('total_contracts_value', $finalAmount * 10); // Assume 10x is contract value
                $stats->increment('pending_commissions', $finalAmount);

                // Multi-tier Logic: If the marketer has a parent, give them a percentage (e.g. 5%)
                if ($marketer->parent) {
                    $parent = $marketer->parent;
                    $parentBonus = $finalAmount * 0.05; // 5% bonus for the recruiter

                    Commission::create([
                        'lead_id' => $lead->id,
                        'user_id' => $parent->id,
                        'amount' => $parentBonus,
                        'status' => 'pending',
                    ]);

                    $parentStats = $parent->stats()->firstOrCreate([], [
                        'clicks_count' => 0,
                        'active_clients_count' => 0,
                        'total_contracts_value' => 0,
                        'pending_commissions' => 0
                    ]);
                    $parentStats->increment('pending_commissions', $parentBonus);
                }

                // Notify Marketer via WhatsApp (Simulated)
                if ($marketer->phone) {
                    $this->whatsapp->sendMessage(
                        $marketer->phone,
                        "مبروك! تم تأكيد مبيعة جديدة للعميل {$lead->client_name}. عمولتك (بعد حساب الرتبة): {$finalAmount} ر.س. تفقد لوحة التحكم الخاصة بك."
                    );
                }
            }
        }
    }
}
