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
 * Completes screening and queues the encounter directly to Pharmacy (no lab).
 *
 * Pre-conditions:
 *   - encounter.current_stage = screening
 *   - encounter.current_status = in_progress
 *   - A ScreeningRecord exists
 *   - lab_requested must be false
 *
 * Post-conditions:
 *   - ScreeningRecord.prescribed = true
 *   - ScreeningRecord.screening_completed_at is set
 *   - Open queue transition marked completed
 *   - Screening stage log closed
 *   - New queue transition (screening→pharmacy) created
 *   - encounter.current_stage = pharmacy, current_status = queued
 *   - Audit written
 */
class QueueEncounterToPharmacyFromScreeningAction
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
                    'A screening assessment must be recorded before queuing to Pharmacy.'
                );
            }

            // Guard: if lab was requested, use QueueEncounterToLabAction instead
            if ($encounter->screeningRecord->lab_requested) {
                throw new \RuntimeException(
                    'Lab has been requested — cannot queue directly to Pharmacy. Use QueueEncounterToLabAction.'
                );
            }

            // 1. Stamp the screening record
            $encounter->screeningRecord->update([
                'prescribed'             => true,
                'screening_completed_at' => now(),
            ]);

            // 2. Complete the open incoming transition
            $openTransition = $this->queueService->getOpenTransition($encounter);
            if ($openTransition) {
                $this->queueService->complete($openTransition);
            }

            // 3. Close the screening stage log
            $this->workflowService->completeStageLog($encounter, $clinicianId, $notes);

            // 4. Create screening→pharmacy queue transition
            $transition = $this->queueService->queueTo(
                encounter: $encounter,
                toStage:   EncounterStage::Pharmacy,
                queuedBy:  $clinicianId,
                notes:     $notes,
            );

            // 5. Advance encounter to pharmacy
            $this->workflowService->advanceToStage(
                $encounter,
                EncounterStage::Pharmacy,
                EncounterStatus::Queued,
            );

            // 6. Audit
            $this->auditService->record(
                encounter:   $encounter->fresh(),
                actionName:  'queued_to_pharmacy_direct',
                actionStage: EncounterStage::Screening,
                actionBy:    $clinicianId,
                newValues:   ['to_stage' => EncounterStage::Pharmacy->value, 'lab_skipped' => true],
                notes:       $notes,
            );

            return $transition;
        });
    }
}
