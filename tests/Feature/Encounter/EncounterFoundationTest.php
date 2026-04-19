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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncounterFoundationTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function createPatient(array $overrides = []): Patient
    {
        return Patient::create(array_merge([
            'patient_id' => 'P00000001',
            'full_name'  => 'Jane Banda',
            'gender'     => 'female',
        ], $overrides));
    }

    private function createEncounter(Patient $patient, User $user, array $overrides = []): Encounter
    {
        return Encounter::create(array_merge([
            'encounter_number' => 'ENC-20260419-00001',
            'patient_id'       => $patient->id,
            'current_stage'    => EncounterStage::Registration,
            'current_status'   => EncounterStatus::Started,
            'started_at'       => now(),
            'started_by'       => $user->id,
        ], $overrides));
    }

    // ─── Tests ────────────────────────────────────────────────────────────────

    public function test_encounter_can_be_created(): void
    {
        $user    = User::factory()->create();
        $patient = $this->createPatient();

        $encounter = $this->createEncounter($patient, $user);

        $this->assertDatabaseHas('encounters', [
            'encounter_number' => 'ENC-20260419-00001',
            'patient_id'       => $patient->id,
        ]);
        $this->assertNotNull($encounter->id);
    }

    public function test_encounter_starts_unlocked(): void
    {
        $user    = User::factory()->create();
        $patient = $this->createPatient();

        $encounter = $this->createEncounter($patient, $user);

        $this->assertFalse($encounter->is_locked);
        $this->assertFalse($encounter->isLocked());
    }

    public function test_encounter_has_current_stage_registration(): void
    {
        $user    = User::factory()->create();
        $patient = $this->createPatient();

        $encounter = $this->createEncounter($patient, $user);

        $this->assertSame(EncounterStage::Registration, $encounter->current_stage);
        $this->assertSame('registration', $encounter->current_stage->value);
    }

    public function test_encounter_has_status_started_on_creation(): void
    {
        $user    = User::factory()->create();
        $patient = $this->createPatient();

        $encounter = $this->createEncounter($patient, $user);

        $this->assertSame(EncounterStatus::Started, $encounter->current_status);
    }

    public function test_encounter_stage_log_can_be_written(): void
    {
        $user    = User::factory()->create();
        $patient = $this->createPatient();
        $encounter = $this->createEncounter($patient, $user);

        $log = EncounterStageLog::create([
            'encounter_id'   => $encounter->id,
            'patient_id'     => $patient->id,
            'stage_name'     => EncounterStage::Registration->value,
            'stage_sequence' => EncounterStage::Registration->sequence(),
            'status'         => QueueTransitionStatus::Received->value,
            'started_by'     => $user->id,
            'started_at'     => now(),
        ]);

        $this->assertDatabaseHas('encounter_stage_logs', [
            'encounter_id' => $encounter->id,
            'stage_name'   => 'registration',
        ]);
        $this->assertSame(QueueTransitionStatus::Received, $log->status);
    }

    public function test_queue_transition_can_be_written(): void
    {
        $user    = User::factory()->create();
        $patient = $this->createPatient();
        $encounter = $this->createEncounter($patient, $user);

        $transition = EncounterQueueTransition::create([
            'encounter_id' => $encounter->id,
            'patient_id'   => $patient->id,
            'from_stage'   => EncounterStage::Registration->value,
            'to_stage'     => EncounterStage::Triage->value,
            'queued_by'    => $user->id,
            'queued_at'    => now(),
            'status'       => QueueTransitionStatus::Queued->value,
        ]);

        $this->assertDatabaseHas('encounter_queue_transitions', [
            'encounter_id' => $encounter->id,
            'from_stage'   => 'registration',
            'to_stage'     => 'triage',
        ]);
        $this->assertSame(QueueTransitionStatus::Queued, $transition->status);
    }

    public function test_encounter_audit_can_be_written(): void
    {
        $user    = User::factory()->create();
        $patient = $this->createPatient();
        $encounter = $this->createEncounter($patient, $user);

        EncounterAudit::create([
            'encounter_id' => $encounter->id,
            'patient_id'   => $patient->id,
            'action_name'  => 'encounter_started',
            'action_stage' => EncounterStage::Registration->value,
            'action_by'    => $user->id,
            'new_values'   => ['encounter_number' => $encounter->encounter_number],
            'action_at'    => now(),
        ]);

        $this->assertDatabaseHas('encounter_audits', [
            'encounter_id' => $encounter->id,
            'action_name'  => 'encounter_started',
        ]);
    }

    public function test_encounter_relationships_load_correctly(): void
    {
        $user    = User::factory()->create();
        $patient = $this->createPatient();
        $encounter = $this->createEncounter($patient, $user);

        EncounterStageLog::create([
            'encounter_id'   => $encounter->id,
            'patient_id'     => $patient->id,
            'stage_name'     => 'registration',
            'stage_sequence' => 1,
            'status'         => 'received',
            'started_by'     => $user->id,
            'started_at'     => now(),
        ]);

        EncounterQueueTransition::create([
            'encounter_id' => $encounter->id,
            'patient_id'   => $patient->id,
            'from_stage'   => 'registration',
            'to_stage'     => 'triage',
            'queued_by'    => $user->id,
            'queued_at'    => now(),
            'status'       => 'queued',
        ]);

        $loaded = Encounter::with(['patient', 'stageLogs', 'queueTransitions', 'audits'])->find($encounter->id);

        $this->assertNotNull($loaded->patient);
        $this->assertSame('Jane Banda', $loaded->patient->full_name);
        $this->assertCount(1, $loaded->stageLogs);
        $this->assertCount(1, $loaded->queueTransitions);
    }

    public function test_patient_has_many_encounters(): void
    {
        $user    = User::factory()->create();
        $patient = $this->createPatient();

        Encounter::create([
            'encounter_number' => 'ENC-20260419-00001',
            'patient_id'       => $patient->id,
            'current_stage'    => EncounterStage::Registration,
            'current_status'   => EncounterStatus::Started,
            'started_at'       => now(),
            'started_by'       => $user->id,
        ]);

        Encounter::create([
            'encounter_number' => 'ENC-20260419-00002',
            'patient_id'       => $patient->id,
            'current_stage'    => EncounterStage::Registration,
            'current_status'   => EncounterStatus::Started,
            'started_at'       => now()->addDay(),
            'started_by'       => $user->id,
        ]);

        $this->assertCount(2, $patient->encounters);
    }
}
