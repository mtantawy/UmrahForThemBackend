<?php

use Illuminate\Database\Seeder;

use App\DeathCause;

class DeathCausesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $causes = [
            'طبيعي',
            'مرض السرطان',
            'جلطة',
            'مرض الكبد',
            'سكتة قلبية',
            'أزمة قلبية',
            'فشل كلوي',
            'غيبوبة سكر',
            'نزيف في المخ',
            'فيرس سي',
        ];

        foreach ($causes as $cause) {
            DeathCause::Create([
                'name' => $cause
            ]);
        };
    }
}
