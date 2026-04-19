<?php

namespace App\Actions\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Enums\QueueTransitionStatus;
use App\Models\Encounter;
use App\Models\EncounterStageLog;
use App\Models\Patient;
use App\Models\RegistrationRecord;
use App\Services\Encounter\EncounterAuditService;
use Illuminate\Support\Facades\DB;

/**
 * Creates an encounter, opens the registration stage log, and
 * writes a registration record — all inside a single DB transaction.
 */
class StartEncounterAction
{
    public function __construct(
        private readonly RegisterOrAttachPatientAction $registerOrAttach,
        private readonly EncounterAuditService         $auditService,
    ) {}

    /**
     * @param  array{
     *   patient_id?: int|null,
     *   full_name?: string,
     *   gender?: string|null,
     *   date_of_birth?: string|null,
     *   nrc_number?: string|null,
     *   phone_number?: string|null,
     *   email?: string|null,
     *   visit_type?: string|null,
     *   priority_level?: string|null,
     *   registration_notes?: string|null,
     *   search_reference?: string|null,
     * } $data
     * @param  int  $registrarId  Authenticated user ID
     */
    public function handle(array $data, int $registrarId): Encounter
    {
        return DB::transaction(function () use ($data, $registrarId): Encounter {

            // 1. Find or create patient
            ['patient' => $patient, 'was_existing' => $wasExisting] =
                $this->registerOrAttach->handle($data);

            // 2. Create encounter
            $encounter = Encounter::create([
                'encounter_number' => $this->generateEncounterNumber(),
                'patient_id'       => $patient->id,
                'current_stage'    => EncounterStage::Registration,
                'current_status'   => EncounterStatus::Started,
                'visit_type'       => $data['visit_type']    ?? null,
                'priority_level'   => $data['priority_level'] ?? null,
                'started_at'       => now(),
                'started_by'       => $registrarId,
            ]);

            // 3. Open registration stage log
            EncounterStageLog::create([
                'encounter_id'   => $encounter->id,
                'patient_id'     => $patient->id,
                'stage_name'     => EncounterStage::Registration->value,
                'stage_sequence' => EncounterStage::Registration->sequence(),
                'status'         => QueueTransitionStatus::Received->value,
                'started_by'     => $registrarId,
                'started_at'     => now(),
            ]);

            // 4. Write registration record
            RegistrationRecord::create([
                'encounter_id'        => $encounter->id,
                'patient_id'          => $patient->id,
                'registrar_id'        => $registrarId,
                'was_existing_patient'=> $wasExisting,
                'search_reference'    => $data['search_reference'] ?? null,
                'registration_notes'  => $data['registration_notes'] ?? null,
                'registered_at'       => now(),
            ]);

            // 5. Audit
            $this->auditService->record(
                encounter:   $encounter,
                actionName:  'encounter_started',
                actionStage: EncounterStage::Registration,
                actionBy:    $registrarId,
                newValues:   [
                    'encounter_number' => $encounter->encounter_number,
                    'patient_id'       => $patient->patient_id,
                    'was_existing'     => $wasExisting,
                ],
            );

            return $encounter->load(['patient', 'registrationRecord', 'stageLogs', 'audits']);
        });
    }

    private function generateEncounterNumber(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "ENC-{$date}-";

        $latest = Encounter::where('encounter_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('encounter_number');

        $seq = $latest ? ((int) substr($latest, -5) + 1) : 1;

        return $prefix . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }
}
