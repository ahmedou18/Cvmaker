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
                'name' => 'الباقة الاقتصادية',
                'slug' => 'basic',
                'price' => 50.00,
                'duration_in_days' => 30,
                'cv_limit' => 1,
                'ai_credits' => 5,
                'remove_watermark' => false,
                'has_cover_letter' => false,
                'priority_support' => false,
                'is_popular' => false,
                'is_active' => true,
                'description' => 'مثالية لإنشاء سيرة ذاتية سريعة بلمسة ذكاء اصطناعي.'
            ],
            [
                'name' => 'الباقة المتوسطة',
                'slug' => 'standard',
                'price' => 200.00,
                'duration_in_days' => 30,
                'cv_limit' => 3,
                'ai_credits' => 20,
                'remove_watermark' => true,
                'has_cover_letter' => false,
                'priority_support' => false,
                'is_popular' => true,
                'is_active' => true,
                'description' => 'الخيار الأفضل للباحثين عن عمل بشكل جدي.'
            ],
            [
                'name' => 'الباقة الاحترافية',
                'slug' => 'professional',
                'price' => 500.00,
                'duration_in_days' => 90,
                'cv_limit' => 10,
                'ai_credits' => 100,
                'remove_watermark' => true,
                'has_cover_letter' => true,
                'priority_support' => true,
                'is_popular' => false,
                'is_active' => true,
                'description' => 'احترافية تامة مع رصيد ذكاء اصطناعي ضخم ورسائل تحفيز.'
            ],
            [
                'name' => 'باقة المؤسسات (المقاهي)',
                'slug' => 'agency',
                'price' => 1500.00,
                'duration_in_days' => 30,
                'cv_limit' => 100,
                'ai_credits' => 500,
                'remove_watermark' => true,
                'has_cover_letter' => true,
                'priority_support' => true,
                'is_popular' => false,
                'is_active' => true,
                'description' => 'خاصة بأصحاب مقاهي الإنترنت والخدمات الجامعية.'
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}