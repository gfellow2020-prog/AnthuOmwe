<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacy_prescription_items', function (Blueprint $table) {
            $table->integer('item_per_dose')->nullable()->after('dose');
            $table->string('time_per', 100)->nullable()->after('frequency');
            $table->string('frequency_unit', 100)->nullable()->after('time_per');
            $table->string('duration_unit', 50)->nullable()->after('duration');
            $table->date('start_date')->nullable()->after('duration_unit');
            $table->date('end_date')->nullable()->after('start_date');
            $table->boolean('is_passer_by')->default(false)->after('end_date');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_prescription_items', function (Blueprint $table) {
            $table->dropColumn([
                'item_per_dose',
                'time_per',
                'frequency_unit',
                'duration_unit',
                'start_date',
                'end_date',
                'is_passer_by',
            ]);
        });
    }
};
