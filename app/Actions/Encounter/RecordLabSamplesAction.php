<?php

namespace App\Actions\Encounter;

use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabSample;
use Illuminate\Support\Facades\DB;

/**
 * Records sample collection against an open lab request.
 *
 * Accepts an array of samples (one per specimen) and optionally
 * updates the status of linked request items to 'collected'.
 *
 * @param array{
 *   samples: array<array{
 *     sample_type: string,
 *     sample_label?: string|null,
 *     collection_notes?: string|null,
 *   }>,
 *   item_ids?: int[],   — optional: mark these items as 'collected'
 * } $data
 */
class RecordLabSamplesAction
{
    /**
     * @param  array{ samples: array<array{sample_type:string,...}>, item_ids?: int[] } $data
     * @return LabSample[]
     */
    public function handle(LabRequest $labRequest, array $data, int $collectedById): array
    {
        return DB::transaction(function () use ($labRequest, $data, $collectedById): array {

            if ($labRequest->isCompleted()) {
                throw new \RuntimeException('Cannot add samples to a completed lab request.');
            }

            $created = [];

            foreach ($data['samples'] as $sample) {
                $created[] = LabSample::create([
                    'lab_request_id'   => $labRequest->id,
                    'encounter_id'     => $labRequest->encounter_id,
                    'patient_id'       => $labRequest->patient_id,
                    'collected_by'     => $collectedById,
                    'sample_type'      => $sample['sample_type'],
                    'sample_label'     => $sample['sample_label']      ?? null,
                    'collection_notes' => $sample['collection_notes']  ?? null,
                    'collected_at'     => now(),
                ]);
            }

            // Optionally mark request items as collected
            if (! empty($data['item_ids'])) {
                LabRequestItem::whereIn('id', $data['item_ids'])
                    ->where('lab_request_id', $labRequest->id)
                    ->update(['status' => 'collected']);
            }

            return $created;
        });
    }
}
