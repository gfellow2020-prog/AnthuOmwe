<?php

namespace Tests\Feature\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Enums\QueueTransitionStatus;
use App\Models\Encounter;
use App\Models\LabRequest;
use App\Models\Patient;
use App\Models\ScreeningRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function actingAsLabTech(): User
    {
        $user = User::factory()->create(['name' => 'Lab Tech']);
        $this->actingAs($user);
        return $user;
    }

    private function makeEncounterAtLabQueued(User $user): Encounter
    {
        $patient = Patient::create([
            'patient_id'    => 'P00000030',
            'full_name'     => 'Grace Mwale',
            'gender'        => 'female',
            'date_of_birth' => '1992-06-15',
        ]);

        $encounter = Encounter::create([
            'patient_id'       => $patient->id,
            'encounter_number' => 'ENC-20260419-00030',
            'visit_type'       => 'OPD',
            'priority_level'   => 'normal',
            'current_stage'    => EncounterStage::Lab,
            'current_status'   => EncounterStatus::Queued,
            'started_by'       => $user->id,
            'started_at'       => now(),
            'is_locked'        => false,
        ]);

        // Simulated screening→lab queue transition
        $encounter->queueTransitions()->create([
            'patient_id' => $patient->id,
            'from_stage' => EncounterStage::Screening->value,
            'to_stage'   => EncounterStage::Lab->value,
            'status'     => QueueTransitionStatus::Queued->value,
            'queued_by'  => $user->id,
            'queued_at'  => now(),
        ]);

        // A screening record is needed for the return-to-screening-review step
        ScreeningRecord::create([
            'encounter_id'   => $encounter->id,
            'patient_id'     => $patient->id,
            'screened_by'    => $user->id,
            'clinician_id'   => $user->id,
            'screening_type' => 'initial',
            'lab_requested'  => true,
        ]);

        return $encounter;
    }

    private function makeEncounterAtLabInProgress(User $user): Encounter
    {
        $encounter = $this->makeEncounterAtLabQueued($user);
        $this->post(route('lab.receive', $encounter));
        $encounter->refresh();
        return $encounter;
    }

    // ─── 1. Patient queued from screening can be received ─────────────────────

    public function test_patient_queued_from_screening_can_be_received_in_lab(): void
    {
        $tech      = $this->actingAsLabTech();
        $encounter = $this->makeEncounterAtLabQueued($tech);

        $response = $this->post(route('lab.receive', $encounter));

        $response->assertRedirect(route('lab.show', $encounter));
        $response->assertSessionHas('success');

        $encounter->refresh();
        $this->assertSame(EncounterStage::Lab, $encounter->current_stage);
        $this->assertSame(EncounterStatus::InProgress, $encounter->current_status);
    }

    // ─── 2. Lab request is created with correct data ──────────────────────────

    public function test_lab_request_is_created_when_patient_is_received(): void
    {
        $tech      = $this->actingAsLabTech();
        $encounter = $this->makeEncounterAtLabQueued($tech);

        $this->post(route('lab.receive', $encounter));

        $encounter->refresh();
        $this->assertNotNull($encounter->labRequest);

        $this->assertDatabaseHas('lab_requests', [
            'encounter_id' => $encounter->id,
            'patient_id'   => $encounter->patient_id,
            'status'       => 'in_progress',
        ]);

        $labRequest = $encounter->labRequest;
        $this->assertStringStartsWith('LAB-', $labRequest->request_number);
        $this->assertNotNull($labRequest->screening_record_id);
    }

    // ─── 3. Queue transition is marked received ───────────────────────────────

    public function test_lab_queue_transition_is_marked_received(): void
    {
        $tech      = $this->actingAsLabTech();
        $encounter = $this->makeEncounterAtLabQueued($tech);

        $this->post(route('lab.receive', $encounter));

        $this->assertDatabaseHas('encounter_queue_transitions', [
            'encounter_id' => $encounter->id,
            'from_stage'   => EncounterStage::Screening->value,
            'to_stage'     => EncounterStage::Lab->value,
            'status'       => QueueTransitionStatus::Received->value,
        ]);
    }

    // ─── 4. Samples are recorded correctly ────────────────────────────────────

    public function test_lab_samples_are_recorded(): void
    {
        $tech      = $this->actingAsLabTech();
        $encounter = $this->makeEncounterAtLabInProgress($tech);
        $lr        = $encounter->labRequest;

        $response = $this->post(route('lab.samples', $encounter), [
            'samples' => [
                ['sample_type' => 'Blood', 'sample_label' => 'A1'],
                ['sample_type' => 'Urine', 'sample_label' => 'U1'],
            ],
        ]);

        $response->assertRedirect(route('lab.show', $encounter));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lab_samples', [
            'lab_request_id' => $lr->id,
            'sample_type'    => 'Blood',
            'sample_label'   => 'A1',
        ]);

        $this->assertDatabaseHas('lab_samples', [
            'lab_request_id' => $lr->id,
            'sample_type'    => 'Urine',
            'sample_label'   => 'U1',
        ]);

        $this->assertEquals(2, $lr->fresh()->samples()->count());
    }

    // ─── 5. Results are recorded correctly ────────────────────────────────────

    public function test_lab_results_are_recorded(): void
    {
        $tech      = $this->actingAsLabTech();
        $encounter = $this->makeEncounterAtLabInProgress($tech);
        $lr        = $encounter->labRequest;

        $response = $this->post(route('lab.results', $encounter), [
            'results' => [
                [
                    'result_value'   => '10.2 g/dL',
                    'reference_range'=> '11.5–16.5 g/dL',
                    'interpretation' => 'abnormal',
                    'result_text'    => 'Below normal range',
                ],
            ],
        ]);

        $response->assertRedirect(route('lab.show', $encounter));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lab_results', [
            'lab_request_id' => $lr->id,
            'result_value'   => '10.2 g/dL',
            'interpretation' => 'abnormal',
            'result_status'  => 'resulted',
        ]);

        $this->assertEquals(1, $lr->fresh()->results()->count());
    }

    public function test_lab_result_item_must_belong_to_current_lab_request(): void
    {
        $tech      = $this->actingAsLabTech();
        $encounter = $this->makeEncounterAtLabInProgress($tech);

        $otherPatient = Patient::create([
            'patient_id'    => 'P00000031',
            'full_name'     => 'Foreign Patient',
            'gender'        => 'female',
            'date_of_birth' => '1990-01-01',
        ]);

        $otherEncounter = Encounter::create([
            'patient_id'       => $otherPatient->id,
            'encounter_number' => 'ENC-20260419-00031',
            'visit_type'       => 'OPD',
            'priority_level'   => 'normal',
            'current_stage'    => EncounterStage::Lab,
            'current_status'   => EncounterStatus::InProgress,
            'started_by'       => $tech->id,
            'started_at'       => now(),
            'is_locked'        => false,
        ]);

        $otherLabRequest = LabRequest::create([
            'encounter_id'    => $otherEncounter->id,
            'patient_id'      => $otherPatient->id,
            'requested_by'    => $tech->id,
            'request_number'  => 'LAB-20260419-0002',
            'status'          => 'in_progress',
            'requested_at'    => now(),
        ]);

        $foreignItem = $otherLabRequest->items()->create([
            'test_name' => 'Full Blood Count',
            'status'    => 'pending',
        ]);

        $response = $this->post(route('lab.results', $encounter), [
            'results' => [
                [
                    'lab_request_item_id' => $foreignItem->id,
                    'result_value' => '10.2 g/dL',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('results.0.lab_request_item_id');
        $this->assertSame(0, $encounter->labRequest->fresh()->results()->count());
    }

    // ─── 6. Encounter is queued back to Screening Review ─────────────────────

    public function test_encounter_is_queued_back_to_screening_review_after_lab(): void
    {
        $tech      = $this->actingAsLabTech();
        $encounter = $this->makeEncounterAtLabInProgress($tech);

        $response = $this->post(route('lab.complete', $encounter), [
            'results' => [
                [
                    'result_value'   => '5000 cells/uL',
                    'reference_range'=> '4500–11000',
                    'interpretation' => 'normal',
                    'result_text'    => 'White cell count within normal limits',
                ],
            ],
            'notes' => 'All results reviewed.',
        ]);

        $response->assertRedirect(route('lab.queue'));
        $response->assertSessionHas('success');

        $encounter->refresh();
        $this->assertSame(EncounterStage::ScreeningReview, $encounter->current_stage);
        $this->assertSame(EncounterStatus::Queued, $encounter->current_status);
    }

    // ─── 7. Lab stage log is opened and closed ───────────────────────────────

    public function test_lab_stage_log_is_opened_and_closed(): void
    {
        $tech      = $this->actingAsLabTech();
        $encounter = $this->makeEncounterAtLabQueued($tech);

        // Open — when received
        $this->post(route('lab.receive', $encounter));

        $this->assertDatabaseHas('encounter_stage_logs', [
            'encounter_id' => $encounter->id,
            'stage_name'   => EncounterStage::Lab->value,
        ]);

        // Stage log is still open (completed_at is null)
        $encounter->refresh();
        $openLog = $encounter->stageLogs()
            ->where('stage_name', EncounterStage::Lab->value)
            ->whereNull('completed_at')
            ->first();
        $this->assertNotNull($openLog, 'Expected an open lab stage log');

        // Close — when completed
        $this->post(route('lab.complete', $encounter), [
            'results' => [['result_value' => '120/80 mmHg', 'interpretation' => 'normal']],
        ]);

        $encounter->refresh();
        $stillOpen = $encounter->stageLogs()
            ->where('stage_name', EncounterStage::Lab->value)
            ->whereNull('completed_at')
            ->first();
        $this->assertNull($stillOpen, 'Lab stage log should be closed after completion');
    }

    // ─── 8. ScreeningReview queue transition is created ──────────────────────

    public function test_lab_to_screening_review_queue_transition_is_created(): void
    {
        $tech      = $this->actingAsLabTech();
        $encounter = $this->makeEncounterAtLabInProgress($tech);

        $this->post(route('lab.complete', $encounter), [
            'results' => [['result_value' => '12.0 g/dL', 'interpretation' => 'normal']],
        ]);

        $this->assertDatabaseHas('encounter_queue_transitions', [
            'encounter_id' => $encounter->id,
            'from_stage'   => EncounterStage::Lab->value,
            'to_stage'     => EncounterStage::ScreeningReview->value,
        ]);
    }

    // ─── 9. Lab request is marked completed after handover ───────────────────

    public function test_lab_request_is_marked_completed(): void
    {
        $tech      = $this->actingAsLabTech();
        $encounter = $this->makeEncounterAtLabInProgress($tech);
        $lr        = $encounter->labRequest;

        $this->post(route('lab.complete', $encounter), [
            'results' => [['result_value' => '7.2 mmol/L', 'interpretation' => 'abnormal']],
        ]);

        $this->assertDatabaseHas('lab_requests', [
            'id'     => $lr->id,
            'status' => 'completed',
        ]);

        $this->assertNotNull($lr->fresh()->completed_at);
    }

    // ─── 10. Lab queue page loads ─────────────────────────────────────────────

    public function test_lab_queue_page_loads(): void
    {
        $this->actingAsLabTech();

        $response = $this->get(route('lab.queue'));

        $response->assertOk();
        $response->assertViewIs('lab.queue');
    }
}
