<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('triage_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('nurse_id')->constrained('users')->restrictOnDelete();

            // ── Vitals ───────────────────────────────────────────────────────
            $table->decimal('weight', 5, 2)->nullable();            // kg
            $table->decimal('height', 5, 2)->nullable();            // cm
            $table->decimal('bmi', 5, 2)->nullable();               // computed
            $table->decimal('temperature', 4, 1)->nullable();       // °C
            $table->unsignedSmallInteger('pulse')->nullable();      // bpm
            $table->unsignedSmallInteger('respiratory_rate')->nullable(); // breaths/min
            $table->unsignedSmallInteger('systolic_bp')->nullable();      // mmHg
            $table->unsignedSmallInteger('diastolic_bp')->nullable();     // mmHg
            $table->decimal('oxygen_saturation', 4, 1)->nullable(); // %
            $table->decimal('blood_sugar', 5, 2)->nullable();       // mmol/L
            $table->unsignedTinyInteger('pain_scale')->nullable();  // 0–10

            // ── Clinical notes ────────────────────────────────────────────────
            $table->text('chief_complaint_brief')->nullable();
            $table->text('startup_interventions_notes')->nullable();
            $table->text('startup_medications_notes')->nullable();
            $table->text('triage_notes')->nullable();

            $table->timestamp('triage_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique('encounter_id'); // one triage record per encounter
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triage_records');
    }
};
