<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'نظام إدارة علاقات العملاء (CRM)', 'color' => '#3B82F6', 'sort_order' => 1],
            ['name' => 'نظام تخطيط موارد المؤسسات (ERP)', 'color' => '#10B981', 'sort_order' => 2],
            ['name' => 'نظام إدارة المشاريع', 'color' => '#F59E0B', 'sort_order' => 3],
            ['name' => 'نظام إدارة الموارد البشرية', 'color' => '#EF4444', 'sort_order' => 4],
            ['name' => 'نظام إدارة المخزون', 'color' => '#8B5CF6', 'sort_order' => 5],
            ['name' => 'نظام إدارة المبيعات', 'color' => '#EC4899', 'sort_order' => 6],
            ['name' => 'نظام المحاسبة والمالية', 'color' => '#14B8A6', 'sort_order' => 7],
            ['name' => 'نظام إدارة سلسلة الإمداد', 'color' => '#F97316', 'sort_order' => 8],
            ['name' => 'نظام إدارة الجودة', 'color' => '#06B6D4', 'sort_order' => 9],
            ['name' => 'نظام إدارة المعرفة', 'color' => '#84CC16', 'sort_order' => 10],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
