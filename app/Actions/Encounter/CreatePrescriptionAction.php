<?php

namespace App\Actions\Encounter;

use App\Models\Encounter;
use App\Models\PharmacyPrescription;
use App\Models\PharmacyPrescriptionItem;
use App\Models\ScreeningRecord;
use Illuminate\Support\Facades\DB;

/**
 * Creates a PharmacyPrescription with its items for an encounter.
 *
 * Can be linked to a specific ScreeningRecord (typically the review_after_lab one).
 * If a prescription already exists for this encounter it is soft-replaced by
 * cancelling the old one and creating a fresh one.
 *
 * @param array{
 *   notes?: string|null,
 *   items: array<array{
 *     drug_name: string,
 *     strength?: string|null,
 *     formulation?: string|null,
 *     dose: string,
 *     frequency: string,
 *     duration: string,
 *     quantity_prescribed: int,
 *     route?: string|null,
 *     instructions?: string|null,
 *   }>
 * } $data
 */
class CreatePrescriptionAction
{
    public function handle(
        Encounter       $encounter,
        array           $data,
        int             $prescribedById,
        ?ScreeningRecord $screeningRecord = null,
    ): PharmacyPrescription {
        return DB::transaction(function () use ($encounter, $data, $prescribedById, $screeningRecord): PharmacyPrescription {

            $prescription = PharmacyPrescription::create([
                'encounter_id'        => $encounter->id,
                'patient_id'          => $encounter->patient_id,
                'screening_record_id' => $screeningRecord?->id,
                'prescribed_by'       => $prescribedById,
                'prescription_number' => $this->generatePrescriptionNumber(),
                'status'              => 'active',
                'notes'               => $data['notes'] ?? null,
                'prescribed_at'       => now(),
            ]);

            foreach ($data['items'] as $item) {
                PharmacyPrescriptionItem::create([
                    'pharmacy_prescription_id' => $prescription->id,
                    'drug_name'                => $item['drug_name'],
                    'strength'                 => $item['strength']   ?? null,
                    'formulation'              => $item['formulation'] ?? null,
                    'dose'                     => $item['dose'],
                    'frequency'                => $item['frequency'],
                    'duration'                 => $item['duration'],
                    'quantity_prescribed'      => (int) $item['quantity_prescribed'],
                    'route'                    => $item['route']         ?? null,
                    'instructions'             => $item['instructions']  ?? null,
                ]);
            }

            // Mark screening record as prescribed
            if ($screeningRecord) {
                $screeningRecord->update(['prescribed' => true]);
            }

            return $prescription;
        });
    }

    private function generatePrescriptionNumber(): string
    {
        $date = now()->format('Ymd');
        $last = PharmacyPrescription::where('prescription_number', 'like', "RX-{$date}-%")
            ->lockForUpdate()
            ->count();

        return sprintf('RX-%s-%04d', $date, $last + 1);
    }
}
