<?php

namespace App\Services\Encounter;

use App\Models\Encounter;

/**
 * Loads an encounter with ALL related models needed for the profile page.
 * Designed to minimize N+1 queries.
 */
class EncounterDetailService
{
    public function load(Encounter $encounter): Encounter
    {
        $encounter->loadMissing([
            // Core
            'patient',
            'startedBy',
            'closedBy',

            // Stage-specific records
            'registrationRecord.registrar',

            'triageRecord.nurse',

            'screeningRecord.clinician',

            'screeningReviewRecord.clinician',

            'labRequest.requestedBy',
            'labRequest.items',
            'labRequest.samples.collectedBy',
            'labRequest.results.recordedBy',
            'labRequest.results.verifiedBy',

            'prescription.prescribedBy',
            'prescription.items',

            'dispense.dispensedBy',
            'dispense.items',

            // Workflow logs
            'stageLogs.startedBy',
            'stageLogs.completedBy',
            'queueTransitions.queuedBy',
            'queueTransitions.receivedBy',

            // Audit trail
            'audits.actionBy',
        ]);

        return $encounter;
    }

    /**
     * Paginated list of all encounters with minimal eager loading for the index page.
     */
    public function list(int $perPage = 25): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Encounter::with(['patient', 'startedBy'])
            ->orderByDesc('started_at')
            ->paginate($perPage);
    }
}
