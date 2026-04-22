<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

    \App\Models\User::updateOrCreate(
        ['email' => 'kmed2498@gmail.com'],
        [
            'name' => 'Ahmedou',
            'password' => bcrypt('Ahmedounaje72021'), // اختر كلمة مرور قوية
            'email_verified_at' => now(),
        ]
    );
        $this->call([
            TemplateSeeder::class, // إضافة القوالب أولاً
            PlanSeeder::class,     // ثم الباقات
        ]);
    }
}