<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lab_request_id')->constrained('lab_requests')->cascadeOnDelete();

            $table->string('test_code', 60)->nullable();
            $table->string('test_name', 200);
            $table->string('specimen_type', 100)->nullable();
            $table->string('test_group', 100)->nullable();
            $table->text('instructions')->nullable();

            // pending | collected | resulted | cancelled
            $table->string('status', 20)->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_request_items');
    }
};
