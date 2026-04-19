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
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('household_id')->unique();
            $table->string('head_of_house')->nullable();
            $table->string('nrc_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('village')->nullable();
            $table->string('town')->nullable();
            $table->string('household_type')->nullable();
            $table->string('barcode')->nullable()->index();
            $table->string('subscription_plan')->nullable();
            $table->decimal('subscription_fee', 12, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('transaction_code')->nullable();
            $table->timestamp('source_created_at')->nullable();
            $table->timestamps();

            $table->index('head_of_house');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
