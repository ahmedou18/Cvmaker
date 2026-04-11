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
    Schema::create('education', function (Blueprint $table) {
        $table->id();
        $table->foreignId('resume_id')->constrained()->onDelete('cascade');
        $table->string('institution')->nullable(); 
        $table->string('degree'); 
        $table->string('field_of_study')->nullable(); 
        $table->string('graduation_year')->nullable(); 
        $table->text('description')->nullable(); // <-- إضافة هذا الحقل مهم جداً للـ AI
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};