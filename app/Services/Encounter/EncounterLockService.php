<?php

namespace App\Services\Encounter;

use App\Exceptions\Encounter\EncounterLockedException;
use App\Models\Encounter;

class EncounterLockService
{
    public function isLocked(Encounter $encounter): bool
    {
        return $encounter->is_locked;
    }

    /**
     * Throws EncounterLockedException if the encounter is locked.
     * Call this at the start of every write operation.
     */
    public function assertNotLocked(Encounter $encounter): void
    {
        if ($encounter->is_locked) {
            throw new EncounterLockedException($encounter->encounter_number);
        }
    }

    /**
     * Locks the encounter permanently.
     * Should only be called by CloseEncounterAction (Phase 6).
     */
    public function lock(Encounter $encounter): Encounter
    {
        $encounter->update(['is_locked' => true]);
        return $encounter->fresh();
    }
}
