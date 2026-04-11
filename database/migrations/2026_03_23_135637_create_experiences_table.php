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
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->onDelete('cascade');
            
            // الحقول الاختيارية
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->date('start_date')->nullable();
            
            // الحقول الخاصة بتاريخ الانتهاء والعمل الحالي
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        }); // <- المشكلة غالباً كانت في نسيان هذه الفاصلة المنقوطة
    } // <- أو نسيان هذا القوس الذي يغلق دالة up

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};