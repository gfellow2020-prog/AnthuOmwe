<?php

namespace App\Actions\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Models\Encounter;
use App\Services\Encounter\EncounterAuditService;
use App\Services\Encounter\EncounterLockService;
use App\Services\Encounter\EncounterWorkflowService;
use Illuminate\Support\Facades\DB;

/**
 * Closes an encounter permanently.
 *
 * Pre-conditions:
 *   - encounter.current_stage = pharmacy
 *   - encounter.current_status = in_progress
 *   - A PharmacyDispense with at least one item must exist
 *
 * Post-conditions:
 *   - Final pharmacy stage log completed
 *   - encounter.current_stage = completed
 *   - encounter.current_status = completed
 *   - encounter.closed_at set
 *   - encounter.closed_by set
 *   - encounter.is_locked = true
 *   - Final audit record written
 */
class CloseEncounterAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    public function handle(Encounter $encounter, int $pharmacistId, ?string $closureNotes = null): void
    {
        DB::transaction(function () use ($encounter, $pharmacistId, $closureNotes): void {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::Pharmacy);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::InProgress);

            // Guard: a dispense with items must exist
            $encounter->loadMissing(['dispense.items']);
            if (! $encounter->dispense || ! $encounter->dispense->hasItems()) {
                throw new \RuntimeException(
                    'Medication must be dispensed before the encounter can be closed.'
                );
            }

            // 1. Complete the open queue transition for pharmacy
            $openTransition = app(\App\Services\Encounter\EncounterQueueService::class)
                ->getOpenTransition($encounter);
            if ($openTransition) {
                app(\App\Services\Encounter\EncounterQueueService::class)
                    ->complete($openTransition);
            }

            // 2. Close the pharmacy stage log
            $this->workflowService->completeStageLog($encounter, $pharmacistId, $closureNotes);

            // 3. Mark encounter as completed
            $encounter->update([
                'current_stage'  => EncounterStage::Completed,
                'current_status' => EncounterStatus::Completed,
                'closed_at'      => now(),
                'closed_by'      => $pharmacistId,
                'closure_notes'  => $closureNotes,
            ]);

            // 4. Hard lock — no further edits allowed
            $this->lockService->lock($encounter);

            // 5. Audit
            $this->auditService->record(
                encounter:   $encounter,
                actionName:  'encounter_closed',
                actionStage: EncounterStage::Pharmacy,
                actionBy:    $pharmacistId,
                notes:       $closureNotes,
            );
        });
    }
}
