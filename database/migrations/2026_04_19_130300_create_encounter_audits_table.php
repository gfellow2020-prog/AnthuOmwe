<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounter_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();

            $table->string('action_name');
            $table->string('action_stage');

            $table->foreignId('action_by')->constrained('users')->restrictOnDelete();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->text('notes')->nullable();
            $table->timestamp('action_at')->useCurrent();

            $table->timestamps();

            $table->index(['encounter_id', 'action_stage']);
            $table->index('action_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounter_audits');
    }
};
