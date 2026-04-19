<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('clinician_id')->constrained('users');

            // 'initial' = first visit from triage; 'review_after_lab' = post-lab review (Phase 5)
            $table->string('screening_type', 30)->default('initial');

            // ── Clinical history ──────────────────────────────────────────
            $table->text('complaints')->nullable();
            $table->text('history_of_presenting_illness')->nullable();
            $table->text('past_medical_history')->nullable();
            $table->text('medication_history')->nullable();
            $table->text('allergy_history')->nullable();

            // ── Examination & assessment ──────────────────────────────────
            $table->text('physical_examination')->nullable();
            $table->text('clinical_findings')->nullable();
            $table->text('provisional_diagnosis')->nullable();
            $table->text('final_diagnosis')->nullable();
            $table->text('assessment_notes')->nullable();
            $table->text('plan')->nullable();

            // ── Lab and referral ──────────────────────────────────────────
            $table->boolean('lab_requested')->default(false);
            $table->timestamp('referred_to_lab_at')->nullable();
            $table->timestamp('returned_from_lab_at')->nullable();
            $table->text('review_notes')->nullable();

            // ── Prescription flag ─────────────────────────────────────────
            $table->boolean('prescribed')->default(false);

            // ── Timestamps ────────────────────────────────────────────────
            $table->timestamp('screening_started_at')->nullable();
            $table->timestamp('screening_completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_records');
    }
};
