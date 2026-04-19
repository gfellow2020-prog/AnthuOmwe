<?php

namespace Tests\Feature\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Enums\QueueTransitionStatus;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\RegistrationRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function actingAsRegistrar(): User
    {
        $user = User::factory()->create(['name' => 'Test Registrar']);
        $this->actingAs($user);
        return $user;
    }

    private function existingPatient(array $overrides = []): Patient
    {
        return Patient::create(array_merge([
            'patient_id'   => 'P00000001',
            'full_name'    => 'Jane Banda',
            'gender'       => 'female',
            'date_of_birth'=> '1990-05-15',
            'nrc_number'   => '123456/90/1',
            'phone_number' => '+260971234567',
        ], $overrides));
    }

    // ─── 1. Existing patient can start a new encounter ────────────────────────

    public function test_existing_patient_can_start_new_encounter(): void
    {
        $this->actingAsRegistrar();
        $patient = $this->existingPatient();

        $response = $this->post(route('encounters.start'), [
            'patient_id'     => $patient->id,
            'visit_type'     => 'OPD',
            'priority_level' => 'normal',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $encounter = Encounter::where('patient_id', $patient->id)->first();
        $this->assertNotNull($encounter);
        $this->assertSame(EncounterStage::Registration, $encounter->current_stage);
        $this->assertSame(EncounterStatus::Started, $encounter->current_status);
        $this->assertFalse($encounter->is_locked);
    }

    // ─── 2. New patient is created when no patient_id is supplied ────────────

    public function test_new_patient_is_created_when_not_found(): void
    {
        $this->actingAsRegistrar();

        $this->assertDatabaseMissing('patients', ['full_name' => 'Charles Mwale']);

        $response = $this->post(route('encounters.start'), [
            'full_name'      => 'Charles Mwale',
            'gender'         => 'male',
            'date_of_birth'  => '1985-03-22',
            'phone_number'   => '+260977654321',
            'visit_type'     => 'ART',
            'priority_level' => 'urgent',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('patients', ['full_name' => 'Charles Mwale']);
        $patient = Patient::where('full_name', 'Charles Mwale')->first();
        $this->assertNotNull($patient->patient_id); // barcode generated
    }

    // ─── 3. Registration record is saved ─────────────────────────────────────

    public function test_registration_record_is_saved(): void
    {
        $this->actingAsRegistrar();
        $patient = $this->existingPatient();

        $this->post(route('encounters.start'), [
            'patient_id'          => $patient->id,
            'registration_notes'  => 'Patient walked in.',
            'search_reference'    => 'Jane Banda',
        ]);

        $encounter = Encounter::where('patient_id', $patient->id)->first();
        $this->assertNotNull($encounter);

        $this->assertDatabaseHas('registration_records', [
            'encounter_id'        => $encounter->id,
            'patient_id'          => $patient->id,
            'was_existing_patient'=> 1,
            'registration_notes'  => 'Patient walked in.',
            'search_reference'    => 'Jane Banda',
        ]);
    }

    // ─── 4. Encounter is queued to triage ────────────────────────────────────

    public function test_encounter_is_queued_to_triage(): void
    {
        $registrar = $this->actingAsRegistrar();
        $patient   = $this->existingPatient();

        // Start encounter
        $this->post(route('encounters.start'), ['patient_id' => $patient->id]);
        $encounter = Encounter::where('patient_id', $patient->id)->first();

        // Queue to triage
        $response = $this->post(route('encounters.queue.triage', $encounter), [
            'notes' => 'Patient ready for triage',
        ]);

        $response->assertRedirect(route('registration.index'));
        $response->assertSessionHas('success');

        $encounter->refresh();
        $this->assertSame(EncounterStage::Triage, $encounter->current_stage);
        $this->assertSame(EncounterStatus::Queued, $encounter->current_status);
    }

    // ─── 5. Queue transition from registration → triage is saved ─────────────

    public function test_queue_transition_registration_to_triage_is_saved(): void
    {
        $this->actingAsRegistrar();
        $patient = $this->existingPatient();

        $this->post(route('encounters.start'), ['patient_id' => $patient->id]);
        $encounter = Encounter::where('patient_id', $patient->id)->first();

        $this->post(route('encounters.queue.triage', $encounter));

        $this->assertDatabaseHas('encounter_queue_transitions', [
            'encounter_id' => $encounter->id,
            'from_stage'   => 'registration',
            'to_stage'     => 'triage',
            'status'       => QueueTransitionStatus::Queued->value,
        ]);
    }

    // ─── 6. Audit log is written ──────────────────────────────────────────────

    public function test_audit_log_is_written_for_start_and_queue(): void
    {
        $this->actingAsRegistrar();
        $patient = $this->existingPatient();

        $this->post(route('encounters.start'), ['patient_id' => $patient->id]);
        $encounter = Encounter::where('patient_id', $patient->id)->first();

        $this->assertDatabaseHas('encounter_audits', [
            'encounter_id' => $encounter->id,
            'action_name'  => 'encounter_started',
            'action_stage' => 'registration',
        ]);

        $this->post(route('encounters.queue.triage', $encounter));

        $this->assertDatabaseHas('encounter_audits', [
            'encounter_id' => $encounter->id,
            'action_name'  => 'queued_to_triage',
            'action_stage' => 'registration',
        ]);
    }

    // ─── 7. Stage log is opened on start and closed on queue ─────────────────

    public function test_stage_log_is_written_correctly(): void
    {
        $this->actingAsRegistrar();
        $patient = $this->existingPatient();

        $this->post(route('encounters.start'), ['patient_id' => $patient->id]);
        $encounter = Encounter::where('patient_id', $patient->id)->first();

        // Stage log should be open (no completed_at)
        $this->assertDatabaseHas('encounter_stage_logs', [
            'encounter_id' => $encounter->id,
            'stage_name'   => 'registration',
            'status'       => QueueTransitionStatus::Received->value,
        ]);

        $this->post(route('encounters.queue.triage', $encounter));

        // Stage log should now be completed
        $this->assertDatabaseHas('encounter_stage_logs', [
            'encounter_id' => $encounter->id,
            'stage_name'   => 'registration',
            'status'       => QueueTransitionStatus::Completed->value,
        ]);
    }

    // ─── 8. Validation: full_name required when no patient_id ────────────────

    public function test_full_name_is_required_for_new_patient(): void
    {
        $this->actingAsRegistrar();

        $response = $this->post(route('encounters.start'), [
            // No patient_id and no full_name → should fail
            'gender' => 'male',
        ]);

        $response->assertSessionHasErrors('full_name');
    }

    // ─── 9. Cannot queue to triage if already queued ─────────────────────────

    public function test_cannot_queue_to_triage_twice(): void
    {
        $this->actingAsRegistrar();
        $patient = $this->existingPatient();

        $this->post(route('encounters.start'), ['patient_id' => $patient->id]);
        $encounter = Encounter::where('patient_id', $patient->id)->first();

        $this->post(route('encounters.queue.triage', $encounter));

        // Second queue attempt should fail (wrong stage)
        $this->expectException(\App\Exceptions\Encounter\InvalidEncounterStageException::class);
        $this->withoutExceptionHandling()
             ->post(route('encounters.queue.triage', $encounter));
    }

    // ─── 10. Patient search returns JSON results ──────────────────────────────

    public function test_patient_search_returns_json(): void
    {
        $this->actingAsRegistrar();
        $this->existingPatient(['full_name' => 'Jane Banda']);

        $response = $this->getJson(route('registration.search') . '?q=Jane');

        $response->assertOk()
                 ->assertJsonStructure(['patients', 'count'])
                 ->assertJsonPath('count', 1)
                 ->assertJsonPath('patients.0.full_name', 'Jane Banda');
    }
}
