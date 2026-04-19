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
 * Receives a patient into Screening Review from the post-lab queue.
 *
 * Pre-conditions:
 *   - encounter.current_stage = screening_review
 *   - encounter.current_status = queued
 *
 * Post-conditions:
 *   - Queue transition (lab → screening_review) marked received
 *   - Screening review stage log opened
 *   - encounter.current_status = in_progress
 *   - Audit written
 */
class ReceiveScreeningReviewQueueAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterQueueService    $queueService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    public function handle(Encounter $encounter, int $clinicianId): void
    {
        DB::transaction(function () use ($encounter, $clinicianId): void {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::ScreeningReview);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::Queued);

            // 1. Mark the incoming transition (lab → screening_review) as received
            $transition = $this->queueService->getOpenTransition($encounter);
            if ($transition) {
                $this->queueService->receive($transition, $clinicianId);
            }

            // 2. Open the screening review stage log
            $this->workflowService->openStageLog($encounter, $clinicianId);

            // 3. Mark in-progress
            $this->workflowService->markInProgress($encounter);

            // 4. Audit
            $this->auditService->record(
                encounter:   $encounter,
                actionName:  'screening_review_received',
                actionStage: EncounterStage::ScreeningReview,
                actionBy:    $clinicianId,
            );
        });
    }
}
