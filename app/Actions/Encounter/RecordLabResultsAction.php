<?php

namespace App\Actions\Encounter;

use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabResult;
use Illuminate\Support\Facades\DB;

/**
 * Records lab test results against an open lab request.
 *
 * Each result entry may optionally be linked to a specific LabRequestItem.
 * After recording, that item's status is updated to 'resulted'.
 *
 * @param array{
 *   results: array<array{
 *     lab_request_item_id?: int|null,
 *     result_value?: string|null,
 *     result_text?: string|null,
 *     reference_range?: string|null,
 *     interpretation?: string|null,
 *     remarks?: string|null,
 *   }>
 * } $data
 */
class RecordLabResultsAction
{
    /**
     * @param  array{ results: array<array{...}> } $data
     * @return LabResult[]
     */
    public function handle(LabRequest $labRequest, array $data, int $recordedById): array
    {
        return DB::transaction(function () use ($labRequest, $data, $recordedById): array {

            if ($labRequest->isCompleted()) {
                throw new \RuntimeException('Cannot record results on a completed lab request.');
            }

            $created = [];

            foreach ($data['results'] as $row) {
                $result = LabResult::create([
                    'lab_request_id'      => $labRequest->id,
                    'lab_request_item_id' => $row['lab_request_item_id'] ?? null,
                    'encounter_id'        => $labRequest->encounter_id,
                    'patient_id'          => $labRequest->patient_id,
                    'recorded_by'         => $recordedById,
                    'result_value'        => $row['result_value']    ?? null,
                    'result_text'         => $row['result_text']     ?? null,
                    'reference_range'     => $row['reference_range'] ?? null,
                    'interpretation'      => $row['interpretation']  ?? null,
                    'remarks'             => $row['remarks']         ?? null,
                    'result_status'       => 'resulted',
                    'result_recorded_at'  => now(),
                ]);

                // Mark the linked item as resulted
                if (! empty($row['lab_request_item_id'])) {
                    LabRequestItem::where('id', $row['lab_request_item_id'])
                        ->where('lab_request_id', $labRequest->id)
                        ->update(['status' => 'resulted']);
                }

                $created[] = $result;
            }

            return $created;
        });
    }
}
