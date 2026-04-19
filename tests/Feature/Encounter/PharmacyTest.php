<?php

namespace Tests\Feature\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Enums\QueueTransitionStatus;
use App\Exceptions\Encounter\EncounterLockedException;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\PharmacyPrescription;
use App\Models\PharmacyPrescriptionItem;
use App\Models\ScreeningRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function actingAsPharmacist(): User
    {
        $user = User::factory()->create(['name' => 'Pharmacist User']);
        $this->actingAs($user);
        return $user;
    }

    /**
     * Build an encounter at stage=pharmacy, status=queued, with a prescription + items.
     */
    private function makeEncounterAtPharmacyQueued(User $user): Encounter
    {
        $patient = Patient::create([
            'patient_id'    => 'P00000060',
            'full_name'     => 'Margaret Zulu',
            'gender'        => 'female',
            'date_of_birth' => '1985-07-14',
        ]);

        $encounter = Encounter::create([
            'patient_id'       => $patient->id,
            'encounter_number' => 'ENC-20260419-00060',
            'visit_type'       => 'OPD',
            'priority_level'   => 'normal',
            'current_stage'    => EncounterStage::Pharmacy,
            'current_status'   => EncounterStatus::Queued,
            'started_by'       => $user->id,
            'started_at'       => now(),
            'is_locked'        => false,
        ]);

        // Incoming queue transition (screening_review → pharmacy)
        $encounter->queueTransitions()->create([
            'patient_id' => $patient->id,
            'from_stage' => EncounterStage::ScreeningReview->value,
            'to_stage'   => EncounterStage::Pharmacy->value,
            'status'     => QueueTransitionStatus::Queued->value,
            'queued_by'  => $user->id,
            'queued_at'  => now(),
        ]);

        // Initial screening record (clinician_id NOT NULL)
        $screeningRecord = ScreeningRecord::create([
            'encounter_id'    => $encounter->id,
            'patient_id'      => $patient->id,
            'clinician_id'    => $user->id,
            'screening_type'  => 'initial',
            'lab_requested'   => true,
        ]);

        // Prescription
        $prescription = PharmacyPrescription::create([
            'encounter_id'        => $encounter->id,
            'patient_id'          => $patient->id,
            'screening_record_id' => $screeningRecord->id,
            'prescribed_by'       => $user->id,
            'prescription_number' => 'RX-20260419-0001',
            'status'              => 'active',
            'prescribed_at'       => now(),
        ]);

        PharmacyPrescriptionItem::create([
            'pharmacy_prescription_id' => $prescription->id,
            'drug_name'                => 'Artemether-Lumefantrine',
            'dose'                     => '4 tablets',
            'frequency'                => 'BD',
            'duration'                 => '3 days',
            'quantity_prescribed'      => 24,
        ]);

        return $encounter;
    }

    private function makeEncounterAtPharmacyInProgress(User $user): Encounter
    {
        $encounter = $this->makeEncounterAtPharmacyQueued($user);
        $this->post(route('pharmacy.receive', $encounter));
        $encounter->refresh();
        return $encounter;
    }

    private function dispense(Encounter $encounter, array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('pharmacy.dispense', $encounter), array_merge([
            'dispensing_notes' => 'All medications dispensed',
            'counseling_notes' => 'Patient counseled on dosage',
            'items'            => [
                [
                    'drug_name'          => 'Artemether-Lumefantrine',
                    'quantity_dispensed' => 24,
                    'batch_no'           => 'BATCH-001',
                    'instructions'       => 'Take with food',
                ],
            ],
        ], $overrides));
    }

    // ─── Tests ────────────────────────────────────────────────────────────────

    public function test_pharmacy_can_receive_queued_encounter(): void
    {
        $user     = $this->actingAsPharmacist();
        $encounter = $this->makeEncounterAtPharmacyQueued($user);

        $response = $this->post(route('pharmacy.receive', $encounter));
        $response->assertRedirect(route('pharmacy.show', $encounter));

        $encounter->refresh();
        $this->assertEquals(EncounterStage::Pharmacy, $encounter->current_stage);
        $this->assertEquals(EncounterStatus::InProgress, $encounter->current_status);
    }

    public function test_receive_opens_pharmacy_stage_log(): void
    {
        $user      = $this->actingAsPharmacist();
        $encounter = $this->makeEncounterAtPharmacyQueued($user);

        $this->post(route('pharmacy.receive', $encounter));

        $this->assertDatabaseHas('encounter_stage_logs', [
            'encounter_id' => $encounter->id,
            'stage_name'   => EncounterStage::Pharmacy->value,
        ]);
    }

    public function test_pharmacy_queue_shows_queued_encounters(): void
    {
        $user      = $this->actingAsPharmacist();
        $encounter = $this->makeEncounterAtPharmacyQueued($user);

        $response = $this->get(route('pharmacy.queue'));
        $response->assertOk();
        $response->assertViewHas('encounters');
    }

    public function test_dispensed_items_are_saved(): void
    {
        $user      = $this->actingAsPharmacist();
        $encounter = $this->makeEncounterAtPharmacyInProgress($user);

        $response = $this->dispense($encounter);
        $response->assertRedirect(route('pharmacy.show', $encounter));

        $this->assertDatabaseHas('pharmacy_dispenses', [
            'encounter_id' => $encounter->id,
            'dispensed_by' => $user->id,
        ]);

        $this->assertDatabaseHas('pharmacy_dispense_items', [
            'drug_name'          => 'Artemether-Lumefantrine',
            'quantity_dispensed' => 24,
        ]);
    }

    public function test_dispense_requires_at_least_one_item(): void
    {
        $user      = $this->actingAsPharmacist();
        $encounter = $this->makeEncounterAtPharmacyInProgress($user);

        $response = $this->post(route('pharmacy.dispense', $encounter), [
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_encounter_closes_successfully(): void
    {
        $user      = $this->actingAsPharmacist();
        $encounter = $this->makeEncounterAtPharmacyInProgress($user);

        $this->dispense($encounter);
        $response = $this->post(route('pharmacy.close', $encounter));

        $response->assertRedirect(route('pharmacy.show', $encounter));

        $encounter->refresh();
        $this->assertEquals(EncounterStage::Completed, $encounter->current_stage);
        $this->assertEquals(EncounterStatus::Completed, $encounter->current_status);
    }

    public function test_closed_encounter_becomes_locked(): void
    {
        $user      = $this->actingAsPharmacist();
        $encounter = $this->makeEncounterAtPharmacyInProgress($user);

        $this->dispense($encounter);
        $this->post(route('pharmacy.close', $encounter));

        $encounter->refresh();
        $this->assertTrue((bool) $encounter->is_locked);
        $this->assertNotNull($encounter->closed_at);
        $this->assertEquals($user->id, $encounter->closed_by);
    }

    public function test_locked_encounter_cannot_be_edited(): void
    {
        $user      = $this->actingAsPharmacist();
        $encounter = $this->makeEncounterAtPharmacyInProgress($user);

        $this->dispense($encounter);
        $this->post(route('pharmacy.close', $encounter));

        $encounter->refresh();

        // Attempting to dispense again should throw EncounterLockedException
        $this->expectException(EncounterLockedException::class);

        app(\App\Actions\Encounter\DispenseMedicationAction::class)->handle(
            $encounter,
            [
                'items' => [
                    ['drug_name' => 'Paracetamol', 'quantity_dispensed' => 10],
                ],
            ],
            $user->id,
        );
    }

    public function test_cannot_close_without_dispense(): void
    {
        $user      = $this->actingAsPharmacist();
        $encounter = $this->makeEncounterAtPharmacyInProgress($user);

        // No dispense — close should fail with RuntimeException
        $this->expectException(\RuntimeException::class);

        app(\App\Actions\Encounter\CloseEncounterAction::class)
            ->handle($encounter, $user->id);
    }

    public function test_final_audit_log_exists_after_close(): void
    {
        $user      = $this->actingAsPharmacist();
        $encounter = $this->makeEncounterAtPharmacyInProgress($user);

        $this->dispense($encounter);
        $this->post(route('pharmacy.close', $encounter));

        $this->assertDatabaseHas('encounter_audits', [
            'encounter_id' => $encounter->id,
            'action_name'  => 'encounter_closed',
        ]);
    }

    public function test_prescription_marked_dispensed_after_dispense(): void
    {
        $user      = $this->actingAsPharmacist();
        $encounter = $this->makeEncounterAtPharmacyInProgress($user);

        $this->dispense($encounter);

        $this->assertDatabaseHas('pharmacy_prescriptions', [
            'encounter_id' => $encounter->id,
            'status'       => 'dispensed',
        ]);
    }
}
