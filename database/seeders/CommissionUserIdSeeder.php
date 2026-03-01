<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommissionUserIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update commissions table to set user_id based on lead relationship
        DB::statement('
            UPDATE commissions 
            SET user_id = (
                SELECT leads.user_id 
                FROM leads 
                WHERE leads.id = commissions.lead_id
            )
            WHERE user_id IS NULL
        ');
    }
}
