<?php

namespace Tests\Feature\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Enums\QueueTransitionStatus;
use App\Models\Encounter;
use App\Models\LabRequest;
use App\Models\LabResult;
use App\Models\Patient;
use App\Models\ScreeningRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreeningReviewTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function actingAsClinician(): User
    {
        $user = User::factory()->create(['name' => 'Review Clinician']);
        $this->actingAs($user);
        return $user;
    }

    private function makeEncounterAtScreeningReviewQueued(User $user): Encounter
    {
        $patient = Patient::create([
            'patient_id'    => 'P00000040',
            'full_name'     => 'Joseph Banda',
            'gender'        => 'male',
            'date_of_birth' => '1975-03-22',
        ]);

        $encounter = Encounter::create([
            'patient_id'       => $patient->id,
            'encounter_number' => 'ENC-20260419-00040',
            'visit_type'       => 'OPD',
            'priority_level'   => 'normal',
            'current_stage'    => EncounterStage::ScreeningReview,
            'current_status'   => EncounterStatus::Queued,
            'started_by'       => $user->id,
            'started_at'       => now(),
            'is_locked'        => false,
        ]);

        // Incoming lab → screening_review queue transition
        $encounter->queueTransitions()->create([
            'patient_id' => $patient->id,
            'from_stage' => EncounterStage::Lab->value,
            'to_stage'   => EncounterStage::ScreeningReview->value,
            'status'     => QueueTransitionStatus::Queued->value,
            'queued_by'  => $user->id,
            'queued_at'  => now(),
        ]);

        // Initial screening record (from Phase 3)
        $screeningRecord = ScreeningRecord::create([
            'encounter_id'          => $encounter->id,
            'patient_id'            => $patient->id,
            'clinician_id'          => $user->id,
            'screening_type'        => 'initial',
            'complaints'            => 'Fever and fatigue',
            'provisional_diagnosis' => 'Malaria',
            'lab_requested'         => true,
        ]);

        // Lab request + result (from Phase 4)
        $labRequest = LabRequest::create([
            'encounter_id'       => $encounter->id,
            'patient_id'         => $patient->id,
            'screening_record_id'=> $screeningRecord->id,
            'requested_by'       => $user->id,
            'request_number'     => 'LAB-20260419-0001',
            'status'             => 'completed',
            'requested_at'       => now(),
            'completed_at'       => now(),
        ]);

        LabResult::create([
            'lab_request_id'     => $labRequest->id,
            'encounter_id'       => $encounter->id,
            'patient_id'         => $patient->id,
            'recorded_by'        => $user->id,
            'result_value'       => '++ Plasmodium falciparum',
            'interpretation'     => 'abnormal',
            'result_status'      => 'resulted',
            'result_recorded_at' => now(),
        ]);

        return $encounter;
    }

    private function makeEncounterAtScreeningReviewInProgress(User $user): Encounter
    {
        $encounter = $this->makeEncounterAtScreeningReviewQueued($user);
        $this->post(route('screening-review.receive', $encounter));
        $encounter->refresh();
        return $encounter;
    }

    private function completeReview(Encounter $encounter, array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('screening-review.complete', $encounter), array_merge([
            'final_diagnosis'  => 'Malaria (confirmed)',
            'assessment_notes' => 'Blood smear positive for P. falciparum',
            'plan'             => 'Treat with ACT',
            'review_notes'     => 'Follow up in 3 days',
            'items' => [
                [
                    'drug_name'          => 'Artemether-Lumefantrine',
                    'strength'           => '20/120mg',
                    'formulation'        => 'Tablet',
                    'dose'               => '4 tablets',
                    'frequency'          => 'BD',
                    'duration'           => '3 days',
                    'quantity_prescribed'=> 24,
                    'route'              => 'Oral',
                    'instructions'       => 'Take with food',
                ],
            ],
        ], $overrides));
    }

    // ─── 1. Patient queued from lab can be received ───────────────────────────

    public function test_patient_queued_from_lab_can_be_received_in_screening_review(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningReviewQueued($clinician);

        $response = $this->post(route('screening-review.receive', $encounter));

        $response->assertRedirect(route('screening-review.show', $encounter));
        $response->assertSessionHas('success');

        $encounter->refresh();
        $this->assertSame(EncounterStage::ScreeningReview, $encounter->current_stage);
        $this->assertSame(EncounterStatus::InProgress, $encounter->current_status);
    }

    // ─── 2. Queue transition is marked received ───────────────────────────────

    public function test_screening_review_queue_transition_is_marked_received(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningReviewQueued($clinician);

        $this->post(route('screening-review.receive', $encounter));

        $this->assertDatabaseHas('encounter_queue_transitions', [
            'encounter_id' => $encounter->id,
            'from_stage'   => EncounterStage::Lab->value,
            'to_stage'     => EncounterStage::ScreeningReview->value,
            'status'       => QueueTransitionStatus::Received->value,
        ]);
    }

    // ─── 3. Post-lab screening review record is saved ─────────────────────────

    public function test_post_lab_screening_review_record_is_saved(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningReviewInProgress($clinician);

        $this->completeReview($encounter, [
            'final_diagnosis'  => 'Confirmed P. falciparum Malaria',
            'assessment_notes' => 'Blood smear +++ ring forms',
        ]);

        $this->assertDatabaseHas('screening_records', [
            'encounter_id'   => $encounter->id,
            'screening_type' => 'review_after_lab',
            'final_diagnosis'=> 'Confirmed P. falciparum Malaria',
        ]);
    }

    // ─── 4. Prescription and items are saved ──────────────────────────────────

    public function test_prescription_and_items_are_saved(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningReviewInProgress($clinician);

        $this->completeReview($encounter);

        $encounter->refresh();
        $this->assertNotNull($encounter->prescription);

        $prescription = $encounter->prescription;
        $this->assertStringStartsWith('RX-', $prescription->prescription_number);
        $this->assertEquals('active', $prescription->status);

        $this->assertDatabaseHas('pharmacy_prescription_items', [
            'pharmacy_prescription_id' => $prescription->id,
            'drug_name'                => 'Artemether-Lumefantrine',
            'quantity_prescribed'      => 24,
        ]);

        $this->assertEquals(1, $prescription->fresh()->items()->count());
    }

    // ─── 5. Encounter is queued to pharmacy ───────────────────────────────────

    public function test_encounter_is_queued_to_pharmacy(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningReviewInProgress($clinician);

        $response = $this->completeReview($encounter);

        $response->assertRedirect(route('screening-review.queue'));
        $response->assertSessionHas('success');

        $encounter->refresh();
        $this->assertSame(EncounterStage::Pharmacy, $encounter->current_stage);
        $this->assertSame(EncounterStatus::Queued, $encounter->current_status);
    }

    // ─── 6. pharmacy queue transition is created ──────────────────────────────

    public function test_pharmacy_queue_transition_is_created(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningReviewInProgress($clinician);

        $this->completeReview($encounter);

        $this->assertDatabaseHas('encounter_queue_transitions', [
            'encounter_id' => $encounter->id,
            'from_stage'   => EncounterStage::ScreeningReview->value,
            'to_stage'     => EncounterStage::Pharmacy->value,
        ]);
    }

    // ─── 7. Screening review stage log is opened and closed ───────────────────

    public function test_screening_review_stage_log_is_opened_and_closed(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningReviewQueued($clinician);

        $this->post(route('screening-review.receive', $encounter));

        $this->assertDatabaseHas('encounter_stage_logs', [
            'encounter_id' => $encounter->id,
            'stage_name'   => EncounterStage::ScreeningReview->value,
        ]);

        $encounter->refresh();
        $openLog = $encounter->stageLogs()
            ->where('stage_name', EncounterStage::ScreeningReview->value)
            ->whereNull('completed_at')
            ->first();
        $this->assertNotNull($openLog, 'Expected an open screening review stage log');

        $this->completeReview($encounter);

        $encounter->refresh();
        $stillOpen = $encounter->stageLogs()
            ->where('stage_name', EncounterStage::ScreeningReview->value)
            ->whereNull('completed_at')
            ->first();
        $this->assertNull($stillOpen, 'Stage log should be closed after completion');
    }

    // ─── 8. Audit entries are written ─────────────────────────────────────────

    public function test_audit_entries_are_written_for_screening_review(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningReviewInProgress($clinician);

        $this->completeReview($encounter);

        $this->assertDatabaseHas('encounter_audits', [
            'encounter_id' => $encounter->id,
            'action_name'  => 'screening_review_received',
        ]);

        $this->assertDatabaseHas('encounter_audits', [
            'encounter_id' => $encounter->id,
            'action_name'  => 'queued_to_pharmacy',
        ]);
    }

    // ─── 9. Screening review queue page loads ─────────────────────────────────

    public function test_screening_review_queue_page_loads(): void
    {
        $this->actingAsClinician();

        $response = $this->get(route('screening-review.queue'));

        $response->assertOk();
        $response->assertViewIs('screening-review.queue');
    }

    // ─── 10. Review without final diagnosis is rejected ───────────────────────

    public function test_review_without_final_diagnosis_is_rejected(): void
    {
        $clinician = $this->actingAsClinician();
        $encounter = $this->makeEncounterAtScreeningReviewInProgress($clinician);

        $response = $this->post(route('screening-review.complete', $encounter), [
            'items' => [
                ['drug_name' => 'Paracetamol', 'dose' => '1g', 'frequency' => 'TDS', 'duration' => '3 days', 'quantity_prescribed' => 9],
            ],
        ]);

        $response->assertSessionHasErrors('final_diagnosis');
    }
}
