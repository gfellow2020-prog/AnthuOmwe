<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('registrar_id')->constrained('users')->restrictOnDelete();

            $table->boolean('was_existing_patient')->default(false);

            // The search string the registrar used to find the patient (if existing)
            $table->string('search_reference')->nullable();

            $table->text('registration_notes')->nullable();
            $table->timestamp('registered_at')->useCurrent();

            $table->timestamps();

            $table->unique('encounter_id'); // one registration per encounter
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_records');
    }
};
