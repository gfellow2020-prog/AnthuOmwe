<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_prescription_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_prescription_id')
                ->constrained('pharmacy_prescriptions')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('drug_id')->nullable();

            $table->string('drug_name');
            $table->string('strength')->nullable();
            $table->string('formulation')->nullable();     // tablet | capsule | syrup | injection …
            $table->string('dose');                         // e.g. "500mg" or "2 tablets"
            $table->string('frequency');                    // e.g. "TDS" / "BD" / "OD"
            $table->string('duration');                     // e.g. "5 days" / "1 week"
            $table->unsignedInteger('quantity_prescribed');
            $table->string('route')->nullable();            // oral | IV | IM | topical …
            $table->text('instructions')->nullable();       // special instructions

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_prescription_items');
    }
};
