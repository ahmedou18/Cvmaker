<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // مسح البيانات القديمة لتجنب التكرار
        DB::table('templates')->truncate();

        DB::table('templates')->insert([
            [
                'name' => 'Minimalist',
                'slug' => 'minimalist',
                'thumbnail' => 'assets/images/templates/minimalist.png',
                'view_path' => 'templates.minimalist',
                'is_premium' => false,
            ],
            [
                'name' => 'Modern',
                'slug' => 'modern',
                'thumbnail' => 'assets/images/templates/violet.jpg',
                'view_path' => 'templates.modern_sidebar',
                'is_premium' => false,
            ],
            [
                'name' => 'Modern Green',
                'slug' => 'modern-green',
                'thumbnail' => 'assets/images/templates/green.jpg',
                'view_path' => 'templates.modern-green',
                'is_premium' => false,
            ],
            [
                'name' => 'Modern Orange',
                'slug' => 'modern-orange',
                'thumbnail' => 'assets/images/templates/Orange.png', // حرف O كبير كما في الصورة
                'view_path' => 'templates.modern-orange',
                'is_premium' => false,
            ],
            [
                'name' => 'Pikachu Gold',
                'slug' => 'pikachu-gold',
                'thumbnail' => 'assets/images/templates/gold.jpg',
                'view_path' => 'templates.pikachu-gold',
                'is_premium' => false,
            ],
            [
                'name' => 'Ditto Pink',
                'slug' => 'ditto-pink',
                'thumbnail' => 'assets/images/templates/ditto.jpg',
                'view_path' => 'templates.ditto-pink',
                'is_premium' => false,
            ],
            [
                'name' => 'Modern Blue',
                'slug' => 'modern-blue',
                'thumbnail' => 'assets/images/templates/blue.jpg',
                'view_path' => 'templates.modern-blue',
                'is_premium' => false,
            ],
            [
                'name' => 'Modern Minimalist',
                'slug' => 'modern-minimalist',
                'thumbnail' => 'assets/images/templates/simple.png',
                'view_path' => 'templates.modern-minimalist',
                'is_premium' => false,
            ],
        ]);
    }
}