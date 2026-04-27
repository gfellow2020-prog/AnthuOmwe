<?php

namespace App\Actions\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Models\Encounter;
use App\Models\ScreeningRecord;
use App\Services\Encounter\EncounterAuditService;
use App\Services\Encounter\EncounterLockService;
use App\Services\Encounter\EncounterWorkflowService;
use Illuminate\Support\Facades\DB;

/**
 * Saves (or updates) the initial screening clinical record.
 *
 * Does NOT advance the stage — call QueueEncounterToLabAction or
 * QueueEncounterToPharmacyAction after this to move the encounter forward.
 *
 * Supports upsert so clinician can save progress and revise before completing.
 */
class RecordInitialScreeningAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    /**
     * @param array{
     *   complaints?: string|null,
     *   history_of_presenting_illness?: string|null,
     *   past_medical_history?: string|null,
     *   medication_history?: string|null,
     *   allergy_history?: string|null,
     *   physical_examination?: string|null,
     *   clinical_findings?: string|null,
     *   provisional_diagnosis?: string|null,
     *   final_diagnosis?: string|null,
     *   assessment_notes?: string|null,
     *   plan?: string|null,
     *   lab_requested?: bool,
     *   staff_assignments?: array<array{user_id:int,role_name?:string,participation_type?:string,notes?:string}>,
     * } $data
     */
    public function handle(Encounter $encounter, array $data, int $clinicianId): ScreeningRecord
    {
        return DB::transaction(function () use ($encounter, $data, $clinicianId): ScreeningRecord {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::Screening);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::InProgress);

            $record = ScreeningRecord::updateOrCreate(
                ['encounter_id' => $encounter->id],
                [
                    'patient_id'                   => $encounter->patient_id,
                    'clinician_id'                 => $clinicianId,
                    'screening_type'               => 'initial',
                    // Complaints & Histories
                    'complaints'                   => $data['complaints']                   ?? null,
                    'tb_symptoms'                  => $data['tb_symptoms']                  ?? null,
                    'constitutional_symptoms'      => $data['constitutional_symptoms']      ?? null,
                    'presumptive_tb_case_no'       => $data['presumptive_tb_case_no']       ?? null,
                    'review_of_systems'            => $data['review_of_systems']            ?? null,
                    'history_of_presenting_illness'=> $data['history_of_presenting_illness'] ?? null,
                    'past_medical_history'         => $data['past_medical_history']         ?? null,
                    'medication_history'           => $data['medication_history']           ?? null,
                    'allergy_history'              => $data['allergy_history']              ?? null,
                    'chronic_conditions'           => $data['chronic_conditions']           ?? null,
                    'family_history'               => $data['family_history']               ?? null,
                    'social_history'               => $data['social_history']               ?? null,
                    // Paediatric History
                    'birth_weight'                 => $data['birth_weight']                 ?? null,
                    'birth_length'                 => $data['birth_length']                 ?? null,
                    'head_circumference'           => $data['head_circumference']           ?? null,
                    'chest_circumference'          => $data['chest_circumference']          ?? null,
                    'general_condition'            => $data['general_condition']            ?? null,
                    'is_breast_feeding_well'       => isset($data['is_breast_feeding_well']) ? (bool) $data['is_breast_feeding_well'] : null,
                    'other_feeding_option'         => $data['other_feeding_option']         ?? null,
                    'delivery_time'                => $data['delivery_time']                ?? null,
                    'vaccination_outside'          => $data['vaccination_outside']          ?? null,
                    'tetanus_at_birth'             => $data['tetanus_at_birth']             ?? null,
                    'birth_outcome'                => $data['birth_outcome']                ?? null,
                    'birth_notes'                  => $data['birth_notes']                  ?? null,
                    'immunization_history'         => $data['immunization_history']         ?? null,
                    'feeding_code'                 => $data['feeding_code']                 ?? null,
                    'feeding_comments'             => $data['feeding_comments']             ?? null,
                    'development_history'          => $data['development_history']          ?? null,
                    // Examination & Diagnosis
                    'physical_examination'         => $data['physical_examination']         ?? null,
                    'clinical_findings'            => $data['clinical_findings']            ?? null,
                    'provisional_diagnosis'        => $data['provisional_diagnosis']        ?? null,
                    'final_diagnosis'              => $data['final_diagnosis']              ?? null,
                    'assessment_notes'             => $data['assessment_notes']             ?? null,
                    // Plan
                    'plan'                         => $data['plan']                         ?? null,
                    'treatment_plan'               => $data['treatment_plan']               ?? null,
                    'lab_requested'                => (bool) ($data['lab_requested'] ?? false),
                    'screening_started_at'         => now(),
                ],
            );

            // Sync staff assignments if provided
            if (! empty($data['staff_assignments'])) {
                // Remove previous and re-insert (idempotent when clinician revises)
                $record->staffAssignments()->delete();

                foreach ($data['staff_assignments'] as $assignment) {
                    $record->staffAssignments()->create([
                        'user_id'            => $assignment['user_id'],
                        'role_name'          => $assignment['role_name']          ?? null,
                        'participation_type' => $assignment['participation_type'] ?? null,
                        'notes'              => $assignment['notes']              ?? null,
                    ]);
                }
            }

            $this->auditService->record(
                encounter:   $encounter,
                actionName:  'screening_assessment_recorded',
                actionStage: EncounterStage::Screening,
                actionBy:    $clinicianId,
                newValues:   array_filter([
                    'provisional_diagnosis' => $data['provisional_diagnosis'] ?? null,
                    'lab_requested'         => $data['lab_requested'] ?? false,
                ]),
            );

            return $record;
        });
    }
}
