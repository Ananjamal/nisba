<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Setup Roles and Permissions first
        $this->call(RolesAndPermissionsSeeder::class);

        // Admin
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@nisba.me',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        // Affiliate
        $user = User::factory()->create([
            'name' => 'Anan Abu Tawahena',
            'email' => 'anan@example.com',
            'password' => bcrypt('password'),
            'role' => 'affiliate',
            'status' => 'active',
            'phone' => '0599123123',
        ]);
        $user->assignRole('affiliate');

        $user->stats()->create([
            'clicks_count' => 120,
            'active_clients_count' => 5,
            'total_contracts_value' => 25000,
            'pending_commissions' => 1250,
        ]);

        // Platforms
        \App\Models\ReferralLink::create([
            'service_name' => 'قيود',
            'base_url' => 'https://affiliates.qoyod.com/nisba',
            'logo_url' => 'https://nisba.me/assets/img/qoyod.png',
        ]);

        \App\Models\ReferralLink::create([
            'service_name' => 'دفترة',
            'base_url' => 'https://www.daftra.com/?ref_id=2607631',
            'logo_url' => 'https://nisba.me/assets/img/daftra.png',
        ]);

        // Some Leads
        $user->leads()->create([
            'client_name' => 'Phelan Wilcox',
            'company_name' => 'Rose Fowler LLC',
            'city' => 'جدة',
            'client_phone' => '0599123123',
            'status' => 'under_review',
        ]);
    }
}
