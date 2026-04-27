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
        Schema::table('screening_records', function (Blueprint $table) {
            // ── Complaints & Histories (Tab 1) ────────────────────────────
            $table->json('tb_symptoms')->nullable()->after('complaints');
            $table->text('review_of_systems')->nullable()->after('tb_symptoms');
            $table->text('chronic_conditions')->nullable()->after('allergy_history');
            $table->text('family_history')->nullable()->after('chronic_conditions');
            $table->text('social_history')->nullable()->after('family_history');

            // ── Paediatric History (Tab 2) ────────────────────────────────
            $table->decimal('birth_weight', 5, 2)->nullable()->after('social_history');
            $table->string('birth_outcome', 100)->nullable()->after('birth_weight');
            $table->text('birth_notes')->nullable()->after('birth_outcome');
            $table->text('immunization_history')->nullable()->after('birth_notes');
            $table->string('feeding_code', 150)->nullable()->after('immunization_history');
            $table->text('feeding_comments')->nullable()->after('feeding_code');
            $table->text('development_history')->nullable()->after('feeding_comments');

            // ── Plan (Tab 4) ──────────────────────────────────────────────
            $table->text('treatment_plan')->nullable()->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('screening_records', function (Blueprint $table) {
            $table->dropColumn([
                'tb_symptoms',
                'review_of_systems',
                'chronic_conditions',
                'family_history',
                'social_history',
                'birth_weight',
                'birth_outcome',
                'birth_notes',
                'immunization_history',
                'feeding_code',
                'feeding_comments',
                'development_history',
                'treatment_plan',
            ]);
        });
    }
};
