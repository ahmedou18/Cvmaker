<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('plan_id')->constrained(); // لربط الدفع بالباقة
        
        // معرف العملية الخاص بموقعك (الذي سترسله لموسيل)
        $table->string('transaction_reference')->unique(); 
        
        // معرف العملية الذي سيعود من موسيل بعد النجاح (اختياري للتوثيق)
        $table->string('moosyl_transaction_id')->nullable(); 
        
        $table->decimal('amount', 10, 2);
        $table->string('currency')->default('MRU');
        
        // حالة الدفع: pending, completed, failed
        $table->string('status')->default('pending'); 
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
