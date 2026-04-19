<?php

namespace App\Actions\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Models\Encounter;
use App\Services\Encounter\EncounterAuditService;
use App\Services\Encounter\EncounterLockService;
use App\Services\Encounter\EncounterQueueService;
use App\Services\Encounter\EncounterWorkflowService;
use Illuminate\Support\Facades\DB;

/**
 * Receives a patient into Pharmacy.
 *
 * Pharmacy accepts encounters coming from either:
 *   - screening_review → pharmacy  (post-lab path)
 *   - screening → pharmacy         (direct path, no lab)
 *
 * Pre-conditions:
 *   - encounter.current_stage = pharmacy
 *   - encounter.current_status = queued
 *
 * Post-conditions:
 *   - Queue transition marked received
 *   - Pharmacy stage log opened
 *   - encounter.current_status = in_progress
 *   - Audit written
 */
class ReceivePharmacyQueueAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterQueueService    $queueService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    public function handle(Encounter $encounter, int $pharmacistId): void
    {
        DB::transaction(function () use ($encounter, $pharmacistId): void {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::Pharmacy);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::Queued);

            // 1. Mark the incoming transition as received
            $transition = $this->queueService->getOpenTransition($encounter);
            if ($transition) {
                $this->queueService->receive($transition, $pharmacistId);
            }

            // 2. Open the pharmacy stage log
            $this->workflowService->openStageLog($encounter, $pharmacistId);

            // 3. Mark in-progress
            $this->workflowService->markInProgress($encounter);

            // 4. Audit
            $this->auditService->record(
                encounter:   $encounter,
                actionName:  'pharmacy_received',
                actionStage: EncounterStage::Pharmacy,
                actionBy:    $pharmacistId,
            );
        });
    }
}
