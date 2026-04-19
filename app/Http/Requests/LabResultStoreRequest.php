<?php

namespace App\Http\Requests;

class LabResultStoreRequest extends BaseEncounterRequest
{
    public function rules(): array
    {
        return [
            'results'                          => ['required', 'array', 'min:1'],
            'results.*.lab_request_item_id'    => ['nullable', 'integer', 'exists:lab_request_items,id'],
            'results.*.result_value'           => ['nullable', 'string', 'max:200'],
            'results.*.result_text'            => ['nullable', 'string', 'max:2000'],
            'results.*.reference_range'        => ['nullable', 'string', 'max:200'],
            'results.*.interpretation'         => ['nullable', 'string', 'in:normal,abnormal,critical,inconclusive'],
            'results.*.remarks'                => ['nullable', 'string', 'max:1000'],

            // Handover note for the screening review clinician
            'notes'                            => ['nullable', 'string', 'max:1000'],
        ];
    }
}
