<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تشغيل الهجرة: إنشاء جدول الباقات بهيكله المطور النهائي.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            
            // البيانات الأساسية
            $table->string('name'); 
            $table->string('slug')->unique(); // الاسم التقني (مثل: basic, pro)
            
            $table->decimal('price', 8, 2); // السعر
            $table->integer('duration_in_days')->default(30); // مدة الباقة بالايام
            
            // حدود الاستخدام والذكاء الاصطناعي
            $table->integer('cv_limit'); // عدد السير المسموح إنشاؤها
            $table->integer('ai_credits')->default(0); // رصيد الذكاء الاصطناعي الممنوح مع الباقة
            
            // المميزات الاحترافية
            $table->boolean('remove_watermark')->default(false); // خيار إزالة العلامة المائية
            $table->boolean('has_cover_letter')->default(false); // ميزة الرسالة التحفيزية
            $table->boolean('priority_support')->default(false); // الدعم الفني السريع
            
            // حالة الباقة والتسويق
            $table->boolean('is_popular')->default(false); // شارة "الأكثر مبيعاً"
            $table->boolean('is_active')->default(true); // هل الباقة متاحة حالياً؟
            $table->text('description')->nullable(); // وصف تفصيلي للمميزات
            
            $table->timestamps();
        });
    }

    /**
     * التراجع عن الهجرة.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};