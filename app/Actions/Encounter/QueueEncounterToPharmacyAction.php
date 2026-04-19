<?php

namespace App\Actions\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Models\Encounter;
use App\Models\EncounterQueueTransition;
use App\Services\Encounter\EncounterAuditService;
use App\Services\Encounter\EncounterLockService;
use App\Services\Encounter\EncounterQueueService;
use App\Services\Encounter\EncounterWorkflowService;
use Illuminate\Support\Facades\DB;

/**
 * Completes Screening Review and queues the encounter to Pharmacy.
 *
 * Pre-conditions:
 *   - encounter.current_stage = screening_review
 *   - encounter.current_status = in_progress
 *   - A PharmacyPrescription with at least one item exists
 *
 * Post-conditions:
 *   - Screening review stage log closed
 *   - New queue transition (screening_review → pharmacy) created
 *   - encounter.current_stage = pharmacy, current_status = queued
 *   - Audit written
 */
class QueueEncounterToPharmacyAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterQueueService    $queueService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    public function handle(Encounter $encounter, int $clinicianId, ?string $notes = null): EncounterQueueTransition
    {
        return DB::transaction(function () use ($encounter, $clinicianId, $notes): EncounterQueueTransition {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::ScreeningReview);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::InProgress);

            $encounter->loadMissing('prescription');

            if (! $encounter->prescription || ! $encounter->prescription->hasItems()) {
                throw new \RuntimeException(
                    'A prescription with at least one item is required before queuing to Pharmacy.'
                );
            }

            // 1. Close the screening review stage log
            $this->workflowService->completeStageLog($encounter, $clinicianId, $notes);

            // 2. Create transition → pharmacy
            $transition = $this->queueService->queueTo(
                encounter: $encounter,
                toStage:   EncounterStage::Pharmacy,
                queuedBy:  $clinicianId,
                notes:     $notes,
            );

            // 3. Advance stage
            $this->workflowService->advanceToStage(
                $encounter,
                EncounterStage::Pharmacy,
                EncounterStatus::Queued,
            );

            // 4. Audit
            $this->auditService->record(
                encounter:   $encounter,
                actionName:  'queued_to_pharmacy',
                actionStage: EncounterStage::Pharmacy,
                actionBy:    $clinicianId,
            );

            return $transition;
        });
    }
}
