<?php

namespace Database\Seeders;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Models\Encounter;
use App\Models\Medication;
use App\Models\ScreeningRecord;
use App\Models\StartupMedication;
use App\Models\TriageRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PastEncountersSeeder extends Seeder
{
    public function run(): void
    {
        $patientId = 3;   // Agness Miyanda
        $userId    = 1;   // Test User

        $meds = Medication::where('is_active', true)->take(20)->get();

        $pastData = [
            [
                'date'    => Carbon::parse('2026-03-05 08:30:00'),
                'number'  => 'ENC-20260305-00001',
                'vitals'  => [
                    'weight' => 62.5, 'height' => 158, 'bmi' => 25.04,
                    'temperature' => 37.2, 'pulse' => 78, 'respiratory_rate' => 18,
                    'systolic_bp' => 120, 'diastolic_bp' => 80,
                    'oxygen_saturation' => 97, 'blood_sugar' => 5.6,
                    'muac' => 14.2, 'muac_score' => 'Normal',
                    'abdominal_circumference' => 82.0,
                    'chief_complaint_brief' => 'Persistent headache for 3 days, mild fever',
                    'startup_interventions_notes' => 'Administered paracetamol 1g stat, temperature monitoring',
                ],
                'meds' => [
                    ['name' => 'Paracetamol', 'dosage' => '1g', 'route' => 'Oral', 'frequency' => 'STAT'],
                    ['name' => 'Ibuprofen',   'dosage' => '400mg', 'route' => 'Oral', 'frequency' => 'TDS'],
                ],
                'screening' => [
                    'complaints' => 'Persistent headache for 3 days with mild fever, no photophobia or neck stiffness',
                    'history_of_presenting_illness' => 'Patient reports headache started 3 days ago, gradual onset, worse in the morning. Associated with mild fever but no chills. No visual disturbances. OTC paracetamol provided partial relief.',
                    'tb_symptoms' => ['fever'],
                    'review_of_systems' => 'CVS: Normal. Resp: Clear. GI: No nausea or vomiting. Neuro: No focal deficits.',
                    'past_medical_history' => 'No significant past medical history. No previous hospitalizations.',
                    'chronic_conditions' => 'None known',
                    'medication_history' => 'OTC Paracetamol 500mg PRN for the last 3 days',
                    'allergy_history' => 'NKDA',
                    'family_history' => 'Mother — hypertension. Father — healthy.',
                    'social_history' => 'Non-smoker, occasional alcohol use. Lives with family.',
                    'physical_examination' => 'General: Alert, oriented. No pallor or jaundice. Temp 37.2°C. No meningeal signs. Cranial nerves intact.',
                    'clinical_findings' => 'Mild tenderness on temporal palpation bilaterally. No lymphadenopathy.',
                    'provisional_diagnosis' => 'Tension-type headache',
                    'final_diagnosis' => 'Tension-type headache',
                    'assessment_notes' => 'Likely stress-related. No red flags identified.',
                    'treatment_plan' => 'Paracetamol 1g TDS x 5 days, adequate hydration, stress management counselling',
                    'plan' => 'Review in 1 week if symptoms persist. Return immediately if severe headache, visual changes, or neck stiffness.',
                ],
            ],
            [
                'date'    => Carbon::parse('2026-03-22 10:15:00'),
                'number'  => 'ENC-20260322-00001',
                'vitals'  => [
                    'weight' => 63.0, 'height' => 158, 'bmi' => 25.24,
                    'temperature' => 38.5, 'pulse' => 92, 'respiratory_rate' => 22,
                    'systolic_bp' => 130, 'diastolic_bp' => 85,
                    'oxygen_saturation' => 95, 'blood_sugar' => 6.1,
                    'muac' => 14.0, 'muac_score' => 'Normal',
                    'abdominal_circumference' => 83.5,
                    'chief_complaint_brief' => 'Cough and difficulty breathing for 5 days, worsening at night',
                    'startup_interventions_notes' => 'Nebulisation with salbutamol, O2 saturation monitored, IV access secured',
                ],
                'meds' => [
                    ['name' => 'Salbutamol',    'dosage' => '5mg/ml', 'route' => 'Nebulisation', 'frequency' => 'STAT'],
                    ['name' => 'Amoxicillin',   'dosage' => '500mg',  'route' => 'Oral',         'frequency' => 'TDS'],
                    ['name' => 'Paracetamol',   'dosage' => '1g',     'route' => 'Oral',         'frequency' => 'QID'],
                ],
                'screening' => [
                    'complaints' => 'Productive cough and difficulty breathing for 5 days, worsening at night. Yellowish sputum.',
                    'history_of_presenting_illness' => 'Cough started 5 days ago, initially dry then became productive. Shortness of breath on exertion, worse lying down. Night sweats present for 3 nights. No hemoptysis. Low-grade fever intermittent.',
                    'tb_symptoms' => ['cough_2weeks', 'night_sweats', 'fever', 'fatigue'],
                    'review_of_systems' => 'CVS: Tachycardia. Resp: Bilateral crackles, wheeze. GI: Appetite reduced. MSK: Body malaise.',
                    'past_medical_history' => 'Childhood asthma, resolved. No TB contact history known.',
                    'chronic_conditions' => 'Childhood asthma (resolved)',
                    'medication_history' => 'No current chronic medications',
                    'allergy_history' => 'Allergic to Sulfonamides — rash',
                    'family_history' => 'Uncle — treated for TB 2020. Mother — asthmatic.',
                    'social_history' => 'Non-smoker. Crowded living conditions, 6 people in 2-room house.',
                    'physical_examination' => 'General: Febrile (38.5°C), tachypnoeic (RR 22), tachycardic (PR 92). Chest: Bilateral basal crackles with expiratory wheeze. No clubbing.',
                    'clinical_findings' => 'Bilateral lower zone crackles. SpO2 95% on room air. Mild dehydration.',
                    'provisional_diagnosis' => 'Lower respiratory tract infection. Rule out pulmonary TB.',
                    'final_diagnosis' => 'Community-acquired pneumonia',
                    'assessment_notes' => 'TB screening indicated given family history and constitutional symptoms. Sputum for GeneXpert requested.',
                    'treatment_plan' => 'Amoxicillin 500mg TDS x 7 days, Paracetamol 1g QID, Salbutamol nebulisation PRN, adequate fluids',
                    'plan' => 'Sputum GeneXpert, CXR. Review in 3 days. Return earlier if worsening dyspnoea or hemoptysis.',
                    'lab_requested' => true,
                ],
            ],
            [
                'date'    => Carbon::parse('2026-04-10 09:00:00'),
                'number'  => 'ENC-20260410-00001',
                'vitals'  => [
                    'weight' => 61.8, 'height' => 158, 'bmi' => 24.76,
                    'temperature' => 36.8, 'pulse' => 72, 'respiratory_rate' => 16,
                    'systolic_bp' => 118, 'diastolic_bp' => 76,
                    'oxygen_saturation' => 98, 'blood_sugar' => 4.9,
                    'muac' => 13.8, 'muac_score' => 'Normal',
                    'abdominal_circumference' => 81.0,
                    'chief_complaint_brief' => 'Follow-up visit, feeling much better, mild joint stiffness in mornings',
                    'startup_interventions_notes' => 'Vitals stable, no intervention required at triage',
                ],
                'meds' => [
                    ['name' => 'Diclofenac', 'dosage' => '50mg', 'route' => 'Oral', 'frequency' => 'BD'],
                ],
                'screening' => [
                    'complaints' => 'Follow-up visit. Feeling much better. Mild joint stiffness in mornings, lasting about 30 minutes.',
                    'history_of_presenting_illness' => 'Patient previously treated for community-acquired pneumonia. Completed antibiotic course. Cough resolved. Now reports morning stiffness in both knees for the past week, no swelling or redness.',
                    'tb_symptoms' => [],
                    'review_of_systems' => 'CVS: Normal. Resp: Clear, no cough. GI: Normal appetite. MSK: Bilateral knee stiffness, no swelling.',
                    'past_medical_history' => 'Recent pneumonia (March 2026), childhood asthma (resolved).',
                    'chronic_conditions' => 'None active',
                    'medication_history' => 'Completed Amoxicillin course. No current medications.',
                    'allergy_history' => 'Sulfonamides — rash',
                    'family_history' => 'Mother — osteoarthritis.',
                    'social_history' => 'Non-smoker, active lifestyle. Works as a teacher.',
                    'physical_examination' => 'General: Well-looking, afebrile (36.8°C). Chest clear. Knees: Full ROM, no effusion, no warmth, mild crepitus on flexion bilaterally.',
                    'clinical_findings' => 'Bilateral knee crepitus without effusion. Vitals within normal limits. Chest clear.',
                    'provisional_diagnosis' => 'Early osteoarthritis — bilateral knees',
                    'final_diagnosis' => 'Mechanical joint stiffness, early osteoarthritis',
                    'assessment_notes' => 'Pneumonia fully resolved. Joint stiffness likely degenerative given family history. No inflammatory markers needed at this point.',
                    'treatment_plan' => 'Diclofenac 50mg BD x 7 days with meals, gentle knee exercises, weight management advice',
                    'plan' => 'Review in 2 weeks. Consider X-ray knees if symptoms persist. Refer physiotherapy if not improving.',
                ],
            ],
        ];

        foreach ($pastData as $data) {
            $closedAt = $data['date']->copy()->addHours(rand(2, 5));

            $encounter = Encounter::create([
                'encounter_number' => $data['number'],
                'patient_id'       => $patientId,
                'current_stage'    => EncounterStage::Completed,
                'current_status'   => EncounterStatus::Completed,
                'priority_level'   => 'normal',
                'visit_type'       => 'OPD',
                'started_at'       => $data['date'],
                'closed_at'        => $closedAt,
                'started_by'       => $userId,
                'closed_by'        => $userId,
                'is_locked'        => true,
            ]);

            $triageAt = $data['date']->copy()->addMinutes(rand(5, 20));

            $triage = TriageRecord::create(array_merge([
                'encounter_id' => $encounter->id,
                'patient_id'   => $patientId,
                'nurse_id'     => $userId,
                'triage_at'    => $triageAt,
                'completed_at' => $triageAt->copy()->addMinutes(rand(10, 30)),
            ], $data['vitals']));

            // Screening record
            if (!empty($data['screening'])) {
                $screeningAt = $triageAt->copy()->addMinutes(rand(20, 45));
                ScreeningRecord::create(array_merge([
                    'encounter_id'            => $encounter->id,
                    'patient_id'              => $patientId,
                    'screening_type'          => 'initial',
                    'clinician_id'            => $userId,
                    'screening_started_at'    => $screeningAt,
                    'screening_completed_at'  => $screeningAt->copy()->addMinutes(rand(15, 40)),
                ], $data['screening']));
            }

            foreach ($data['meds'] as $med) {
                $medRecord = $meds->firstWhere('name', $med['name']);

                StartupMedication::create([
                    'encounter_id'     => $encounter->id,
                    'triage_record_id' => $triage->id,
                    'patient_id'       => $patientId,
                    'recorded_by'      => $userId,
                    'medication_id'    => $medRecord?->id,
                    'medication_name'  => $med['name'],
                    'dosage'           => $med['dosage'],
                    'route'            => $med['route'],
                    'frequency'        => $med['frequency'],
                    'administered_at'  => $triageAt->copy()->addMinutes(rand(2, 10)),
                ]);
            }

            $this->command->info("Seeded past encounter {$encounter->encounter_number} ({$data['date']->format('d M Y')})");
        }
    }
}
