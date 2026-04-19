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
 * Triage nurse receives the queued encounter.
 *
 * Post-conditions:
 *   - Active queue transition (registration→triage) is marked received
 *   - A triage stage log entry is opened
 *   - encounter.current_status = in_progress
 *   - Audit written
 */
class ReceiveTriageQueueAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterQueueService    $queueService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    public function handle(Encounter $encounter, int $nurseId): Encounter
    {
        return DB::transaction(function () use ($encounter, $nurseId): Encounter {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::Triage);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::Queued);

            // 1. Mark the incoming queue transition as received
            $transition = $this->queueService->getOpenTransition($encounter);
            if ($transition) {
                $this->queueService->receive($transition, $nurseId);
            }

            // 2. Open a triage stage log
            $this->workflowService->openStageLog($encounter, $nurseId);

            // 3. Mark encounter in-progress
            $this->workflowService->markInProgress($encounter);

            // 4. Audit
            $this->auditService->record(
                encounter:   $encounter->fresh(),
                actionName:  'triage_received',
                actionStage: EncounterStage::Triage,
                actionBy:    $nurseId,
            );

            return $encounter->fresh();
        });
    }
}
