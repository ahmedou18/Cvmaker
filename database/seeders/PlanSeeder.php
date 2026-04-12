<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // التعرف على نوع قاعدة البيانات وتغيير أمر الحماية بناءً عليه
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // مسح البيانات القديمة
        Plan::truncate();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $plans = [
            [
                'name' => 'باقة الفرصة الواحدة',
                'slug' => 'basic',
                'price' => 50.00,
                'cv_limit' => 1,
                'ai_credits' => 10,
                'remove_watermark' => false, // العلامة المائية هنا تميزها عن باقة الـ 200
                'has_cover_letter' => false,
                'is_popular' => false,
                'is_active' => true,
                'description' => 'سيرة ذاتية واحدة مع علامة مائية للموقع.'
            ],
            [
                'name' => 'باقة التميز الفردي',
                'slug' => 'premium',
                'price' => 200.00,
                'cv_limit' => 1, // سيرة واحدة أيضاً لكن بمميزات كاملة
                'ai_credits' => 50,
                'remove_watermark' => true, // بدون علامة مائية
                'has_cover_letter' => true, // تشمل رسالة التغطية
                'is_popular' => true,
                'is_active' => true,
                'description' => 'سيرة ذاتية احترافية بدون علامة مائية + رسالة تغطية (Cover Letter).'
            ],
            [
                'name' => 'باقة الخبراء',
                'slug' => 'vip',
                'price' => 500.00,
                'cv_limit' => 5, // خمس سير فقط كما اقترحت
                'ai_credits' => 200,
                'remove_watermark' => true,
                'has_cover_letter' => true,
                'priority_support' => true,
                'is_popular' => false,
                'is_active' => true,
                'description' => '5 سير ذاتية كاملة المميزات. مثالية لمن يتقدم لعدة وظائف أو لعدة لغات.'
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}