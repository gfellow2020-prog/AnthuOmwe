<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Trade / brand name
            $table->string('generic_name')->nullable();      // INN / generic
            $table->string('category');                      // Analgesic, Antibiotic, etc.
            $table->string('form');                          // Tablet, Injection, Syrup, etc.
            $table->string('strength')->nullable();          // 500mg, 250mg/5ml, etc.
            $table->string('default_route')->nullable();     // Oral, IV, IM, SC, etc.
            $table->string('default_frequency')->nullable(); // Stat, BD, TDS, etc.
            $table->boolean('is_controlled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
            $table->index('name');
            $table->index('generic_name');
        });

        // Add optional FK from startup_medications → medications
        Schema::table('startup_medications', function (Blueprint $table) {
            $table->foreignId('medication_id')->nullable()->after('recorded_by')
                  ->constrained('medications')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('startup_medications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('medication_id');
        });

        Schema::dropIfExists('medications');
    }
};
