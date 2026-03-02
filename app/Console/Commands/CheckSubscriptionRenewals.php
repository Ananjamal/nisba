<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\SubscriptionRenewal;
use Carbon\Carbon;

class CheckSubscriptionRenewals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for leads with upcoming subscription renewals and create renewal records/notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting subscription renewal check...');

        // Find leads whose subscription expires in the next 30 days
        // and don't already have a pending renewal record
        $leads = Lead::whereNotNull('subscription_renewal_date')
            ->where('subscription_renewal_date', '<=', now()->addDays(30))
            ->where('subscription_status', 'active')
            ->get();

        $count = 0;
        foreach ($leads as $lead) {
            $existingRenewal = SubscriptionRenewal::where('lead_id', $lead->id)
                ->where('status', 'pending')
                ->first();

            if (!$existingRenewal) {
                $renewal = SubscriptionRenewal::create([
                    'lead_id' => $lead->id,
                    'renewal_date' => $lead->subscription_renewal_date,
                    'previous_expiry_date' => $lead->subscription_renewal_date,
                    'renewal_amount' => $lead->subscription_amount,
                    'renewal_type' => 'automatic',
                    'status' => 'pending',
                ]);

                // Send notification to the assigned marketer
                $renewal->sendNotification();

                $this->line("Created renewal record for Lead: {$lead->client_name} (ID: {$lead->id})");
                $count++;
            }
        }

        $this->info("Subscription renewal check completed. {$count} records created.");
    }
}
