<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_dispense_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_dispense_id')
                ->constrained('pharmacy_dispenses')
                ->cascadeOnDelete();
            $table->foreignId('pharmacy_prescription_item_id')
                ->nullable()
                ->constrained('pharmacy_prescription_items')
                ->nullOnDelete();

            $table->unsignedBigInteger('drug_id')->nullable();

            $table->string('drug_name');
            $table->unsignedInteger('quantity_dispensed');
            $table->string('batch_no')->nullable();
            $table->string('stock_reference')->nullable();
            $table->text('instructions')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_dispense_items');
    }
};
