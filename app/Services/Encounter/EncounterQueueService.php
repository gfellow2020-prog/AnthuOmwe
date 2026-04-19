<?php

namespace App\Services\Encounter;

use App\Enums\EncounterStage;
use App\Enums\QueueTransitionStatus;
use App\Models\Encounter;
use App\Models\EncounterQueueTransition;

class EncounterQueueService
{
    /**
     * Places the encounter in the queue for the given target stage.
     * Records the queue transition and returns it.
     */
    public function queueTo(
        Encounter     $encounter,
        EncounterStage $toStage,
        int            $queuedBy,
        ?string        $notes = null,
    ): EncounterQueueTransition {
        return EncounterQueueTransition::create([
            'encounter_id'     => $encounter->id,
            'patient_id'       => $encounter->patient_id,
            'from_stage'       => $encounter->current_stage->value,
            'to_stage'         => $toStage->value,
            'queued_by'        => $queuedBy,
            'queued_at'        => now(),
            'status'           => QueueTransitionStatus::Queued->value,
            'transition_notes' => $notes,
        ]);
    }

    /**
     * Marks a pending queue transition as received by the accepting department.
     */
    public function receive(EncounterQueueTransition $transition, int $receivedBy): EncounterQueueTransition
    {
        $transition->update([
            'received_by'  => $receivedBy,
            'received_at'  => now(),
            'status'       => QueueTransitionStatus::Received->value,
        ]);

        return $transition->fresh();
    }

    /**
     * Marks a queue transition as completed (stage work is done).
     */
    public function complete(EncounterQueueTransition $transition): EncounterQueueTransition
    {
        $transition->update(['status' => QueueTransitionStatus::Completed->value]);
        return $transition->fresh();
    }

    /**
     * Retrieves the currently open (queued or received) transition for the
     * encounter's current stage, or null if none exists.
     */
    public function getOpenTransition(Encounter $encounter): ?EncounterQueueTransition
    {
        return $encounter->queueTransitions()
            ->where('to_stage', $encounter->current_stage->value)
            ->whereIn('status', [
                QueueTransitionStatus::Queued->value,
                QueueTransitionStatus::Received->value,
            ])
            ->latest()
            ->first();
    }

    /**
     * Returns all transitions for an encounter, ordered chronologically.
     */
    public function getTimeline(Encounter $encounter): \Illuminate\Database\Eloquent\Collection
    {
        return $encounter->queueTransitions()->orderBy('queued_at')->get();
    }
}
