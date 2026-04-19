<?php

namespace App\Actions\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Models\Encounter;
use App\Models\TriageRecord;
use App\Services\Encounter\EncounterAuditService;
use App\Services\Encounter\EncounterLockService;
use App\Services\Encounter\EncounterWorkflowService;
use Illuminate\Support\Facades\DB;

/**
 * Records triage vitals and clinical notes.
 * Must be called while the encounter is in-progress at triage.
 * Does NOT advance the stage — that is done by QueueEncounterToScreeningAction.
 */
class RecordTriageAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterAuditService    $auditService,
        private readonly EncounterLockService     $lockService,
    ) {}

    /**
     * @param  array{
     *   weight?: float|null,
     *   height?: float|null,
     *   temperature?: float|null,
     *   pulse?: int|null,
     *   respiratory_rate?: int|null,
     *   systolic_bp?: int|null,
     *   diastolic_bp?: int|null,
     *   oxygen_saturation?: float|null,
     *   blood_sugar?: float|null,
     *   pain_scale?: int|null,
     *   chief_complaint_brief?: string|null,
     *   startup_interventions_notes?: string|null,
     *   startup_medications_notes?: string|null,
     *   triage_notes?: string|null,
     * } $data
     */
    public function handle(Encounter $encounter, array $data, int $nurseId): TriageRecord
    {
        return DB::transaction(function () use ($encounter, $data, $nurseId): TriageRecord {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::Triage);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::InProgress);

            // Compute BMI automatically if weight and height are supplied
            $bmi = TriageRecord::computeBmi(
                $data['weight'] ?? null,
                $data['height'] ?? null,
            );

            // Upsert — allows nurse to update before queuing to screening
            $record = TriageRecord::updateOrCreate(
                ['encounter_id' => $encounter->id],
                [
                    'patient_id'                 => $encounter->patient_id,
                    'nurse_id'                   => $nurseId,
                    'weight'                     => $data['weight']            ?? null,
                    'height'                     => $data['height']            ?? null,
                    'bmi'                        => $bmi,
                    'temperature'                => $data['temperature']       ?? null,
                    'pulse'                      => $data['pulse']             ?? null,
                    'respiratory_rate'           => $data['respiratory_rate']  ?? null,
                    'systolic_bp'                => $data['systolic_bp']       ?? null,
                    'diastolic_bp'               => $data['diastolic_bp']      ?? null,
                    'oxygen_saturation'          => $data['oxygen_saturation'] ?? null,
                    'blood_sugar'                => $data['blood_sugar']       ?? null,
                    'pain_scale'                 => $data['pain_scale']        ?? null,
                    'chief_complaint_brief'      => $data['chief_complaint_brief']      ?? null,
                    'startup_interventions_notes'=> $data['startup_interventions_notes'] ?? null,
                    'startup_medications_notes'  => $data['startup_medications_notes']   ?? null,
                    'triage_notes'               => $data['triage_notes']      ?? null,
                    'triage_at'                  => now(),
                ],
            );

            $this->auditService->record(
                encounter:   $encounter,
                actionName:  'triage_vitals_recorded',
                actionStage: EncounterStage::Triage,
                actionBy:    $nurseId,
                newValues:   array_filter([
                    'weight'      => $data['weight']      ?? null,
                    'temperature' => $data['temperature'] ?? null,
                    'pulse'       => $data['pulse']       ?? null,
                    'bmi'         => $bmi,
                ]),
            );

            return $record;
        });
    }
}
