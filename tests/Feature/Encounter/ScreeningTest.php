<?php

namespace Tests\Feature\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Enums\QueueTransitionStatus;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\ScreeningRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreeningTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function actingAsClinician(): User
    {
        $user = User::factory()->create(['name' => 'Test Clinician']);
        $this->actingAs($user);
        return $user;
    }

    private function makeEncounterAtScreeningQueued(User $user): Encounter
    {
        $patient = Patient::create([
            'patient_id'   => 'P00000020',
            'full_name'    => 'Blessings Tembo',
            'gender'       => 'male',
            'date_of_birth'=> '1980-11-03',
        ]);

        $encounter = Encounter::create([
            'patient_id'       => $patient->id,
            'encounter_number' => 'ENC-20260419-00020',
            'visit_type'       => 'OPD',
            'priority_level'   => 'normal',
            'current_stage'    => EncounterStage::Screening,
            'current_status'   => EncounterStatus::Queued,
            'started_by'       => $user->id,
            'started_at'       => now(),
            'is_locked'        => false,
        ]);

        // Incoming queue transition from triage
        $encounter->queueTransitions()->create([
            'patient_id' => $patient->id,
            'from_stage' => EncounterStage::Triage->value,
            'to_stage'   => EncounterStage::Screening->value,
            'status'     => QueueTransitionStatus::Queued->value,
            'queued_by'  => $user->id,
            'queued_at'  => now(),
        ]);

        return $encounter;
    }

    private function makeEncounterAtScreeningInProgress(User $user): Encounter
    {
        $encounter = $this->makeEncounterAtScreeningQueued($user);
        $this->post(route('screening.receive', $encounter));
        $encounter->refresh();
        return $encounter;
    }

    private function postAssessment(Encounter $encounter, array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('screening.complete', $encounter), array_merge([
            'complaints'            => 'Headache and fever for 3 days',
            'provisional_diagnosis' => 'Malaria',
            'plan'                  => 'Order blood smear',
            'lab_requested'         => false,
        ], $overrides));
    }

    // ─── 1. Patient queued from triage can be received ────────────────────────

    public function test_patient_queued_from_triage_can_be_received_in_screening(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningQueued($clinician);

        $response = $this->post(route('screening.receive', $encounter));

        $response->assertRedirect(route('screening.show', $encounter));
        $response->assertSessionHas('success');

        $encounter->refresh();
        $this->assertSame(EncounterStage::Screening, $encounter->current_stage);
        $this->assertSame(EncounterStatus::InProgress, $encounter->current_status);
    }

    // ─── 2. Queue transition is marked received ───────────────────────────────

    public function test_screening_queue_transition_is_marked_received(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningQueued($clinician);

        $this->post(route('screening.receive', $encounter));

        $this->assertDatabaseHas('encounter_queue_transitions', [
            'encounter_id' => $encounter->id,
            'from_stage'   => EncounterStage::Triage->value,
            'to_stage'     => EncounterStage::Screening->value,
            'status'       => QueueTransitionStatus::Received->value,
        ]);
    }

    // ─── 3. Initial screening data is saved ──────────────────────────────────

    public function test_initial_screening_data_is_saved(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningInProgress($clinician);

        $this->postAssessment($encounter, [
            'complaints'                    => 'Fever and chills',
            'history_of_presenting_illness' => 'Started 2 days ago',
            'provisional_diagnosis'         => 'Suspect Malaria',
            'plan'                          => 'Request blood smear + CBC',
        ]);

        $this->assertDatabaseHas('screening_records', [
            'encounter_id'          => $encounter->id,
            'complaints'            => 'Fever and chills',
            'provisional_diagnosis' => 'Suspect Malaria',
            'screening_type'        => 'initial',
        ]);
    }

    // ─── 4. Encounter can be queued to lab ────────────────────────────────────

    public function test_encounter_can_be_queued_to_lab(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningInProgress($clinician);

        $response = $this->postAssessment($encounter, ['lab_requested' => true]);

        $response->assertRedirect(route('screening.queue'));

        $encounter->refresh();
        $this->assertSame(EncounterStage::Lab, $encounter->current_stage);
        $this->assertSame(EncounterStatus::Queued, $encounter->current_status);
    }

    // ─── 5. Encounter can be queued directly to pharmacy (no lab) ────────────

    public function test_encounter_can_be_queued_directly_to_pharmacy_when_lab_not_required(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningInProgress($clinician);

        $response = $this->postAssessment($encounter, ['lab_requested' => false]);

        $response->assertRedirect(route('screening.queue'));

        $encounter->refresh();
        $this->assertSame(EncounterStage::Pharmacy, $encounter->current_stage);
        $this->assertSame(EncounterStatus::Queued, $encounter->current_status);
    }

    // ─── 6. lab_requested flag is set correctly on screening record ──────────

    public function test_lab_requested_flag_is_set_on_screening_record(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningInProgress($clinician);

        $this->postAssessment($encounter, ['lab_requested' => true]);

        $record = ScreeningRecord::where('encounter_id', $encounter->id)->first();
        $this->assertNotNull($record);
        $this->assertTrue($record->lab_requested);
        $this->assertNotNull($record->referred_to_lab_at);
    }

    // ─── 7. Screening staff assignments persist ───────────────────────────────

    public function test_screening_staff_assignments_persist_correctly(): void
    {
        $clinician = $this->actingAsClinician();
        $intern    = User::factory()->create(['name' => 'Intern User']);
        $encounter = $this->makeEncounterAtScreeningInProgress($clinician);

        $this->postAssessment($encounter, [
            'lab_requested'    => false,
            'staff_assignments' => [
                [
                    'user_id'            => $intern->id,
                    'role_name'          => 'intern',
                    'participation_type' => 'assisting',
                    'notes'              => 'Helped with examination',
                ],
            ],
        ]);

        $this->assertDatabaseHas('screening_staff_assignments', [
            'user_id'            => $intern->id,
            'role_name'          => 'intern',
            'participation_type' => 'assisting',
        ]);
    }

    // ─── 8. Queue transition to lab is created ────────────────────────────────

    public function test_queue_transition_to_lab_is_created(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningInProgress($clinician);

        $this->postAssessment($encounter, ['lab_requested' => true]);

        $this->assertDatabaseHas('encounter_queue_transitions', [
            'encounter_id' => $encounter->id,
            'from_stage'   => EncounterStage::Screening->value,
            'to_stage'     => EncounterStage::Lab->value,
            'status'       => QueueTransitionStatus::Queued->value,
        ]);
    }

    // ─── 9. Queue transition to pharmacy is created (direct route) ────────────

    public function test_queue_transition_to_pharmacy_is_created_when_no_lab(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningInProgress($clinician);

        $this->postAssessment($encounter, ['lab_requested' => false]);

        $this->assertDatabaseHas('encounter_queue_transitions', [
            'encounter_id' => $encounter->id,
            'from_stage'   => EncounterStage::Screening->value,
            'to_stage'     => EncounterStage::Pharmacy->value,
            'status'       => QueueTransitionStatus::Queued->value,
        ]);
    }

    // ─── 10. Stage log and audit are written ──────────────────────────────────

    public function test_stage_log_and_audit_are_written(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningQueued($clinician);

        // Receive
        $this->post(route('screening.receive', $encounter));

        $this->assertDatabaseHas('encounter_audits', [
            'encounter_id' => $encounter->id,
            'action_name'  => 'screening_received',
            'action_stage' => EncounterStage::Screening->value,
        ]);

        $this->assertDatabaseHas('encounter_stage_logs', [
            'encounter_id' => $encounter->id,
            'stage_name'   => EncounterStage::Screening->value,
            'status'       => QueueTransitionStatus::Received->value,
        ]);

        // Complete
        $encounter->refresh();
        $this->postAssessment($encounter, ['lab_requested' => false]);

        $this->assertDatabaseHas('encounter_audits', [
            'encounter_id' => $encounter->id,
            'action_name'  => 'queued_to_pharmacy_direct',
            'action_stage' => EncounterStage::Screening->value,
        ]);

        // Stage log should be closed
        $log = $encounter->stageLogs()
            ->where('stage_name', EncounterStage::Screening->value)
            ->whereNotNull('completed_at')
            ->first();
        $this->assertNotNull($log);
    }
}
