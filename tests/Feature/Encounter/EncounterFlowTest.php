<?php

namespace Tests\Feature\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Exceptions\Encounter\EncounterLockedException;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 8 — End-to-End Integration Tests
 *
 * Covers two complete patient journeys through the entire pipeline:
 *   1. Full path with lab (registration → triage → screening → lab → screening review → pharmacy → close)
 *   2. Direct path without lab (registration → triage → screening → pharmacy → close)
 */
class EncounterFlowTest extends TestCase
{
    use RefreshDatabase;

    // ─── Shared helpers ───────────────────────────────────────────────────────

    private function makeUser(string $name = 'Staff'): User
    {
        $user = User::factory()->create(['name' => $name]);
        $this->actingAs($user);
        return $user;
    }

    private function startEncounter(User $user): Encounter
    {
        // Create a new patient and start an encounter through the registration endpoint
        $this->post(route('encounters.start'), [
            'full_name'      => 'Integration Patient',
            'gender'         => 'female',
            'date_of_birth'  => '1985-06-15',
            'phone_number'   => '+260977000001',
            'visit_type'     => 'OPD',
            'priority_level' => 'normal',
        ]);

        return Encounter::with('patient')
            ->where('current_stage', EncounterStage::Registration)
            ->latest()
            ->firstOrFail();
    }

    private function triagePayload(): array
    {
        return [
            'weight'                => 65.0,
            'height'                => 168.0,
            'temperature'           => 37.8,
            'pulse'                 => 88,
            'respiratory_rate'      => 18,
            'systolic_bp'           => 118,
            'diastolic_bp'          => 76,
            'oxygen_saturation'     => 97.5,
            'chief_complaint_brief' => 'Fever and body aches',
        ];
    }

    private function screeningPayload(bool $labRequested): array
    {
        return [
            'complaints'            => 'Fever for 3 days',
            'provisional_diagnosis' => 'Probable malaria',
            'plan'                  => $labRequested ? 'Request blood smear' : 'Treat empirically',
            'lab_requested'         => $labRequested,
        ];
    }

    // ─── Full path with lab ────────────────────────────────────────────────────

