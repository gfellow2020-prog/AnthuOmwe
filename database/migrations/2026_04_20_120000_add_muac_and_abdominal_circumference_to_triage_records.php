<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('triage_records', function (Blueprint $table) {
            $table->decimal('muac', 5, 1)->nullable()->after('blood_sugar');           // cm
            $table->string('muac_score', 20)->nullable()->after('muac');               // Green/Yellow/Red
            $table->decimal('abdominal_circumference', 5, 1)->nullable()->after('muac_score'); // cm
        });
    }

    public function down(): void
    {
        Schema::table('triage_records', function (Blueprint $table) {
            $table->dropColumn(['muac', 'muac_score', 'abdominal_circumference']);
        });
    }
};
