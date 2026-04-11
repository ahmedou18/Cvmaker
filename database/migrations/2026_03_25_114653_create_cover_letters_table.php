<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cover_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('target_job_title')->nullable(); // المسمى الوظيفي المستهدف
            $table->string('company_name')->nullable(); // اسم الشركة
            $table->text('content'); // نص الرسالة المُولد بالذكاء الاصطناعي
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cover_letters');
    }
};