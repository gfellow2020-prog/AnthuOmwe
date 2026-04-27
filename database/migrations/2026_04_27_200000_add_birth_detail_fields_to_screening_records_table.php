<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('screening_records', function (Blueprint $table) {
            $table->decimal('birth_length', 5, 2)->nullable()->after('birth_weight');
            $table->decimal('head_circumference', 5, 2)->nullable()->after('birth_length');
            $table->decimal('chest_circumference', 5, 2)->nullable()->after('head_circumference');
            $table->string('general_condition', 100)->nullable()->after('chest_circumference');
            $table->boolean('is_breast_feeding_well')->nullable()->after('general_condition');
            $table->string('other_feeding_option', 100)->nullable()->after('is_breast_feeding_well');
            $table->string('delivery_time', 20)->nullable()->after('other_feeding_option');
            $table->string('vaccination_outside', 255)->nullable()->after('delivery_time');
            $table->string('tetanus_at_birth', 100)->nullable()->after('vaccination_outside');
        });
    }

    public function down(): void
    {
        Schema::table('screening_records', function (Blueprint $table) {
            $table->dropColumn([
                'birth_length',
                'head_circumference',
                'chest_circumference',
                'general_condition',
                'is_breast_feeding_well',
                'other_feeding_option',
                'delivery_time',
                'vaccination_outside',
                'tetanus_at_birth',
            ]);
        });
    }
};
