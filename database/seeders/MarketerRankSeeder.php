<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MarketerRankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranks = [
            [
                'name' => 'bronze',
                'min_sales_count' => 0,
                'min_revenue' => 0,
                'commission_multiplier' => 1.00,
                'icon' => '🥉',
                'color' => 'bg-orange-100 text-orange-700 border-orange-200',
                'description' => 'الرتبة الافتراضية لكل المسوقين الجدد',
            ],
            [
                'name' => 'silver',
                'min_sales_count' => 10,
                'min_revenue' => 5000,
                'commission_multiplier' => 1.20,
                'icon' => '🥈',
                'color' => 'bg-gray-100 text-gray-700 border-gray-200',
                'description' => 'للشركاء الذين حققوا أداءً جيداً',
            ],
            [
                'name' => 'gold',
                'min_sales_count' => 50,
                'min_revenue' => 20000,
                'commission_multiplier' => 1.50,
                'icon' => '🥇',
                'color' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                'description' => 'للنخبة من شركائنا',
            ],
        ];

        foreach ($ranks as $rank) {
            \App\Models\Rank::firstOrCreate(['name' => $rank['name']], $rank);
        }
    }
}
