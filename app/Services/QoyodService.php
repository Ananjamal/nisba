<?php

namespace App\Services;

use App\Models\User;
use App\Models\Lead;
use App\Models\UserReferral;
use Illuminate\Support\Facades\Http;

class QoyodService
{
    /**
     * Simulate fetching leads and conversions from Qoyod.
     */
    public function syncLeadsFromQoyod()
    {
        // In a real scenario, we would call:
        // $response = Http::withHeaders(['API-KEY' => '...'])->get('https://api.qoyod.com/v1/customers');

        // Mocking the behavior for demo:
        $mockedData = [
            [
                'name' => 'شركة الحلول المتقدمة',
                'phone' => '0500112233',
                'city' => 'الرياض',
                'nisba_ref' => 'qoyod-2-4f1a', // Example ref id
                'status' => 'invoice_paid',
                'deal_value' => 7500.00
            ],
            [
                'name' => 'مؤسسة النور للتجارة',
                'phone' => '0544556677',
                'city' => 'الدمام',
                'nisba_ref' => 'qoyod-2-a2b3',
                'status' => 'lead',
                'deal_value' => 0
            ]
        ];

        foreach ($mockedData as $item) {
            $referral = UserReferral::where('unique_ref_id', $item['nisba_ref'])->first();

            if ($referral) {
                $statusMap = [
                    'invoice_paid' => 'sold',
                    'lead' => 'under_review'
                ];

                $lead = Lead::updateOrCreate(
                    ['client_phone' => $item['phone'], 'user_id' => $referral->user_id],
                    [
                        'client_name' => $item['name'],
                        'city' => $item['city'],
                        'status' => $statusMap[$item['status']] ?? 'under_review',
                        'is_verified' => $item['status'] === 'invoice_paid', // Verified only if paid
                        'external_system' => 'qoyod',
                        'external_invoice_id' => 'INV-' . rand(1000, 9999),
                        'commission_amount' => $item['deal_value'] * 0.10, // 10% commission
                        'notes' => 'تمت المزامنة آلياً وتحقق الدفع من منصة قيود'
                    ]
                );
            }
        }

        return count($mockedData);
    }
}
