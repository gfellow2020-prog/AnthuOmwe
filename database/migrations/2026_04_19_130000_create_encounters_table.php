<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounters', function (Blueprint $table) {
            $table->id();
            $table->string('encounter_number')->unique();

            // Adapts to the existing patients table (id is the integer PK)
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();

            // Stage and status stored as strings; cast to enums in the model
            $table->string('current_stage')->default('registration');
            $table->string('current_status')->default('started');

            $table->string('priority_level')->nullable();
            $table->string('visit_type')->nullable();

            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();

            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('closure_notes')->nullable();
            $table->boolean('is_locked')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'current_stage']);
            $table->index('current_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};
