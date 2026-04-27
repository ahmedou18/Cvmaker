<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('job_title')->nullable();
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('references');
    }
};