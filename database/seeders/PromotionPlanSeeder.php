<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromotionPlan;

class PromotionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'التسويق الرقمي',
                'description' => 'الترويج عبر القنوات الرقمية المختلفة',
                'icon' => '📱',
                'color' => '#3B82F6',
                'sort_order' => 1
            ],
            [
                'name' => 'التسويق عبر وسائل التواصل الاجتماعي',
                'description' => 'استخدام منصات التواصل الاجتماعي للترويج',
                'icon' => '📢',
                'color' => '#10B981',
                'sort_order' => 2
            ],
            [
                'name' => 'التسويق عبر البريد الإلكتروني',
                'description' => 'إرسال حملات بريدية مستهدفة',
                'icon' => '📧',
                'color' => '#F59E0B',
                'sort_order' => 3
            ],
            [
                'name' => 'التسويق بالمحتوى',
                'description' => 'إنشاء محتوى قيم لجذب العملاء',
                'icon' => '📝',
                'color' => '#EF4444',
                'sort_order' => 4
            ],
            [
                'name' => 'التسويق المباشر',
                'description' => 'التواصل المباشر مع العملاء المحتملين',
                'icon' => '🤝',
                'color' => '#8B5CF6',
                'sort_order' => 5
            ],
            [
                'name' => 'التسويق عبر الإعلانات المدفوعة',
                'description' => 'استخدام الإعلانات المدفوعة للوصول للعملاء',
                'icon' => '💰',
                'color' => '#EC4899',
                'sort_order' => 6
            ],
            [
                'name' => 'التسويق عبر المؤتمرات والفعاليات',
                'description' => 'المشاركة في الفعاليات والمعارض',
                'icon' => '🎯',
                'color' => '#14B8A6',
                'sort_order' => 7
            ],
            [
                'name' => 'التسويق عبر الشركاء',
                'description' => 'التعاون مع شركاء للترويج المشترك',
                'icon' => '🤝',
                'color' => '#F97316',
                'sort_order' => 8
            ]
        ];

        foreach ($plans as $plan) {
            PromotionPlan::create($plan);
        }
    }
}
