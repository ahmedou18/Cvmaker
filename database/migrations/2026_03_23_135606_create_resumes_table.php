<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تشغيل الهجرة: إنشاء جدول السير الذاتية بكافة ميزاته.
     */
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            // المعرفات الأساسية
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->constrained();
            
            // إعدادات السيرة
            $table->string('resume_language')->default('ar'); // لغة السيرة
            $table->uuid('uuid')->unique(); // الرابط الفريد
            
            // نظام حماية تعديل الاسم
            $table->integer('name_changes_left')->default(3); // محاولات التغيير
            $table->boolean('is_name_locked')->default(false); // حالة القفل
            
            // المعلومات الأساسية والحالة
            $table->string('title'); // عنوان السيرة (للمستخدم)
            $table->boolean('is_published')->default(false); // حالة النشر العام
            
            // البيانات التقنية (JSON)
            $table->json('settings')->nullable(); // الألوان والخطوط والترتيب
            $table->json('extra_sections')->nullable(); // الأقسام الإضافية المخصصة
            
            $table->timestamps();
        });
    }

    /**
     * التراجع عن الهجرة.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};