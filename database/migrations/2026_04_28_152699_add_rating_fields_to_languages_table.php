<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->tinyInteger('level')->nullable()->after('name');
            $table->tinyInteger('percentage')->nullable()->after('level');
        });
    }

    public function down()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn(['level', 'percentage']);
        });
    }
};