<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounter_queue_transitions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();

            $table->string('from_stage')->nullable();
            $table->string('to_stage');

            $table->foreignId('queued_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('queued_at')->useCurrent();
            $table->timestamp('received_at')->nullable();

            $table->text('transition_notes')->nullable();

            // QueueTransitionStatus: queued | received | completed
            $table->string('status')->default('queued');

            $table->timestamps();

            $table->index(['encounter_id', 'to_stage', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounter_queue_transitions');
    }
};
