<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_staff_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('screening_record_id')->constrained('screening_records')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');

            // e.g. 'lead_clinician', 'intern', 'consultant', 'nurse_aide'
            $table->string('role_name', 60)->nullable();

            // e.g. 'primary', 'assisting', 'observing', 'supervising'
            $table->string('participation_type', 60)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_staff_assignments');
    }
};
