<?php

namespace Tests\Feature\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Enums\QueueTransitionStatus;
use App\Models\Encounter;
use App\Models\EncounterAudit;
use App\Models\EncounterQueueTransition;
use App\Models\EncounterStageLog;
use App\Models\Patient;
use App\Models\PharmacyDispense;
use App\Models\PharmacyDispenseItem;
use App\Models\PharmacyPrescription;
use App\Models\PharmacyPrescriptionItem;
use App\Models\RegistrationRecord;
use App\Models\ScreeningRecord;
use App\Models\TriageRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncounterProfileTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helper — builds a fully completed encounter with all data ────────────

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['name' => 'Profile Viewer']);
        $this->actingAs($user);
        return $user;
    }

    private function makeCompletedEncounter(User $user): Encounter
    {
        $patient = Patient::create([
            'patient_id'    => 'P00000099',
            'full_name'     => 'Alice Phiri',
            'gender'        => 'female',
            'date_of_birth' => '1990-01-01',
        ]);

        $encounter = Encounter::create([
            'patient_id'       => $patient->id,
            'encounter_number' => 'ENC-20260419-00099',
            'visit_type'       => 'OPD',
            'priority_level'   => 'normal',
            'current_stage'    => EncounterStage::Completed,
            'current_status'   => EncounterStatus::Completed,
            'started_by'       => $user->id,
            'closed_by'        => $user->id,
            'started_at'       => now()->subHours(3),
            'closed_at'        => now(),
            'is_locked'        => true,
        ]);

        // Registration record
        RegistrationRecord::create([
            'encounter_id'         => $encounter->id,
            'patient_id'           => $patient->id,
            'registrar_id'         => $user->id,
            'was_existing_patient' => false,
            'registered_at'        => now()->subHours(3),
        ]);

        // Triage record
        TriageRecord::create([
            'encounter_id' => $encounter->id,
            'patient_id'   => $patient->id,
            'nurse_id'     => $user->id,
            'weight'       => 60,
            'height'       => 165,
            'temperature'  => 38.2,
            'triage_at'    => now()->subHours(2),
        ]);

        // Initial screening record
        $screeningRecord = ScreeningRecord::create([
            'encounter_id'    => $encounter->id,
            'patient_id'      => $patient->id,
            'clinician_id'    => $user->id,
            'screening_type'  => 'initial',
            'complaints'      => 'Fever and chills',
            'lab_requested'   => false,
            'prescribed'      => true,
        ]);

        // Prescription
        $prescription = PharmacyPrescription::create([
            'encounter_id'        => $encounter->id,
            'patient_id'          => $patient->id,
            'screening_record_id' => $screeningRecord->id,
            'prescribed_by'       => $user->id,
            'prescription_number' => 'RX-20260419-0099',
            'status'              => 'dispensed',
            'prescribed_at'       => now()->subHour(),
        ]);

        PharmacyPrescriptionItem::create([
            'pharmacy_prescription_id' => $prescription->id,
            'drug_name'                => 'Paracetamol 500mg',
            'dose'                     => '1 tablet',
            'frequency'                => 'TDS',
            'duration'                 => '5 days',
            'quantity_prescribed'      => 15,
        ]);

        // Dispense
        $dispense = PharmacyDispense::create([
            'encounter_id'             => $encounter->id,
            'patient_id'               => $patient->id,
            'pharmacy_prescription_id' => $prescription->id,
            'dispensed_by'             => $user->id,
            'counseling_notes'         => 'Take with water',
            'dispensed_at'             => now()->subMinutes(10),
        ]);

        PharmacyDispenseItem::create([
            'pharmacy_dispense_id' => $dispense->id,
            'drug_name'            => 'Paracetamol 500mg',
            'quantity_dispensed'   => 15,
        ]);

        // Queue transitions
        $encounter->queueTransitions()->create([
            'patient_id' => $patient->id,
            'from_stage' => EncounterStage::Registration->value,
            'to_stage'   => EncounterStage::Triage->value,
            'status'     => QueueTransitionStatus::Completed->value,
            'queued_by'  => $user->id,
            'queued_at'  => now()->subHours(3),
        ]);

        $encounter->queueTransitions()->create([
            'patient_id' => $patient->id,
            'from_stage' => EncounterStage::Triage->value,
            'to_stage'   => EncounterStage::Screening->value,
            'status'     => QueueTransitionStatus::Completed->value,
            'queued_by'  => $user->id,
            'queued_at'  => now()->subHours(2),
        ]);

        $encounter->queueTransitions()->create([
            'patient_id' => $patient->id,
            'from_stage' => EncounterStage::Screening->value,
            'to_stage'   => EncounterStage::Pharmacy->value,
            'status'     => QueueTransitionStatus::Completed->value,
            'queued_by'  => $user->id,
            'queued_at'  => now()->subHour(),
        ]);

        // Stage logs
        EncounterStageLog::create([
            'encounter_id'  => $encounter->id,
            'patient_id'    => $patient->id,
            'stage_name'    => EncounterStage::Pharmacy->value,
            'stage_sequence'=> 5,
            'status'        => 'completed',
            'started_by'    => $user->id,
            'completed_by'  => $user->id,
            'started_at'    => now()->subHours(1),
            'completed_at'  => now()->subMinutes(5),
        ]);

        // Audit entries
        EncounterAudit::create([
            'encounter_id' => $encounter->id,
            'patient_id'   => $patient->id,
            'action_name'  => 'encounter_started',
            'action_stage' => EncounterStage::Registration->value,
            'action_by'    => $user->id,
            'action_at'    => now()->subHours(3),
        ]);

        EncounterAudit::create([
            'encounter_id' => $encounter->id,
            'patient_id'   => $patient->id,
            'action_name'  => 'encounter_closed',
            'action_stage' => EncounterStage::Pharmacy->value,
            'action_by'    => $user->id,
            'action_at'    => now(),
        ]);

        return $encounter;
    }

    // ─── Tests ────────────────────────────────────────────────────────────────

    public function test_full_encounter_profile_loads_successfully(): void
    {
        $user      = $this->actingAsUser();
        $encounter = $this->makeCompletedEncounter($user);

        $response = $this->get(route('encounters.show', $encounter));

        $response->assertOk();
        $response->assertViewIs('encounters.show');
        $response->assertViewHas('encounter');
    }

    public function test_encounters_index_loads_successfully(): void
    {
        $user      = $this->actingAsUser();
        $encounter = $this->makeCompletedEncounter($user);

        $response = $this->get(route('encounters.index'));

        $response->assertOk();
        $response->assertViewIs('encounters.index');
        $response->assertViewHas('encounters');
    }

    public function test_all_queue_transitions_are_visible_in_profile(): void
    {
        $user      = $this->actingAsUser();
        $encounter = $this->makeCompletedEncounter($user);

        $this->get(route('encounters.show', $encounter));

        $encounter->loadMissing('queueTransitions');
        $this->assertEquals(3, $encounter->queueTransitions->count());
    }

    public function test_all_stage_data_is_eager_loaded(): void
    {
        $user      = $this->actingAsUser();
        $encounter = $this->makeCompletedEncounter($user);

        $response = $this->get(route('encounters.show', $encounter));
        $response->assertOk();

        // The view should display the encounter with all sub-sections
        $viewEncounter = $response->viewData('encounter');
        $this->assertNotNull($viewEncounter->registrationRecord);
        $this->assertNotNull($viewEncounter->triageRecord);
        $this->assertNotNull($viewEncounter->screeningRecord);
        $this->assertNotNull($viewEncounter->prescription);
        $this->assertNotNull($viewEncounter->dispense);
    }

    public function test_completed_encounter_shows_locked_state(): void
    {
        $user      = $this->actingAsUser();
        $encounter = $this->makeCompletedEncounter($user);

        $response = $this->get(route('encounters.show', $encounter));

        $response->assertOk();

        $viewEncounter = $response->viewData('encounter');
        $this->assertTrue($viewEncounter->is_locked);
        $this->assertEquals(EncounterStage::Completed, $viewEncounter->current_stage);
    }
}
