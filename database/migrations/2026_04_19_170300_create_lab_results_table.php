<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lab_request_id')->constrained('lab_requests')->cascadeOnDelete();
            $table->foreignId('lab_request_item_id')->nullable()->constrained('lab_request_items')->nullOnDelete();
            $table->foreignId('encounter_id')->constrained('encounters');
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('recorded_by')->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('result_value', 200)->nullable();
            $table->text('result_text')->nullable();
            $table->string('reference_range', 200)->nullable();

            // normal | abnormal | critical | inconclusive
            $table->string('interpretation', 50)->nullable();

            $table->text('remarks')->nullable();

            // pending | resulted | verified | flagged
            $table->string('result_status', 30)->default('resulted');

            $table->timestamp('result_recorded_at');
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
