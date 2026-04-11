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
        Schema::create('languages', function (Blueprint $table) {
        $table->id();
        // الربط مع جدول السير الذاتية، عند حذف السيرة تُحذف اللغات التابعة لها تلقائياً
        $table->foreignId('resume_id')->constrained()->onDelete('cascade');
        
        $table->string('name'); // اسم اللغة مثل: العربية، الإنجليزية
        $table->string('proficiency')->nullable(); // مستوى الإتقان: مبتدئ، طليق، لغة أم
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
