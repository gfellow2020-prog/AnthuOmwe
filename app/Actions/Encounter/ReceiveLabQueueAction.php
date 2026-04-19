<?php

namespace App\Actions\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Models\Encounter;
use App\Models\LabRequest;
use App\Services\Encounter\EncounterAuditService;
use App\Services\Encounter\EncounterLockService;
use App\Services\Encounter\EncounterQueueService;
use App\Services\Encounter\EncounterWorkflowService;
use Illuminate\Support\Facades\DB;

/**
 * Receives a patient into Lab from the screening queue.
 *
 * Also creates (or re-uses) the LabRequest record that lab staff will work from.
 *
 * Pre-conditions:
 *   - encounter.current_stage = lab
 *   - encounter.current_status = queued
 *
 * Post-conditions:
 *   - Queue transition (screening→lab) marked received
 *   - Lab stage log opened
 *   - LabRequest created (status = in_progress)
 *   - encounter.current_status = in_progress
 *   - Audit written
 */
class ReceiveLabQueueAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterQueueService    $queueService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    public function handle(Encounter $encounter, int $labTechId): LabRequest
    {
        return DB::transaction(function () use ($encounter, $labTechId): LabRequest {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::Lab);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::Queued);

            // 1. Mark the incoming transition (screening→lab) as received
            $transition = $this->queueService->getOpenTransition($encounter);
            if ($transition) {
                $this->queueService->receive($transition, $labTechId);
            }

            // 2. Open the lab stage log
            $this->workflowService->openStageLog($encounter, $labTechId);

            // 3. Create the LabRequest if one doesn't already exist
            $encounter->loadMissing(['screeningRecord', 'labRequest']);

            $labRequest = $encounter->labRequest ?? LabRequest::create([
                'encounter_id'       => $encounter->id,
                'patient_id'         => $encounter->patient_id,
                'screening_record_id'=> $encounter->screeningRecord?->id,
                'requested_by'       => $labTechId,
                'request_number'     => $this->generateRequestNumber($encounter),
                'priority_level'     => $encounter->priority_level,
                'status'             => 'in_progress',
                'requested_at'       => now(),
            ]);

            // 4. Set encounter in-progress
            $this->workflowService->markInProgress($encounter);

            // 5. Audit
            $this->auditService->record(
                encounter:   $encounter,
                actionName:  'lab_received',
                actionStage: EncounterStage::Lab,
                actionBy:    $labTechId,
                newValues:   ['lab_request_number' => $labRequest->request_number],
            );

            return $labRequest;
        });
    }

    private function generateRequestNumber(Encounter $encounter): string
    {
        $date = now()->format('Ymd');
        $seq  = str_pad(LabRequest::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        return "LAB-{$date}-{$seq}";
    }
}
