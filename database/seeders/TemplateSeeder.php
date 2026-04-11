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
                'name' => 'Classic',
                'slug' => 'classic',
                'thumbnail' => 'assets/images/templates/classic.png', 
                'view_path' => 'templates.classic', // يشير إلى resources/views/templates/classic.blade.php
                'is_premium' => false,
            ],
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
                'name' => 'Modern',
                'slug' => 'modern',
                'thumbnail' => 'assets/images/templates/modern.png',
                'view_path' => 'templates.modern', // يشير إلى resources/views/templates/modern.blade.php
                'is_premium' => true, // يمكنك تغييره حسب رغبتك إذا كان مجانياً أم مدفوعاً
            ],
            [
                'name' => 'Modern Split',
                'slug' => 'modern-split',
                'thumbnail' => 'assets/images/templates/modern-split.png',
                'view_path' => 'templates.modern_split', // يشير إلى resources/views/templates/modern_split.blade.php
                'is_premium' => true,
            ]
        ]);
    }
}