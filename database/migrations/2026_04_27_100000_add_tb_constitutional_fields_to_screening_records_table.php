<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('screening_records', function (Blueprint $table) {
            $table->string('constitutional_symptoms', 255)->nullable()->after('tb_symptoms');
            $table->string('presumptive_tb_case_no', 100)->nullable()->after('constitutional_symptoms');
        });
    }

    public function down(): void
    {
        Schema::table('screening_records', function (Blueprint $table) {
            $table->dropColumn(['constitutional_symptoms', 'presumptive_tb_case_no']);
        });
    }
};
