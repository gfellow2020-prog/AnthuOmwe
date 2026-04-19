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
        Schema::create('shift_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->nullable();
            $table->string('shift_type')->nullable();
            $table->unsignedInteger('total_patients_seen')->nullable();
            $table->string('reported_by')->nullable();
            $table->timestamp('source_created_at')->nullable();
            $table->timestamps();

            $table->index(['report_date', 'shift_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_reports');
    }
};
