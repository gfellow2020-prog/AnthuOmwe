<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('screening_record_id')->nullable()->constrained('screening_records')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users');

            $table->string('request_number')->unique();
            $table->text('request_notes')->nullable();
            $table->string('priority_level', 20)->nullable();    // normal | urgent | stat

            // Possible values: pending | in_progress | completed | cancelled
            $table->string('status', 20)->default('pending');

            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_requests');
    }
};
