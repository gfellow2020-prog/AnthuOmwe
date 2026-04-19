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
 * Completes triage and queues the encounter to Screening.
 *
 * Pre-conditions:
 *   - encounter.current_stage = triage
 *   - encounter.current_status = in_progress
 *   - A TriageRecord must already exist for this encounter
 *
 * Post-conditions:
 *   - Open queue transition (reg→triage) is completed
 *   - Triage stage log is closed
 *   - TriageRecord.completed_at is set
 *   - New queue transition (triage→screening) created
 *   - encounter.current_stage = screening
 *   - encounter.current_status = queued
 *   - Audit written
 */
class QueueEncounterToScreeningAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterQueueService    $queueService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    public function handle(Encounter $encounter, int $nurseId, ?string $notes = null): EncounterQueueTransition
    {
        return DB::transaction(function () use ($encounter, $nurseId, $notes): EncounterQueueTransition {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::Triage);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::InProgress);

            // Guard: triage record must exist before completing
            $encounter->loadMissing('triageRecord');
            if (! $encounter->triageRecord) {
                throw new \RuntimeException(
                    'Triage vitals must be recorded before queuing to Screening.'
                );
            }

            // 1. Mark the triage→screening queue transition (received) as completed
            $openTransition = $this->queueService->getOpenTransition($encounter);
            if ($openTransition) {
                $this->queueService->complete($openTransition);
            }

            // 2. Stamp completed_at on the triage record
            $encounter->triageRecord->update(['completed_at' => now()]);

            // 3. Close triage stage log
            $this->workflowService->completeStageLog($encounter, $nurseId, $notes);

            // 4. Create queue transition triage → screening
            $transition = $this->queueService->queueTo(
                encounter: $encounter,
                toStage:   EncounterStage::Screening,
                queuedBy:  $nurseId,
                notes:     $notes,
            );

            // 5. Advance encounter
            $this->workflowService->advanceToStage(
                $encounter,
                EncounterStage::Screening,
                EncounterStatus::Queued,
            );

            // 6. Audit
            $this->auditService->record(
                encounter:   $encounter->fresh(),
                actionName:  'queued_to_screening',
                actionStage: EncounterStage::Triage,
                actionBy:    $nurseId,
                newValues:   ['to_stage' => EncounterStage::Screening->value],
                notes:       $notes,
            );

            return $transition;
        });
    }
}
