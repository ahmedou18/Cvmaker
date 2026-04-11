<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('templates', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // اسم القالب (مثل: Modern, Classic)
    $table->string('slug')->unique(); // للرابط البرمجي
    $table->string('thumbnail')->nullable(); // صورة مصغرة للقالب
    $table->string('view_path'); // مسار ملف الـ blade (templates.modern)
    $table->boolean('is_premium')->default(false); // للتوسع مستقبلاً (دفع)
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
