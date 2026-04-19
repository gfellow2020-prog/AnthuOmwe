<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_dispenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('pharmacy_prescription_id')
                ->nullable()
                ->constrained('pharmacy_prescriptions')
                ->nullOnDelete();
            $table->foreignId('dispensed_by')->constrained('users');

            $table->text('dispensing_notes')->nullable();
            $table->text('counseling_notes')->nullable();

            $table->timestamp('dispensed_at');
            $table->timestamps();

            $table->index('encounter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_dispenses');
    }
};
