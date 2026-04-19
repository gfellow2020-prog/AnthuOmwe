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
 * Completes lab work and queues the encounter back to Screening Review.
 *
 * Pre-conditions:
 *   - encounter.current_stage = lab
 *   - encounter.current_status = in_progress
 *   - A LabRequest exists with at least one result
 *
 * Post-conditions:
 *   - LabRequest.status = completed, completed_at set
 *   - ScreeningRecord.returned_from_lab_at set
 *   - Open queue transition (screening→lab) marked completed
 *   - Lab stage log closed
 *   - New queue transition (lab→screening_review) created
 *   - encounter.current_stage = screening_review, current_status = queued
 *   - Audit written
 */
class QueueEncounterBackToScreeningAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterQueueService    $queueService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    public function handle(Encounter $encounter, int $labTechId, ?string $notes = null): EncounterQueueTransition
    {
        return DB::transaction(function () use ($encounter, $labTechId, $notes): EncounterQueueTransition {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::Lab);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::InProgress);

            $encounter->loadMissing(['labRequest.results', 'screeningRecord']);

            if (! $encounter->labRequest) {
                throw new \RuntimeException('No lab request found for this encounter.');
            }

            if (! $encounter->labRequest->hasResults()) {
                throw new \RuntimeException(
                    'At least one result must be recorded before completing the lab stage.'
                );
            }

            // 1. Complete the lab request
            $encounter->labRequest->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            // 2. Stamp returned_from_lab_at on the screening record
            if ($encounter->screeningRecord) {
                $encounter->screeningRecord->update(['returned_from_lab_at' => now()]);
            }

            // 3. Complete the open incoming (screening→lab) transition
            $openTransition = $this->queueService->getOpenTransition($encounter);
            if ($openTransition) {
                $this->queueService->complete($openTransition);
            }

            // 4. Close the lab stage log
            $this->workflowService->completeStageLog($encounter, $labTechId, $notes);

            // 5. Create lab→screening_review queue transition
            $transition = $this->queueService->queueTo(
                encounter: $encounter,
                toStage:   EncounterStage::ScreeningReview,
                queuedBy:  $labTechId,
                notes:     $notes,
            );

            // 6. Advance encounter to screening_review
            $this->workflowService->advanceToStage(
                $encounter,
                EncounterStage::ScreeningReview,
                EncounterStatus::Queued,
            );

            // 7. Audit
            $this->auditService->record(
                encounter:   $encounter->fresh(),
                actionName:  'queued_back_to_screening_review',
                actionStage: EncounterStage::Lab,
                actionBy:    $labTechId,
                newValues:   ['to_stage' => EncounterStage::ScreeningReview->value],
                notes:       $notes,
            );

            return $transition;
        });
    }
}
