<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_prescriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('screening_record_id')->nullable()->constrained('screening_records')->nullOnDelete();
            $table->foreignId('prescribed_by')->constrained('users');

            $table->string('prescription_number')->unique();

            // status: draft | active | dispensed | cancelled
            $table->string('status', 30)->default('active');

            $table->text('notes')->nullable();

            $table->timestamp('prescribed_at')->nullable();
            $table->timestamps();

            $table->index(['encounter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_prescriptions');
    }
};
