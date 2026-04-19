<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_samples', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lab_request_id')->constrained('lab_requests')->cascadeOnDelete();
            $table->foreignId('encounter_id')->constrained('encounters');
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('collected_by')->constrained('users');

            $table->string('sample_type', 100);        // e.g. Blood, Urine, Stool, Sputum
            $table->string('sample_label', 100)->nullable();
            $table->text('collection_notes')->nullable();

            $table->timestamp('collected_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_samples');
    }
};
