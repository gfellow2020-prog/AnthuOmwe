<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('startup_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignId('triage_record_id')->nullable()->constrained('triage_records')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('medication_name');
            $table->string('dosage')->nullable();
            $table->string('route')->nullable();           // oral, IV, IM, SC, etc.
            $table->string('frequency')->nullable();        // stat, BD, TDS, etc.
            $table->text('notes')->nullable();
            $table->timestamp('administered_at')->nullable();

            $table->timestamps();

            $table->index(['encounter_id', 'triage_record_id']);
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('startup_medications');
    }
};
