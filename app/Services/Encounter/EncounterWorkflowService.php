<?php

namespace App\Services\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Enums\QueueTransitionStatus;
use App\Exceptions\Encounter\InvalidEncounterStageException;
use App\Models\Encounter;
use App\Models\EncounterStageLog;
use App\Support\Encounter\EncounterStageMap;

class EncounterWorkflowService
{
    // ─── Stage Assertions ─────────────────────────────────────────────────────

    /**
     * Throws if the encounter is not at the expected stage.
     */
    public function assertStageIs(Encounter $encounter, EncounterStage $expected): void
    {
        if ($encounter->current_stage !== $expected) {
            throw new InvalidEncounterStageException($expected, $encounter->current_stage);
        }
    }

    /**
     * Throws if the encounter does not have the expected status.
     */
    public function assertStatusIs(Encounter $encounter, EncounterStatus $expected): void
    {
        if ($encounter->current_status !== $expected) {
            throw new \RuntimeException(
                "Expected encounter status [{$expected->value}] but found [{$encounter->current_status->value}]."
            );
        }
    }

    // ─── Stage Transitions ────────────────────────────────────────────────────

    /**
     * Moves the encounter to a new stage and sets its status to 'queued'.
     * Validates that the transition is allowed before writing.
     */
    public function advanceToStage(
        Encounter     $encounter,
        EncounterStage $nextStage,
        EncounterStatus $nextStatus = EncounterStatus::Queued,
    ): Encounter {
        EncounterStageMap::assertCanTransitionTo($encounter->current_stage, $nextStage);

        $encounter->update([
            'current_stage'  => $nextStage,
            'current_status' => $nextStatus,
        ]);

        return $encounter->fresh();
    }

    // ─── Stage Log Management ─────────────────────────────────────────────────

    /**
     * Opens a new stage log entry for the encounter's current stage.
     * Called when a department receives the patient.
     */
    public function openStageLog(
        Encounter $encounter,
        int       $startedBy,
        ?string   $notes = null,
        array     $metadata = [],
    ): EncounterStageLog {
        return EncounterStageLog::create([
            'encounter_id'   => $encounter->id,
            'patient_id'     => $encounter->patient_id,
            'stage_name'     => $encounter->current_stage->value,
            'stage_sequence' => $encounter->current_stage->sequence(),
            'status'         => QueueTransitionStatus::Received->value,
            'started_by'     => $startedBy,
            'started_at'     => now(),
            'notes'          => $notes,
            'metadata'       => $metadata ?: null,
        ]);
    }

    /**
     * Completes the active stage log for the encounter's current stage.
     * Called when a department finishes its work.
     */
    public function completeStageLog(
        Encounter $encounter,
        int       $completedBy,
        ?string   $notes = null,
    ): EncounterStageLog {
        $log = EncounterStageLog::where('encounter_id', $encounter->id)
            ->where('stage_name', $encounter->current_stage->value)
            ->whereNull('completed_at')
            ->latest()
            ->firstOrFail();

        $log->update([
            'status'       => QueueTransitionStatus::Completed->value,
            'completed_by' => $completedBy,
            'completed_at' => now(),
            'notes'        => $notes ?? $log->notes,
        ]);

        return $log->fresh();
    }

    /**
     * Marks the encounter itself as in-progress (called on stage receive).
     */
    public function markInProgress(Encounter $encounter): Encounter
    {
        $encounter->update(['current_status' => EncounterStatus::InProgress]);
        return $encounter->fresh();
    }
}
