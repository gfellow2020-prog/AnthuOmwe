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
 * Completes initial screening and queues the encounter to Lab.
 *
 * Pre-conditions:
 *   - encounter.current_stage = screening
 *   - encounter.current_status = in_progress
 *   - A ScreeningRecord exists with lab_requested = true
 *
 * Post-conditions:
 *   - ScreeningRecord.referred_to_lab_at is set
 *   - ScreeningRecord.screening_completed_at is set
 *   - Open queue transition (triage→screening) marked completed
 *   - Screening stage log closed
 *   - New queue transition (screening→lab) created
 *   - encounter.current_stage = lab, current_status = queued
 *   - Audit written
 */
class QueueEncounterToLabAction
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
            $this->workflowService->assertStageIs($encounter, EncounterStage::Screening);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::InProgress);

            $encounter->loadMissing('screeningRecord');
            if (! $encounter->screeningRecord) {
                throw new \RuntimeException(
                    'A screening assessment must be recorded before queuing to Lab.'
                );
            }

            if (! $encounter->screeningRecord->lab_requested) {
                throw new \RuntimeException(
                    'Lab must be requested on the screening record before queuing to Lab.'
                );
            }

            // 1. Stamp lab referral time and completion on the screening record
            $encounter->screeningRecord->update([
                'referred_to_lab_at'     => now(),
                'screening_completed_at' => now(),
            ]);

            // 2. Complete the incoming (triage→screening) transition
            $openTransition = $this->queueService->getOpenTransition($encounter);
            if ($openTransition) {
                $this->queueService->complete($openTransition);
            }

            // 3. Close the screening stage log
            $this->workflowService->completeStageLog($encounter, $clinicianId, $notes);

            // 4. Create screening→lab queue transition
            $transition = $this->queueService->queueTo(
                encounter: $encounter,
                toStage:   EncounterStage::Lab,
                queuedBy:  $clinicianId,
                notes:     $notes,
            );

            // 5. Advance encounter to lab
            $this->workflowService->advanceToStage(
                $encounter,
                EncounterStage::Lab,
                EncounterStatus::Queued,
            );

            // 6. Audit
            $this->auditService->record(
                encounter:   $encounter->fresh(),
                actionName:  'queued_to_lab',
                actionStage: EncounterStage::Screening,
                actionBy:    $clinicianId,
                newValues:   ['to_stage' => EncounterStage::Lab->value],
                notes:       $notes,
            );

            return $transition;
        });
    }
}
