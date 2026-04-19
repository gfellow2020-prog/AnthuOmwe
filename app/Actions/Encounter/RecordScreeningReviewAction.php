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
 * Records the post-lab clinical review as a second ScreeningRecord
 * with screening_type = 'review_after_lab'.
 *
 * Pre-conditions:
 *   - encounter.current_stage = screening_review
 *   - encounter.current_status = in_progress
 *
 * Post-conditions:
 *   - A new ScreeningRecord (type=review_after_lab) is persisted
 *   - Audit written
 *
 * @param array{
 *   final_diagnosis?: string|null,
 *   clinical_findings?: string|null,
 *   physical_examination?: string|null,
 *   assessment_notes?: string|null,
 *   plan?: string|null,
 *   review_notes?: string|null,
 * } $data
 */
class RecordScreeningReviewAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    public function handle(Encounter $encounter, array $data, int $clinicianId): ScreeningRecord
    {
        return DB::transaction(function () use ($encounter, $data, $clinicianId): ScreeningRecord {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::ScreeningReview);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::InProgress);

            $record = ScreeningRecord::create([
                'encounter_id'          => $encounter->id,
                'patient_id'            => $encounter->patient_id,
                'clinician_id'          => $clinicianId,
                'screening_type'        => 'review_after_lab',
                'final_diagnosis'       => $data['final_diagnosis']      ?? null,
                'clinical_findings'     => $data['clinical_findings']    ?? null,
                'physical_examination'  => $data['physical_examination'] ?? null,
                'assessment_notes'      => $data['assessment_notes']     ?? null,
                'plan'                  => $data['plan']                 ?? null,
                'review_notes'          => $data['review_notes']         ?? null,
                'prescribed'            => false,
                'screening_started_at'  => now(),
                'screening_completed_at'=> now(),
            ]);

            $this->auditService->record(
                encounter:   $encounter,
                actionName:  'screening_review_completed',
                actionStage: EncounterStage::ScreeningReview,
                actionBy:    $clinicianId,
            );

            return $record;
        });
    }
}
