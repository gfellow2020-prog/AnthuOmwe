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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('country', 10)->nullable()->after('nrc_number');
            $table->string('email')->nullable()->after('phone_number');
            $table->string('other_cellphone', 30)->nullable()->after('email');
            $table->string('landline', 30)->nullable()->after('other_cellphone');
            $table->string('house_number', 50)->nullable()->after('landline');
            $table->string('road_street')->nullable()->after('house_number');
            $table->string('area')->nullable()->after('road_street');
            $table->string('city_town_village')->nullable()->after('area');
            $table->text('landmarks')->nullable()->after('city_town_village');
            $table->string('marital_status', 30)->nullable()->after('landmarks');
            $table->string('spouse_first_name')->nullable()->after('marital_status');
            $table->string('spouse_surname')->nullable()->after('spouse_first_name');
            $table->string('home_language', 50)->nullable()->after('spouse_surname');
            $table->string('born_in_zambia', 5)->nullable()->after('home_language');
            $table->string('province_of_birth', 50)->nullable()->after('born_in_zambia');
            $table->string('district_of_birth', 50)->nullable()->after('province_of_birth');
            $table->string('place_of_birth')->nullable()->after('district_of_birth');
            $table->string('occupation')->nullable()->after('place_of_birth');
            $table->string('art_number', 50)->nullable()->after('occupation');
            $table->string('nupn', 80)->nullable()->after('art_number');
            $table->string('blood_group', 10)->nullable()->after('nupn');
            $table->text('allergies')->nullable()->after('blood_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'country', 'email', 'other_cellphone', 'landline',
                'house_number', 'road_street', 'area', 'city_town_village', 'landmarks',
                'marital_status', 'spouse_first_name', 'spouse_surname',
                'home_language', 'born_in_zambia', 'province_of_birth', 'district_of_birth', 'place_of_birth',
                'occupation', 'art_number', 'nupn', 'blood_group', 'allergies',
            ]);
        });
    }
};
