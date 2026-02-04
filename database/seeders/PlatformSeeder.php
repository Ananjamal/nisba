<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@nisba.me'],
            [
                'name' => 'Admin NISBA',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '0500000000',
            ]
        );

        // Affiliate User
        \App\Models\User::updateOrCreate(
            ['email' => 'affiliate@nisba.me'],
            [
                'name' => 'Anan Affiliate',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'affiliate',
                'status' => 'active',
                'phone' => '0511111111',
            ]
        );

        // Referral Links
        $links = [
            [
                'service_name' => 'Qoyod | قيود',
                'base_url' => 'https://qoyod.com',
                'logo_url' => 'https://www.qoyod.com/wp-content/uploads/2021/05/logo_qoyod.svg',
            ],
            [
                'service_name' => 'Daftra | دفترة',
                'base_url' => 'https://daftra.com',
                'logo_url' => 'https://www.daftra.com/images/daftra-logo.png',
            ],
            [
                'service_name' => 'Foodics | فودكس',
                'base_url' => 'https://foodics.com',
                'logo_url' => 'https://www.foodics.com/wp-content/themes/foodics/assets/images/logo.svg',
            ],
        ];

        foreach ($links as $link) {
            \App\Models\ReferralLink::updateOrCreate(['service_name' => $link['service_name']], $link);
        }
    }
}
