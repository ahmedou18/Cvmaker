<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        // نقوم بإفراغ الجدول أولاً لتجنب التكرار إذا قمت بتشغيل السيدر مرة أخرى
        DB::table('templates')->truncate();

        DB::table('templates')->insert([
            [
                'name' => 'Green Classic',
                'slug' => 'green-classic',
                'thumbnail' => 'assets/images/templates/green-classic.png',
                'view_path' => 'templates.green-classic', // يشير إلى resources/views/templates/green-classic.blade.php
                'is_premium' => false,
            ],
            [
                'name' => 'Minimalist',
                'slug' => 'minimalist',
                'thumbnail' => 'assets/images/templates/minimalist.png',
                'view_path' => 'templates.minimalist', // يشير إلى resources/views/templates/minimalist.blade.php
                'is_premium' => false,
            ],
            [
                'name' => 'Modern Sidebar',
                'slug' => 'modern-sidebar',
                'thumbnail' => 'assets/images/templates/modern-sidebar.png',
                'view_path' => 'templates.modern_sidebar', 
                'is_premium' => false, // جعلتها مدفوعة كمثال، يمكنك تغييرها إلى false
            ]
        ]);
    }
}