    public function test_full_path_with_lab_registration_through_closure(): void
    {
        $user = $this->makeUser('Full Path Staff');

        // ── Step 1: Registration ────────────────────────────────────────────
        $encounter = $this->startEncounter($user);

        $this->assertEquals(EncounterStage::Registration, $encounter->current_stage);
        $this->assertEquals(EncounterStatus::Started, $encounter->current_status);
        $this->assertFalse($encounter->is_locked);

        // ── Step 2: Queue to triage ─────────────────────────────────────────
        $this->post(route('encounters.queue.triage', $encounter))
             ->assertRedirect();

        $encounter->refresh();
        $this->assertEquals(EncounterStage::Triage, $encounter->current_stage);
        $this->assertEquals(EncounterStatus::Queued, $encounter->current_status);

        // ── Step 3: Triage receive ──────────────────────────────────────────
        $this->post(route('triage.receive', $encounter));

        $encounter->refresh();
        $this->assertEquals(EncounterStatus::InProgress, $encounter->current_status);

        // ── Step 4: Triage complete ─────────────────────────────────────────
        $this->post(route('triage.complete', $encounter), $this->triagePayload())
             ->assertRedirect();

        $encounter->refresh();
        $this->assertEquals(EncounterStage::Screening, $encounter->current_stage);
        $this->assertEquals(EncounterStatus::Queued, $encounter->current_status);

        $this->assertDatabaseHas('triage_records', [
            'encounter_id' => $encounter->id,
            'temperature'  => 37.8,
        ]);

        // ── Step 5: Screening receive ───────────────────────────────────────
        $this->post(route('screening.receive', $encounter));

        $encounter->refresh();
        $this->assertEquals(EncounterStatus::InProgress, $encounter->current_status);

        // ── Step 6: Screening complete — request lab ────────────────────────
        $this->post(route('screening.complete', $encounter), $this->screeningPayload(labRequested: true))
             ->assertRedirect();

        $encounter->refresh();
        $this->assertEquals(EncounterStage::Lab, $encounter->current_stage);
        $this->assertEquals(EncounterStatus::Queued, $encounter->current_status);

        $this->assertDatabaseHas('screening_records', [
            'encounter_id'  => $encounter->id,
            'lab_requested' => 1,
            'screening_type'=> 'initial',
        ]);

        // ── Step 7: Lab receive ─────────────────────────────────────────────
        $this->post(route('lab.receive', $encounter));

        $encounter->refresh();
        $this->assertEquals(EncounterStatus::InProgress, $encounter->current_status);

        $this->assertDatabaseHas('lab_requests', [
            'encounter_id' => $encounter->id,
        ]);

        // ── Step 8: Record lab samples ──────────────────────────────────────
        $this->post(route('lab.samples', $encounter), [
            'samples' => [
                ['sample_type' => 'Blood', 'sample_label' => 'SMEAR-001'],
            ],
        ])->assertRedirect();

        // ── Step 9 & 10: Record results AND complete lab (combined endpoint) ────
        $this->post(route('lab.complete', $encounter), [
            'results' => [
                [
                    'result_value'   => '++ Plasmodium falciparum',
                    'interpretation' => 'abnormal',
                    'result_text'    => 'Positive for malaria',
                ],
            ],
        ])->assertRedirect();

        $encounter->refresh();
        $this->assertEquals(EncounterStage::ScreeningReview, $encounter->current_stage);
        $this->assertEquals(EncounterStatus::Queued, $encounter->current_status);

        $this->assertDatabaseHas('lab_results', [
            'encounter_id'   => $encounter->id,
            'interpretation' => 'abnormal',
        ]);


        // ── Step 11: Screening review receive ───────────────────────────────
        $this->post(route('screening-review.receive', $encounter));

        $encounter->refresh();
        $this->assertEquals(EncounterStatus::InProgress, $encounter->current_status);

        // ── Step 12: Screening review complete — create prescription ────────
        $this->post(route('screening-review.complete', $encounter), [
            'final_diagnosis'  => 'Malaria (P. falciparum confirmed)',
            'assessment_notes' => 'Blood smear strongly positive',
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
                ],
            ],
        ])->assertRedirect();

        $encounter->refresh();
        $this->assertEquals(EncounterStage::Pharmacy, $encounter->current_stage);
        $this->assertEquals(EncounterStatus::Queued, $encounter->current_status);

        $this->assertDatabaseHas('screening_records', [
            'encounter_id'    => $encounter->id,
            'screening_type'  => 'review_after_lab',
            'final_diagnosis' => 'Malaria (P. falciparum confirmed)',
        ]);

        $this->assertDatabaseHas('pharmacy_prescriptions', [
            'encounter_id' => $encounter->id,
        ]);

        // ── Step 13: Pharmacy receive ───────────────────────────────────────
        $this->post(route('pharmacy.receive', $encounter));

        $encounter->refresh();
        $this->assertEquals(EncounterStatus::InProgress, $encounter->current_status);

        // ── Step 14: Dispense medication ────────────────────────────────────
        $this->post(route('pharmacy.dispense', $encounter), [
            'dispensing_notes' => 'All medications dispensed',
            'counseling_notes' => 'Take with food, complete course',
            'items' => [
                [
                    'drug_name'          => 'Artemether-Lumefantrine',
                    'quantity_dispensed' => 24,
                    'batch_no'           => 'BATCH-E2E-001',
                    'instructions'       => 'Take twice daily with food',
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('pharmacy_dispenses', [
            'encounter_id' => $encounter->id,
        ]);

        $this->assertDatabaseHas('pharmacy_dispense_items', [
            'drug_name' => 'Artemether-Lumefantrine',
        ]);

        // ── Step 15: Close encounter ────────────────────────────────────────
        $this->post(route('pharmacy.close', $encounter))->assertRedirect();

        $encounter->refresh();
        $this->assertEquals(EncounterStage::Completed, $encounter->current_stage);
        $this->assertEquals(EncounterStatus::Completed, $encounter->current_status);
        $this->assertTrue((bool) $encounter->is_locked);
        $this->assertNotNull($encounter->closed_at);
    }

    public function test_full_path_every_queue_transition_exists(): void
    {
        $user      = $this->makeUser('Transition Staff');
        $encounter = $this->startEncounter($user);

        $this->post(route('encounters.queue.triage', $encounter));
        $this->post(route('triage.receive', $encounter));
        $this->post(route('triage.complete', $encounter), $this->triagePayload());

        $this->post(route('screening.receive', $encounter->fresh()));
        $this->post(route('screening.complete', $encounter->fresh()), $this->screeningPayload(labRequested: true));

        $encounter->refresh();
        $this->post(route('lab.receive', $encounter));
        $this->post(route('lab.samples', $encounter), ['samples' => [['sample_type' => 'Blood', 'sample_label' => 'X1']]]);
        $this->post(route('lab.complete', $encounter), ['results' => [['result_value' => 'Positive', 'interpretation' => 'abnormal']]]);

        $encounter->refresh();
        $this->post(route('screening-review.receive', $encounter));
        $this->post(route('screening-review.complete', $encounter), [
            'final_diagnosis' => 'Malaria',
            'items' => [['drug_name' => 'AL', 'dose' => '4 tabs', 'frequency' => 'BD', 'duration' => '3 days', 'quantity_prescribed' => 24]],
        ]);

        $encounter->refresh();
        $this->post(route('pharmacy.receive', $encounter));
        $this->post(route('pharmacy.dispense', $encounter), [
            'items' => [['drug_name' => 'AL', 'quantity_dispensed' => 24]],
        ]);
        $this->post(route('pharmacy.close', $encounter));

        // Every stage queue transition must exist
        $enc = $encounter->fresh()->load('queueTransitions');
        $stages = $enc->queueTransitions->pluck('to_stage')->unique()->sort()->values()->toArray();

        $this->assertContains(EncounterStage::Triage->value,          $stages);
        $this->assertContains(EncounterStage::Screening->value,       $stages);
        $this->assertContains(EncounterStage::Lab->value,             $stages);
        $this->assertContains(EncounterStage::ScreeningReview->value, $stages);
        $this->assertContains(EncounterStage::Pharmacy->value,        $stages);
    }

    public function test_full_path_every_stage_log_exists(): void
    {
        $user      = $this->makeUser('StageLog Staff');
        $encounter = $this->startEncounter($user);

        $this->post(route('encounters.queue.triage', $encounter));
        $this->post(route('triage.receive', $encounter));
        $this->post(route('triage.complete', $encounter), $this->triagePayload());

        $this->post(route('screening.receive', $encounter->fresh()));
        $this->post(route('screening.complete', $encounter->fresh()), $this->screeningPayload(labRequested: true));

        $encounter->refresh();
        $this->post(route('lab.receive', $encounter));
        $this->post(route('lab.samples', $encounter), ['samples' => [['sample_type' => 'Blood', 'sample_label' => 'Y1']]]);
        $this->post(route('lab.complete', $encounter), ['results' => [['result_value' => 'Positive', 'interpretation' => 'abnormal']]]);

        $encounter->refresh();
        $this->post(route('screening-review.receive', $encounter));
        $this->post(route('screening-review.complete', $encounter), [
            'final_diagnosis' => 'Malaria',
            'items' => [['drug_name' => 'AL', 'dose' => '4 tabs', 'frequency' => 'BD', 'duration' => '3 days', 'quantity_prescribed' => 24]],
        ]);

        $encounter->refresh();
        $this->post(route('pharmacy.receive', $encounter));
        $this->post(route('pharmacy.dispense', $encounter), [
            'items' => [['drug_name' => 'AL', 'quantity_dispensed' => 24]],
        ]);
        $this->post(route('pharmacy.close', $encounter));

        $enc = $encounter->fresh()->load('stageLogs');
        $stages = $enc->stageLogs->pluck('stage_name')->unique()->sort()->values()->toArray();

        $this->assertContains(EncounterStage::Triage->value,          $stages);
        $this->assertContains(EncounterStage::Screening->value,       $stages);
        $this->assertContains(EncounterStage::Lab->value,             $stages);
        $this->assertContains(EncounterStage::ScreeningReview->value, $stages);
        $this->assertContains(EncounterStage::Pharmacy->value,        $stages);
    }

    public function test_full_path_audit_entries_exist(): void
    {
        $user      = $this->makeUser('Audit Staff');
        $encounter = $this->startEncounter($user);

        $this->post(route('encounters.queue.triage', $encounter));
        $this->post(route('triage.receive', $encounter));
        $this->post(route('triage.complete', $encounter), $this->triagePayload());

        $this->post(route('screening.receive', $encounter->fresh()));
        $this->post(route('screening.complete', $encounter->fresh()), $this->screeningPayload(labRequested: false));

        $encounter->refresh();
        $this->post(route('pharmacy.receive', $encounter));
        $this->post(route('pharmacy.dispense', $encounter), [
            'items' => [['drug_name' => 'Paracetamol', 'quantity_dispensed' => 15]],
        ]);
        $this->post(route('pharmacy.close', $encounter));

        $enc = $encounter->fresh()->load('audits');
        $actions = $enc->audits->pluck('action_name')->toArray();

        $this->assertContains('encounter_started', $actions);
        $this->assertContains('encounter_closed',  $actions);
    }

    public function test_full_path_locked_encounter_rejects_changes(): void
    {
        $user      = $this->makeUser('Lock Staff');
        $encounter = $this->startEncounter($user);

        // Run through direct path (no lab)
        $this->post(route('encounters.queue.triage', $encounter));
        $this->post(route('triage.receive', $encounter));
        $this->post(route('triage.complete', $encounter), $this->triagePayload());
        $this->post(route('screening.receive', $encounter->fresh()));
        $this->post(route('screening.complete', $encounter->fresh()), $this->screeningPayload(labRequested: false));

        $encounter->refresh();
        $this->post(route('pharmacy.receive', $encounter));
        $this->post(route('pharmacy.dispense', $encounter), [
            'items' => [['drug_name' => 'Paracetamol', 'quantity_dispensed' => 15]],
        ]);
        $this->post(route('pharmacy.close', $encounter));

        $encounter->refresh();
        $this->assertTrue((bool) $encounter->is_locked);

        // Any further write attempt should throw
        $this->expectException(EncounterLockedException::class);

        app(\App\Actions\Encounter\DispenseMedicationAction::class)->handle(
            $encounter,
            ['items' => [['drug_name' => 'Aspirin', 'quantity_dispensed' => 5]]],
            $user->id,
        );
    }

    // ─── Direct path without lab ──────────────────────────────────────────────

    public function test_direct_path_no_lab_completes_successfully(): void
    {
        $user = $this->makeUser('Direct Path Staff');

        // ── Step 1: Registration ────────────────────────────────────────────
        $encounter = $this->startEncounter($user);

        // ── Step 2: Queue to triage ─────────────────────────────────────────
        $this->post(route('encounters.queue.triage', $encounter));

        // ── Step 3 & 4: Triage ──────────────────────────────────────────────
        $this->post(route('triage.receive', $encounter));
        $this->post(route('triage.complete', $encounter), $this->triagePayload());

        // ── Step 5 & 6: Screening — no lab ──────────────────────────────────
        $encounter->refresh();
        $this->post(route('screening.receive', $encounter));
        $this->post(route('screening.complete', $encounter), $this->screeningPayload(labRequested: false));

        $encounter->refresh();
        $this->assertEquals(EncounterStage::Pharmacy, $encounter->current_stage);
        $this->assertEquals(EncounterStatus::Queued, $encounter->current_status);

        $this->assertDatabaseHas('screening_records', [
            'encounter_id'  => $encounter->id,
            'lab_requested' => 0,
        ]);

        // ── Step 7: Pharmacy receive ────────────────────────────────────────
        $this->post(route('pharmacy.receive', $encounter));

        $encounter->refresh();
        $this->assertEquals(EncounterStatus::InProgress, $encounter->current_status);

        // ── Step 8: Dispense ────────────────────────────────────────────────
        $this->post(route('pharmacy.dispense', $encounter), [
            'dispensing_notes' => 'Dispensed at counter',
            'items' => [
                ['drug_name' => 'Paracetamol 500mg', 'quantity_dispensed' => 15, 'instructions' => 'TDS after meals'],
                ['drug_name' => 'Amoxicillin 500mg', 'quantity_dispensed' => 21, 'batch_no' => 'AMX-202'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('pharmacy_dispense_items', ['drug_name' => 'Paracetamol 500mg']);
        $this->assertDatabaseHas('pharmacy_dispense_items', ['drug_name' => 'Amoxicillin 500mg']);

        // ── Step 9: Close ───────────────────────────────────────────────────
        $this->post(route('pharmacy.close', $encounter));

        $encounter->refresh();
        $this->assertEquals(EncounterStage::Completed, $encounter->current_stage);
        $this->assertEquals(EncounterStatus::Completed, $encounter->current_status);
        $this->assertTrue((bool) $encounter->is_locked);
        $this->assertNotNull($encounter->closed_at);
    }

    public function test_direct_path_no_lab_transitions_skip_lab_stage(): void
    {
        $user      = $this->makeUser('Skip Lab Staff');
        $encounter = $this->startEncounter($user);

        $this->post(route('encounters.queue.triage', $encounter));
        $this->post(route('triage.receive', $encounter));
        $this->post(route('triage.complete', $encounter), $this->triagePayload());

        $encounter->refresh();
        $this->post(route('screening.receive', $encounter));
        $this->post(route('screening.complete', $encounter), $this->screeningPayload(labRequested: false));

        $encounter->refresh();
        $this->post(route('pharmacy.receive', $encounter));
        $this->post(route('pharmacy.dispense', $encounter), [
            'items' => [['drug_name' => 'Metronidazole', 'quantity_dispensed' => 10]],
        ]);
        $this->post(route('pharmacy.close', $encounter));

        $enc = $encounter->fresh()->load('queueTransitions');
        $toStages = $enc->queueTransitions->pluck('to_stage')->toArray();

        // Lab stage must NOT appear in transitions
        $this->assertNotContains(EncounterStage::Lab->value, $toStages);
        // Pharmacy should be reached directly from screening
        $this->assertContains(EncounterStage::Pharmacy->value, $toStages);
    }

    public function test_direct_path_encounter_profile_loads_after_close(): void
    {
        $user      = $this->makeUser('Profile Staff');
        $encounter = $this->startEncounter($user);

        $this->post(route('encounters.queue.triage', $encounter));
        $this->post(route('triage.receive', $encounter));
        $this->post(route('triage.complete', $encounter), $this->triagePayload());

        $encounter->refresh();
        $this->post(route('screening.receive', $encounter));
        $this->post(route('screening.complete', $encounter), $this->screeningPayload(labRequested: false));

        $encounter->refresh();
        $this->post(route('pharmacy.receive', $encounter));
        $this->post(route('pharmacy.dispense', $encounter), [
            'items' => [['drug_name' => 'Zinc', 'quantity_dispensed' => 14]],
        ]);
        $this->post(route('pharmacy.close', $encounter));

        $response = $this->get(route('encounters.show', $encounter));
        $response->assertOk();
        $response->assertViewIs('encounters.show');

        $viewEncounter = $response->viewData('encounter');
        $this->assertTrue($viewEncounter->is_locked);
        $this->assertEquals(EncounterStage::Completed, $viewEncounter->current_stage);
    }
}
