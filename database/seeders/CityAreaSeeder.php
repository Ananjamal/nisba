<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Area;

class CityAreaSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            [
                'name' => 'الرياض',
                'areas' => ['الملك فهد', 'الملك عبدالله', 'النخيل', 'الياسمين', 'الغدير', 'العارض', 'السلي', 'عريض', 'درعة', 'حطين']
            ],
            [
                'name' => 'جدة',
                'areas' => ['البلد', 'الروضة', 'الشرفية', 'النزهة', 'السلامة', 'المحمدية', 'الرويس', 'البحرة', 'الشاطئ', 'المرجان']
            ],
            [
                'name' => 'مكة المكرمة',
                'areas' => ['الشوقية', 'المسفلة', 'العزيزية', 'الشرائع', 'الجندول', 'الراشدية', 'الهجرة', 'المعابدة', 'العمرة', 'اجياد']
            ],
            [
                'name' => 'المدينة المنورة',
                'areas' => ['المنطقة المركزية', 'العنبرية', 'القبلان', 'الحرة', 'بانقابل', 'وادي الفجر', 'السوق', 'الخالدية', 'الفيصلية', 'الروضة']
            ],
            [
                'name' => 'الدمام',
                'areas' => ['الشاطئ', 'المنطقة الصناعية', 'الخالدية', 'الروضة', 'النخيل', 'العزيزية', 'الفيصلية', 'المحمدية', 'الريان', 'الظهران']
            ]
        ];

        foreach ($cities as $cityData) {
            $city = City::create(['name' => $cityData['name']]);
            
            foreach ($cityData['areas'] as $areaName) {
                Area::create([
                    'city_id' => $city->id,
                    'name' => $areaName
                ]);
            }
        }
    }
}
