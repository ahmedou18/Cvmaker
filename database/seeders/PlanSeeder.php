<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
{
    // تعطيل الحماية في PostgreSQL
    if (DB::getDriverName() === 'pgsql') {
        DB::statement('SET CONSTRAINTS ALL DEFERRED;');
    } elseif (DB::getDriverName() === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = OFF;');
    } else {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    }

    Plan::truncate();
        $plans = [
            [
                'name' => 'باقة البداية', 
                'slug' => 'basic',
                'price' => 50.00,
                'duration_in_days' => 30, // شهر
                'cv_limit' => 1,
                'ai_credits' => 10,
                'remove_watermark' => false,
                'has_cover_letter' => false,
                'priority_support' => false,
                'is_popular' => false,
                'is_active' => true,
                'description' => 'سيرة ذاتية واحدة مع علامة مائية للموقع.'
            ],
            [
                'name' => 'باقة الاحترافية', 
                'slug' => 'premium',
                'price' => 200.00,
                'duration_in_days' => 90, // 3 أشهر
                'cv_limit' => 1,
                'ai_credits' => 50,
                'remove_watermark' => true,
                'has_cover_letter' => true,
                'priority_support' => false,
                'is_popular' => true,
                'is_active' => true,
                'description' => 'سيرة ذاتية احترافية بدون علامة مائية + رسالة تغطية (Cover Letter).'
            ],
            [
                'name' => 'باقة النخبة',  
                'slug' => 'vip',
                'price' => 500.00,
                'duration_in_days' => 365, // سنة كاملة
                'cv_limit' => 5,
                'ai_credits' => 200,
                'remove_watermark' => true,
                'has_cover_letter' => true,
                'priority_support' => true,
                'is_popular' => false,
                'is_active' => true,
                'description' => '5 سير ذاتية كاملة المميزات مع دعم أولوية لمدة عام.'
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}