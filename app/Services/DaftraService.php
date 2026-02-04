<?php

namespace App\Services;

use App\Models\User;
use App\Models\Lead;
use App\Models\UserReferral;
use Illuminate\Support\Facades\Http;

class DaftraService
{
    /**
     * Simulate fetching leads and conversions from Daftra.
     */
    public function syncLeadsFromDaftra()
    {
        // Mocking the behavior for demo:
        $mockedData = [
            [
                'name' => 'متجر السحاب الإلكتروني',
                'phone' => '0566889900',
                'city' => 'جدة',
                'nisba_ref' => 'daftra-2-b5c6', // Example ref id
                'status' => 'completed',
                'deal_value' => 12000.00
            ],
            [
                'name' => 'مطاعم الضيافة السعيدة',
                'phone' => '0577889911',
                'city' => 'مكة',
                'nisba_ref' => 'daftra-2-e8f9',
                'status' => 'pending',
                'deal_value' => 0
            ]
        ];

        foreach ($mockedData as $item) {
            $referral = UserReferral::where('unique_ref_id', $item['nisba_ref'])->first();

            if ($referral) {
                $statusMap = [
                    'completed' => 'sold',
                    'pending' => 'contacting'
                ];

                $lead = Lead::updateOrCreate(
                    ['client_phone' => $item['phone'], 'user_id' => $referral->user_id],
                    [
                        'client_name' => $item['name'],
                        'city' => $item['city'],
                        'status' => $statusMap[$item['status']] ?? 'under_review',
                        'is_verified' => $item['status'] === 'completed', // Verified only if completed
                        'external_system' => 'daftra',
                        'external_invoice_id' => 'DFT-' . rand(1000, 9999),
                        'commission_amount' => $item['deal_value'] * 0.15, // 15% commission
                        'notes' => 'تمت المزامنة آلياً وتحقق الدفع من منصة دفترة'
                    ]
                );
            }
        }

        return count($mockedData);
    }
}
