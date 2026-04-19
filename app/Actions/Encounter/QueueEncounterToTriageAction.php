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
 * Completes the registration stage and moves the encounter into the
 * triage queue.
 *
 * Post-conditions:
 *   - registration stage log is marked completed
 *   - an EncounterQueueTransition to triage is created (status: queued)
 *   - encounter.current_stage = triage
 *   - encounter.current_status = queued
 *   - audit entry written
 */
class QueueEncounterToTriageAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterQueueService    $queueService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    /**
     * @param  int  $queuedBy  Authenticated user ID
     */
    public function handle(Encounter $encounter, int $queuedBy, ?string $notes = null): EncounterQueueTransition
    {
        return DB::transaction(function () use ($encounter, $queuedBy, $notes): EncounterQueueTransition {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::Registration);

            // 1. Close the registration stage log
            $this->workflowService->completeStageLog($encounter, $queuedBy, $notes);

            // 2. Create queue transition record (registration → triage)
            $transition = $this->queueService->queueTo(
                encounter: $encounter,
                toStage:   EncounterStage::Triage,
                queuedBy:  $queuedBy,
                notes:     $notes,
            );

            // 3. Advance encounter stage/status
            $this->workflowService->advanceToStage(
                $encounter,
                EncounterStage::Triage,
                EncounterStatus::Queued,
            );

            // 4. Audit
            $this->auditService->record(
                encounter:   $encounter->fresh(),
                actionName:  'queued_to_triage',
                actionStage: EncounterStage::Registration,
                actionBy:    $queuedBy,
                newValues:   ['to_stage' => EncounterStage::Triage->value],
                notes:       $notes,
            );

            return $transition;
        });
    }
}
