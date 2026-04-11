<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TemplateSeeder::class, // إضافة القوالب أولاً
            PlanSeeder::class,     // ثم الباقات
        ]);
    }
}