<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تشغيل الهجرة: إنشاء جداول المستخدمين مع نظام الباقات والرصيد.
     */
    public function up(): void
    {
        // 1. جدول المستخدمين (شامل لكل الحقول)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // --- حقول نظام الباقات والرصيد ---
            // ربط المستخدم بباقة (Nullable لأن المستخدم قد لا يشترك فوراً)
           // $table->foreignId('plan_id')->nullable()->constrained('plans')->onDelete('set null');
            
            // رصيد الذكاء الاصطناعي (تم حل التعارض باستخدام ai_credits_balance)
            $table->integer('ai_credits_balance')->default(3); // 3 رصيد مجاني عند التسجيل
            
            // رصيد رسائل التغطية
            $table->integer('cover_letters_balance')->default(0);
            // ---------------------------------

            $table->rememberToken();
            $table->timestamps();
        });

        // 2. جدول رموز استعادة كلمة المرور
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. جدول الجلسات
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * التراجع عن الهجرة.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
