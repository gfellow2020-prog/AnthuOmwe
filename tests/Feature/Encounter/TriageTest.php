<?php

namespace Tests\Feature\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Enums\QueueTransitionStatus;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\TriageRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriageTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function actingAsNurse(): User
    {
        $user = User::factory()->create(['name' => 'Test Nurse']);
        $this->actingAs($user);
        return $user;
    }

    private function makeEncounterAtTriageQueued(User $user): Encounter
    {
        $patient = Patient::create([
            'patient_id'   => 'P00000010',
            'full_name'    => 'Mary Phiri',
            'gender'       => 'female',
            'date_of_birth'=> '1985-07-20',
        ]);

        // Create encounter already at triage/queued (simulates registration hand-off)
        $encounter = Encounter::create([
            'patient_id'       => $patient->id,
            'encounter_number' => 'ENC-20260419-00001',
            'visit_type'       => 'OPD',
            'priority_level'   => 'normal',
            'current_stage'    => EncounterStage::Triage,
            'current_status'   => EncounterStatus::Queued,
            'started_by'       => $user->id,
            'started_at'       => now(),
            'is_locked'        => false,
        ]);

        // Triage queue transition
        $encounter->queueTransitions()->create([
            'patient_id'  => $patient->id,
            'from_stage'  => EncounterStage::Registration->value,
            'to_stage'    => EncounterStage::Triage->value,
            'status'      => QueueTransitionStatus::Queued->value,
            'queued_by'   => $user->id,
            'queued_at'   => now(),
        ]);

        return $encounter;
    }

    private function makeEncounterAtTriageInProgress(User $user): Encounter
    {
        $encounter = $this->makeEncounterAtTriageQueued($user);

        // Receive it
        $this->post(route('triage.receive', $encounter));
        $encounter->refresh();

        return $encounter;
    }

    // ─── 1. Queued triage patient can be received ─────────────────────────────

    public function test_queued_triage_patient_can_be_received(): void
    {
        $nurse    = $this->actingAsNurse();
        $encounter = $this->makeEncounterAtTriageQueued($nurse);

        $response = $this->post(route('triage.receive', $encounter));

        $response->assertRedirect(route('triage.show', $encounter));
        $response->assertSessionHas('success');

        $encounter->refresh();
        $this->assertSame(EncounterStage::Triage, $encounter->current_stage);
        $this->assertSame(EncounterStatus::InProgress, $encounter->current_status);
    }

    // ─── 2. Triage queue transition is marked received ───────────────────────

    public function test_triage_queue_transition_is_marked_received(): void
    {
        $nurse     = $this->actingAsNurse();
        $encounter = $this->makeEncounterAtTriageQueued($nurse);

        $this->post(route('triage.receive', $encounter));

        $this->assertDatabaseHas('encounter_queue_transitions', [
            'encounter_id' => $encounter->id,
            'from_stage'   => EncounterStage::Registration->value,
            'to_stage'     => EncounterStage::Triage->value,
            'status'       => QueueTransitionStatus::Received->value,
        ]);
    }

    // ─── 3. Stage log is opened on receive ────────────────────────────────────

    public function test_triage_stage_log_is_opened_on_receive(): void
    {
        $nurse     = $this->actingAsNurse();
        $encounter = $this->makeEncounterAtTriageQueued($nurse);

        $this->post(route('triage.receive', $encounter));

        $this->assertDatabaseHas('encounter_stage_logs', [
            'encounter_id' => $encounter->id,
            'stage_name'   => EncounterStage::Triage->value,
            'status'       => QueueTransitionStatus::Received->value,
        ]);

        // completed_at should be null (still open)
        $log = $encounter->stageLogs()
            ->where('stage_name', EncounterStage::Triage->value)
            ->whereNull('completed_at')
            ->first();
        $this->assertNotNull($log);
    }

    // ─── 4. Vitals are saved correctly ────────────────────────────────────────

    public function test_vitals_are_saved_correctly(): void
    {
        $nurse     = $this->actingAsNurse();
        $encounter = $this->makeEncounterAtTriageInProgress($nurse);

        $response = $this->post(route('triage.complete', $encounter), [
            'weight'              => 68.5,
            'height'              => 165.0,
            'temperature'         => 36.8,
            'pulse'               => 72,
            'respiratory_rate'    => 18,
            'systolic_bp'         => 120,
            'diastolic_bp'        => 80,
            'oxygen_saturation'   => 98.5,
            'blood_sugar'         => 5.5,
            'pain_scale'          => 2,
            'chief_complaint_brief' => 'Headache and fever',
        ]);

        $response->assertRedirect(route('triage.queue'));

        $record = TriageRecord::where('encounter_id', $encounter->id)->first();
        $this->assertNotNull($record);
        $this->assertEqualsWithDelta(68.5, $record->weight, 0.01);
        $this->assertEqualsWithDelta(165.0, $record->height, 0.01);
        $this->assertEqualsWithDelta(36.8, $record->temperature, 0.01);
        $this->assertSame(72, (int) $record->pulse);
        $this->assertSame(2, (int) $record->pain_scale);
        $this->assertSame('Headache and fever', $record->chief_complaint_brief);
    }

    // ─── 5. BMI is computed automatically ────────────────────────────────────

    public function test_bmi_is_computed_automatically_from_weight_and_height(): void
    {
        $nurse     = $this->actingAsNurse();
        $encounter = $this->makeEncounterAtTriageInProgress($nurse);

        $this->post(route('triage.complete', $encounter), [
            'weight' => 70.0,
            'height' => 175.0,
        ]);

        $record = TriageRecord::where('encounter_id', $encounter->id)->first();
        $expectedBmi = round(70.0 / ((175.0 / 100) ** 2), 1);
        $this->assertEqualsWithDelta($expectedBmi, (float) $record->bmi, 0.1);
    }

    // ─── 6. Startup medication notes are saved ───────────────────────────────

    public function test_startup_medication_notes_are_saved(): void
    {
        $nurse     = $this->actingAsNurse();
        $encounter = $this->makeEncounterAtTriageInProgress($nurse);

        $this->post(route('triage.complete', $encounter), [
            'startup_medications_notes'   => 'Paracetamol 1g given orally.',
            'startup_interventions_notes' => 'IV line inserted.',
        ]);

        $this->assertDatabaseHas('triage_records', [
            'encounter_id'                => $encounter->id,
            'startup_medications_notes'   => 'Paracetamol 1g given orally.',
            'startup_interventions_notes' => 'IV line inserted.',
        ]);
    }

    // ─── 7. Encounter is queued to screening after triage complete ────────────

    public function test_encounter_is_queued_to_screening_after_triage_complete(): void
    {
        $nurse     = $this->actingAsNurse();
        $encounter = $this->makeEncounterAtTriageInProgress($nurse);

        $this->post(route('triage.complete', $encounter), [
            'weight' => 60.0,
            'height' => 160.0,
        ]);

        $encounter->refresh();
        $this->assertSame(EncounterStage::Screening, $encounter->current_stage);
        $this->assertSame(EncounterStatus::Queued, $encounter->current_status);
    }

    // ─── 8. Queue transition to screening is created ─────────────────────────

    public function test_queue_transition_to_screening_is_created(): void
    {
        $nurse     = $this->actingAsNurse();
        $encounter = $this->makeEncounterAtTriageInProgress($nurse);

        $this->post(route('triage.complete', $encounter), [
            'weight' => 60.0,
        ]);

        $this->assertDatabaseHas('encounter_queue_transitions', [
            'encounter_id' => $encounter->id,
            'from_stage'   => EncounterStage::Triage->value,
            'to_stage'     => EncounterStage::Screening->value,
            'status'       => QueueTransitionStatus::Queued->value,
        ]);
    }

    // ─── 9. Triage stage log is closed on complete ────────────────────────────

    public function test_triage_stage_log_is_closed_on_complete(): void
    {
        $nurse     = $this->actingAsNurse();
        $encounter = $this->makeEncounterAtTriageInProgress($nurse);

        $this->post(route('triage.complete', $encounter), ['weight' => 55.0]);

        $log = $encounter->stageLogs()
            ->where('stage_name', EncounterStage::Triage->value)
            ->whereNotNull('completed_at')
            ->first();

        $this->assertNotNull($log);
    }

    // ─── 10. Audit is written for triage events ───────────────────────────────

    public function test_audit_log_is_written_for_triage_events(): void
    {
        $nurse     = $this->actingAsNurse();
        $encounter = $this->makeEncounterAtTriageQueued($nurse);

        // Receive
        $this->post(route('triage.receive', $encounter));

        $this->assertDatabaseHas('encounter_audits', [
            'encounter_id' => $encounter->id,
            'action_name'  => 'triage_received',
            'action_stage' => EncounterStage::Triage->value,
        ]);

        // Complete
        $encounter->refresh();
        $this->post(route('triage.complete', $encounter), ['weight' => 58.0]);

        $this->assertDatabaseHas('encounter_audits', [
            'encounter_id' => $encounter->id,
            'action_name'  => 'queued_to_screening',
            'action_stage' => EncounterStage::Triage->value,
        ]);
    }
}
