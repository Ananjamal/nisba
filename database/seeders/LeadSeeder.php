<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $affiliate = User::where('role', 'affiliate')->first();
        if (!$affiliate) return;

        // Create some leads in various stages
        Lead::factory()->count(10)->create([
            'user_id' => $affiliate->id,
        ]);

        // Create several sold leads with varied renewal dates
        // Some near renewal (within 30 days)
        Lead::factory()->count(5)->state(function (array $attributes) {
            return [
                'status' => Lead::STATUS_SOLD,
                'subscription_renewal_date' => now()->addDays(rand(1, 25)),
            ];
        })->create([
            'user_id' => $affiliate->id,
        ]);

        // Some already expired
        Lead::factory()->count(3)->state(function (array $attributes) {
            return [
                'status' => Lead::STATUS_SOLD,
                'subscription_renewal_date' => now()->subDays(rand(1, 10)),
            ];
        })->create([
            'user_id' => $affiliate->id,
        ]);

        // Some far in the future
        Lead::factory()->count(5)->state(function (array $attributes) {
            return [
                'status' => Lead::STATUS_SOLD,
                'subscription_renewal_date' => now()->addMonths(rand(2, 11)),
            ];
        })->create([
            'user_id' => $affiliate->id,
        ]);
    }
}
