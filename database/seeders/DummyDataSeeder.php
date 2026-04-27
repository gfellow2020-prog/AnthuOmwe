<?php

namespace Database\Seeders;

use App\Models\Encounter;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    /**
     * Seed the application's database with dummy data.
     */
    public function run(): void
    {
        $this->command->info('Seeding dummy data...');

        // Create Households
        $householdIds = [];
        $householdNames = [
            'Banda', 'Phiri', 'Mwale', 'Tembo', 'Chirwa', 'Kamanga', 'Nyirenda', 'Gondwe',
            'Mbewe', 'Zimba', 'Lungu', 'Sakala', 'Mumba', 'Zulu', 'Nkhoma', 'Chisi',
            'Mkandawire', 'Kalua', 'Msiska', 'Ngoma'
        ];

        foreach ($householdNames as $index => $name) {
            $householdId = 'HH-' . str_pad($index + 1, 5, '0', STR_PAD_LEFT);
            $village = ['Lilongwe', 'Blantyre', 'Mzuzu', 'Zomba', 'Kasungu'][array_rand(['Lilongwe', 'Blantyre', 'Mzuzu', 'Zomba', 'Kasungu'])];

            DB::table('households')->insert([
                'household_id' => $householdId,
                'head_of_house' => $name . ' Family',
                'village' => $village,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $householdIds[] = [
                'household_id' => $householdId,
                'head_of_house' => $name . ' Family',
                'village' => $village,
            ];
        }
        $this->command->info('Created ' . count($householdIds) . ' households');

        // Create Patients
        $firstNames = ['James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth', 'Chikondi', 'Tawonga', 'Blessings', 'Grace', 'Hope', 'Faith', 'Mercy', 'Precious', 'Gift', 'Love'];
        $lastNames = ['Banda', 'Phiri', 'Mwale', 'Tembo', 'Chirwa', 'Kamanga', 'Nyirenda', 'Gondwe', 'Mbewe', 'Zimba'];

        $patients = [];
        for ($i = 0; $i < 150; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $gender = ['male', 'female'][array_rand(['male', 'female'])];
            $dob = Carbon::now()->subYears(rand(1, 80))->subDays(rand(0, 365));
            $household = $householdIds[array_rand($householdIds)];

            $patient = Patient::create([
                'patient_id' => 'PAT-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'barcode' => 'BC' . strtoupper(Str::random(10)),
                'full_name' => $firstName . ' ' . $lastName,
                'gender' => $gender,
                'date_of_birth' => $dob,
                'phone_number' => '+265' . rand(880000000, 999999999),
                'household_id' => $household['household_id'],
                'household_head_of_house' => $household['head_of_house'],
                'city_town_village' => $household['village'],
            ]);
            $patients[] = $patient;
        }
        $this->command->info('Created ' . count($patients) . ' patients');

        // Create Encounters across different stages
        $stages = ['registration', 'triage', 'screening', 'lab', 'screening_review', 'pharmacy', 'completed'];
        $statuses = ['queued', 'in_progress', 'completed'];
        $visitTypes = ['new_visit', 'follow_up', 'referral', 'emergency'];
        $priorities = ['normal', 'urgent', 'emergency'];

        $encounters = [];
        for ($i = 0; $i < 80; $i++) {
            $patient = $patients[array_rand($patients)];
            $stage = $stages[array_rand($stages)];
            $status = $stage === 'completed' ? 'completed' : $statuses[array_rand($statuses)];
            $createdAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23));

            $encounters[] = Encounter::create([
                'encounter_number' => 'ENC-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'patient_id' => $patient->id,
                'current_stage' => $stage,
                'current_status' => $status,
                'visit_type' => $visitTypes[array_rand($visitTypes)],
                'priority_level' => $priorities[array_rand($priorities)],
                'started_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
        $this->command->info('Created ' . count($encounters) . ' encounters');

        // Create Shift Reports
        $shiftTypes = ['morning', 'afternoon', 'night'];
        $reporters = ['Dr. Banda', 'Nurse Phiri', 'Dr. Mwale', 'Nurse Tembo', 'Dr. Chirwa'];

        for ($i = 0; $i < 14; $i++) {
            $reportDate = Carbon::now()->subDays($i);
            DB::table('shift_reports')->insert([
                'report_date' => $reportDate,
                'shift_type' => $shiftTypes[array_rand($shiftTypes)],
                'total_patients_seen' => rand(15, 60),
                'reported_by' => $reporters[array_rand($reporters)],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->command->info('Created 14 shift reports');

        $this->command->info('Dummy data seeding completed!');
    }
}
