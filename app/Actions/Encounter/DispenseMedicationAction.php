<?php

namespace App\Actions\Encounter;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Models\Encounter;
use App\Models\PharmacyDispense;
use App\Models\PharmacyDispenseItem;
use App\Services\Encounter\EncounterLockService;
use App\Services\Encounter\EncounterWorkflowService;
use Illuminate\Support\Facades\DB;

/**
 * Records medication dispensing against the active prescription.
 *
 * Works directly on the encounter; does NOT advance the stage.
 * Call CloseEncounterAction separately after dispensing.
 *
 * @param array{
 *   dispensing_notes?: string|null,
 *   counseling_notes?: string|null,
 *   items: array<array{
 *     pharmacy_prescription_item_id?: int|null,
 *     drug_name: string,
 *     quantity_dispensed: int,
 *     batch_no?: string|null,
 *     stock_reference?: string|null,
 *     instructions?: string|null,
 *   }>
 * } $data
 */
class DispenseMedicationAction
{
    public function __construct(
        private readonly EncounterWorkflowService $workflowService,
        private readonly EncounterLockService     $lockService,
    ) {}

    public function handle(Encounter $encounter, array $data, int $pharmacistId): PharmacyDispense
    {
        return DB::transaction(function () use ($encounter, $data, $pharmacistId): PharmacyDispense {

            $this->lockService->assertNotLocked($encounter);
            $this->workflowService->assertStageIs($encounter, EncounterStage::Pharmacy);
            $this->workflowService->assertStatusIs($encounter, EncounterStatus::InProgress);

            $encounter->loadMissing('prescription');

            $dispense = PharmacyDispense::create([
                'encounter_id'              => $encounter->id,
                'patient_id'                => $encounter->patient_id,
                'pharmacy_prescription_id'  => $encounter->prescription?->id,
                'dispensed_by'              => $pharmacistId,
                'dispensing_notes'          => $data['dispensing_notes'] ?? null,
                'counseling_notes'          => $data['counseling_notes'] ?? null,
                'dispensed_at'              => now(),
            ]);

            foreach ($data['items'] as $item) {
                PharmacyDispenseItem::create([
                    'pharmacy_dispense_id'          => $dispense->id,
                    'pharmacy_prescription_item_id' => $item['pharmacy_prescription_item_id'] ?? null,
                    'drug_name'                     => $item['drug_name'],
                    'quantity_dispensed'            => (int) $item['quantity_dispensed'],
                    'batch_no'                      => $item['batch_no']         ?? null,
                    'stock_reference'               => $item['stock_reference']  ?? null,
                    'instructions'                  => $item['instructions']     ?? null,
                ]);
            }

            // Mark prescription as dispensed
            if ($encounter->prescription) {
                $encounter->prescription->update(['status' => 'dispensed']);
            }

            return $dispense;
        });
    }
}
