<?php

namespace Tests\Feature\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Enums\QueueTransitionStatus;
use App\Models\Encounter;
use App\Models\LabRequest;
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

/**
 * UI Pages Test — verifies every encounter-cycle page renders HTTP 200
 * and shows the key content expected by the technical guide.
 */
class UiPagesTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function user(): User
    {
        $user = User::factory()->create(['name' => 'UI Test User']);
        $this->actingAs($user);
        return $user;
    }

    private function patient(): Patient
    {
        return Patient::create([
            'patient_id'    => 'P00000200',
            'full_name'     => 'UI Test Patient',
            'gender'        => 'female',
            'date_of_birth' => '1992-03-15',
        ]);
    }

    private function encounter(
        Patient $patient,
        User $user,
        EncounterStage $stage = EncounterStage::Registration,
        EncounterStatus $status = EncounterStatus::Started,
        bool $locked = false,
    ): Encounter {
        return Encounter::create([
            'patient_id'       => $patient->id,
            'encounter_number' => 'ENC-UI-' . str_pad($stage->value, 10, '0', STR_PAD_LEFT),
            'visit_type'       => 'OPD',
            'priority_level'   => 'normal',
            'current_stage'    => $stage,
            'current_status'   => $status,
            'started_by'       => $user->id,
            'started_at'       => now(),
            'is_locked'        => $locked,
        ]);
    }

    // ─── 1. Dashboard ─────────────────────────────────────────────────────────

    public function test_dashboard_renders(): void
    {
        $this->user();

        $this->get(route('dashboard'))
             ->assertOk()
             ->assertSee('Encounter Cycle')        // new encounter stats section header text
             ->assertSee('Total Encounters')
             ->assertSee('Active Encounters')
             ->assertSee('Completed Encounters')
             ->assertSee('Encounters by Stage')
             ->assertSee('Recent Encounters');
    }

    public function test_dashboard_quick_actions_link_to_real_routes(): void
    {
        $this->user();

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // All quick actions should now be real URLs, not '#'
        $response->assertSee(route('registration.index'));
        $response->assertSee(route('encounters.index'));
        $response->assertSee(route('triage.queue'));
        $response->assertSee(route('pharmacy.queue'));
    }

    // ─── 2. Registration ──────────────────────────────────────────────────────

    public function test_registration_index_renders(): void
    {
        $this->user();

        $this->get(route('registration.index'))
             ->assertOk()
             ->assertSee('Registration');
    }

    public function test_registration_encounter_page_renders(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user);

        RegistrationRecord::create([
            'encounter_id'        => $enc->id,
            'patient_id'          => $p->id,
            'registrar_id'        => $user->id,
            'was_existing_patient'=> true,
            'registered_at'       => now(),
        ]);

        $this->get(route('registration.encounter', $enc))
             ->assertOk()
             ->assertSee($enc->encounter_number)
             ->assertSee($p->full_name);
    }

    public function test_registration_patient_search_returns_json(): void
    {
        $this->user();
        $this->patient(); // P00000200 / UI Test Patient

        $this->getJson(route('registration.search', ['q' => 'UI Test']))
             ->assertOk()
             ->assertJsonStructure(['patients' => [['id', 'patient_id', 'full_name']]]);
    }

    // ─── 3. Triage ────────────────────────────────────────────────────────────

    public function test_triage_queue_renders(): void
    {
        $this->user();

        $this->get(route('triage.queue'))
             ->assertOk()
             ->assertSee('Triage Queue');
    }

    public function test_triage_queue_shows_queued_patients(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Triage, EncounterStatus::Queued);

        $this->get(route('triage.queue'))
             ->assertOk()
             ->assertSee($p->full_name)
             ->assertSee('Receive Patient');
    }

    public function test_triage_show_renders_for_in_progress_encounter(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Triage, EncounterStatus::InProgress);

        $this->get(route('triage.show', $enc))
             ->assertOk()
             ->assertSee($enc->encounter_number)
             ->assertSee($p->full_name)
             ->assertSee('Record Vitals');
    }

    public function test_triage_show_displays_existing_vitals(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Triage, EncounterStatus::InProgress);

        TriageRecord::create([
            'encounter_id'        => $enc->id,
            'patient_id'          => $p->id,
            'nurse_id'            => $user->id,
            'weight'              => 72.5,
            'temperature'         => 38.1,
            'systolic_bp'         => 130,
            'diastolic_bp'        => 85,
            'chief_complaint_brief' => 'Headache and fever',
            'triage_at'           => now(),
        ]);

        $this->get(route('triage.show', $enc))
             ->assertOk()
             ->assertSee('72.5')
             ->assertSee('38.1')
             ->assertSee('Headache and fever')
             ->assertSee('Current Vitals');
    }

    // ─── 4. Screening ─────────────────────────────────────────────────────────

    public function test_screening_queue_renders(): void
    {
        $this->user();

        $this->get(route('screening.queue'))
             ->assertOk()
             ->assertSee('Screening Queue');
    }

    public function test_screening_queue_shows_queued_patients(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Screening, EncounterStatus::Queued);

        $this->get(route('screening.queue'))
             ->assertOk()
             ->assertSee($p->full_name)
             ->assertSee('Receive Patient');
    }

    public function test_screening_show_renders_assessment_form(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Screening, EncounterStatus::InProgress);

        $this->get(route('screening.show', $enc))
             ->assertOk()
             ->assertSee('Clinical Assessment')
             ->assertSee('Request Lab Tests')
             ->assertSee('Pharmacy');
    }

    public function test_screening_show_displays_triage_vitals_summary(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Screening, EncounterStatus::InProgress);

        TriageRecord::create([
            'encounter_id' => $enc->id,
            'patient_id'   => $p->id,
            'nurse_id'     => $user->id,
            'temperature'  => 37.5,
            'pulse'        => 80,
            'triage_at'    => now(),
        ]);

        $this->get(route('screening.show', $enc))
             ->assertOk()
             ->assertSee('Triage Vitals')
             ->assertSee('37.5');
    }

    // ─── 5. Lab ───────────────────────────────────────────────────────────────

    public function test_lab_queue_renders(): void
    {
        $this->user();

        $this->get(route('lab.queue'))
             ->assertOk()
             ->assertSee('Lab Queue');
    }

    public function test_lab_queue_shows_queued_patients(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Lab, EncounterStatus::Queued);

        $this->get(route('lab.queue'))
             ->assertOk()
             ->assertSee($p->full_name)
             ->assertSee('Receive Patient');
    }

    public function test_lab_show_renders_sample_and_results_forms(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Lab, EncounterStatus::InProgress);

        LabRequest::create([
            'encounter_id'   => $enc->id,
            'patient_id'     => $p->id,
            'requested_by'   => $user->id,
            'request_number' => 'LAB-UI-001',
            'status'         => 'pending',
            'requested_at'   => now(),
        ]);

        $this->get(route('lab.show', $enc))
             ->assertOk()
             ->assertSee('Sample Collection')
             ->assertSee('Record Results')
             ->assertSee('LAB-UI-001');
    }

    // ─── 6. Screening Review ──────────────────────────────────────────────────

    public function test_screening_review_queue_renders(): void
    {
        $this->user();

        $this->get(route('screening-review.queue'))
             ->assertOk()
             ->assertSee('Screening Review Queue');
    }

    public function test_screening_review_queue_shows_queued_patients(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::ScreeningReview, EncounterStatus::Queued);

        $this->get(route('screening-review.queue'))
             ->assertOk()
             ->assertSee($p->full_name)
             ->assertSee('Receive Patient');
    }

    public function test_screening_review_show_renders_review_and_prescription_form(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::ScreeningReview, EncounterStatus::InProgress);

        $this->get(route('screening-review.show', $enc))
             ->assertOk()
             ->assertSee('Post-Lab Clinical Review')
             ->assertSee('Final Diagnosis')
             ->assertSee('Prescription')
             ->assertSee('Drug Name');
    }

    // ─── 7. Pharmacy ──────────────────────────────────────────────────────────

    public function test_pharmacy_queue_renders(): void
    {
        $this->user();

        $this->get(route('pharmacy.queue'))
             ->assertOk()
             ->assertSee('Pharmacy Queue');
    }

    public function test_pharmacy_queue_shows_queued_patients(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Pharmacy, EncounterStatus::Queued);

        $this->get(route('pharmacy.queue'))
             ->assertOk()
             ->assertSee($p->full_name)
             ->assertSee('Receive');
    }

    public function test_pharmacy_show_renders_prescription_and_dispense_form(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Pharmacy, EncounterStatus::InProgress);

        $rx = PharmacyPrescription::create([
            'encounter_id'        => $enc->id,
            'patient_id'          => $p->id,
            'screening_record_id' => null,
            'prescribed_by'       => $user->id,
            'prescription_number' => 'RX-UI-001',
            'status'              => 'active',
            'prescribed_at'       => now(),
        ]);

        PharmacyPrescriptionItem::create([
            'pharmacy_prescription_id' => $rx->id,
            'drug_name'                => 'Amoxicillin',
            'dose'                     => '500mg',
            'frequency'                => 'TDS',
            'duration'                 => '7 days',
            'quantity_prescribed'      => 21,
        ]);

        $this->get(route('pharmacy.show', $enc))
             ->assertOk()
             ->assertSee('RX-UI-001')
             ->assertSee('Amoxicillin')
             ->assertSee('Dispense Medications')
             ->assertSee('Close');
    }

    public function test_pharmacy_show_locked_encounter_hides_forms(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Completed, EncounterStatus::Completed, locked: true);
        $enc->update(['closed_at' => now(), 'closed_by' => $user->id]);

        $this->get(route('pharmacy.show', $enc))
             ->assertOk()
             ->assertSee('Closed')
             ->assertSee('Locked');
    }

    // ─── 8. Encounter Profile ─────────────────────────────────────────────────

    public function test_encounters_index_renders(): void
    {
        $this->user();

        $this->get(route('encounters.index'))
             ->assertOk()
             ->assertSee('Encounters');
    }

    public function test_encounters_index_lists_encounters(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user);

        $this->get(route('encounters.index'))
             ->assertOk()
             ->assertSee($enc->encounter_number)
             ->assertSee($p->full_name)
             ->assertSee('View Profile');
    }

    public function test_encounter_profile_shows_all_sections(): void
    {
        $user = $this->user();
        $p    = $this->patient();
        $enc  = $this->encounter($p, $user, EncounterStage::Completed, EncounterStatus::Completed, locked: true);
        $enc->update(['closed_at' => now(), 'closed_by' => $user->id]);

        RegistrationRecord::create([
            'encounter_id'        => $enc->id,
            'patient_id'          => $p->id,
            'registrar_id'        => $user->id,
            'was_existing_patient'=> true,
            'registered_at'       => now(),
        ]);

        TriageRecord::create([
            'encounter_id' => $enc->id,
            'patient_id'   => $p->id,
            'nurse_id'     => $user->id,
            'weight'       => 68.0,
            'temperature'  => 37.2,
            'triage_at'    => now(),
        ]);

        ScreeningRecord::create([
            'encounter_id'    => $enc->id,
            'patient_id'      => $p->id,
            'clinician_id'    => $user->id,
            'screening_type'  => 'initial',
            'complaints'      => 'Test complaint',
            'provisional_diagnosis' => 'Test diagnosis',
            'lab_requested'   => false,
            'prescribed'      => true,
        ]);

        $rx = PharmacyPrescription::create([
            'encounter_id'        => $enc->id,
            'patient_id'          => $p->id,
            'screening_record_id' => null,
            'prescribed_by'       => $user->id,
            'prescription_number' => 'RX-PROF-001',
            'status'              => 'dispensed',
            'prescribed_at'       => now(),
        ]);

        PharmacyPrescriptionItem::create([
            'pharmacy_prescription_id' => $rx->id,
            'drug_name'                => 'Paracetamol',
            'dose'                     => '1g',
            'frequency'                => 'TDS',
            'duration'                 => '5 days',
            'quantity_prescribed'      => 15,
        ]);

        $dispense = PharmacyDispense::create([
            'encounter_id'              => $enc->id,
            'patient_id'                => $p->id,
            'pharmacy_prescription_id'  => $rx->id,
            'dispensed_by'              => $user->id,
            'dispensed_at'              => now(),
        ]);

        PharmacyDispenseItem::create([
            'pharmacy_dispense_id'             => $dispense->id,
            'pharmacy_prescription_item_id'    => null,
            'drug_name'                        => 'Paracetamol',
            'quantity_dispensed'               => 15,
        ]);

        $this->get(route('encounters.show', $enc))
             ->assertOk()
             // Encounter header section
             ->assertSee('Encounter Header')
             ->assertSee($enc->encounter_number)
             // Patient demographics section
             ->assertSee('Patient Demographics')
             ->assertSee($p->full_name)
             ->assertSee($p->patient_id)
             // Registration section
             ->assertSee('Registration')
             // Triage section
             ->assertSee('Triage')
             ->assertSee('68')
             // Screening section
             ->assertSee('Initial Screening')
             ->assertSee('Test complaint')
             // Prescription section
             ->assertSee('Paracetamol')
             // Lock state
             ->assertSee('Locked');
    }

    // ─── 9. Sidebar navigation ────────────────────────────────────────────────

    public function test_sidebar_encounter_cycle_links_are_rendered(): void
    {
        $this->user();

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Encounter cycle section must appear in navigation
        $response->assertSee('Encounter Cycle');
        $response->assertSee(route('registration.index'));
        $response->assertSee(route('triage.queue'));
        $response->assertSee(route('screening.queue'));
        $response->assertSee(route('lab.queue'));
        $response->assertSee(route('screening-review.queue'));
        $response->assertSee(route('pharmacy.queue'));
        $response->assertSee(route('encounters.index'));
    }

    // ─── 10. Unauthenticated redirect ─────────────────────────────────────────

    public function test_all_cycle_pages_redirect_unauthenticated_to_login(): void
    {
        $routes = [
            route('registration.index'),
            route('triage.queue'),
            route('screening.queue'),
            route('lab.queue'),
            route('screening-review.queue'),
            route('pharmacy.queue'),
            route('encounters.index'),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }
}
