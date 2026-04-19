<?php

namespace App\Services\Encounter;

use App\Enums\EncounterStage;
use App\Models\Encounter;
use App\Models\EncounterAudit;

class EncounterAuditService
{
    /**
     * Records an audit entry for any encounter workflow action.
     *
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function record(
        Encounter     $encounter,
        string        $actionName,
        EncounterStage $actionStage,
        int            $actionBy,
        array          $oldValues = [],
        array          $newValues = [],
        ?string        $notes = null,
    ): EncounterAudit {
        return EncounterAudit::create([
            'encounter_id' => $encounter->id,
            'patient_id'   => $encounter->patient_id,
            'action_name'  => $actionName,
            'action_stage' => $actionStage->value,
            'action_by'    => $actionBy,
            'old_values'   => $oldValues ?: null,
            'new_values'   => $newValues ?: null,
            'notes'        => $notes,
            'action_at'    => now(),
        ]);
    }
}
