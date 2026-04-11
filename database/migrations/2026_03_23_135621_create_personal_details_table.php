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
        Schema::create('personal_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('resume_id')->constrained()->onDelete('cascade');
    $table->string('full_name');
    $table->string('job_title')->nullable();
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->string('address')->nullable();
    $table->text('summary')->nullable(); // النبذة الشخصية
    $table->string('photo_path')->nullable(); // مسار الصورة الشخصية
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_details');
    }
};
