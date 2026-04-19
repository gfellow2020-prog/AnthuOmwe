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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->unique();
            $table->string('full_name');
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nrc_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('relationship_to_head')->nullable();
            $table->string('household_head_of_house')->nullable();
            $table->string('household_id')->nullable();
            $table->string('barcode')->nullable()->index();
            $table->timestamp('source_created_at')->nullable();
            $table->timestamps();

            $table->index('full_name');
            $table->index('household_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
