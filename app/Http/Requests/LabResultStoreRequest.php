<?php

namespace App\Http\Requests;

use App\Models\Encounter;
use Illuminate\Validation\Rule;

class LabResultStoreRequest extends BaseEncounterRequest
{
    public function rules(): array
    {
        $labRequestId = null;
        $encounter = $this->route('encounter');

        if ($encounter instanceof Encounter) {
            $labRequestId = $encounter->labRequest()->value('id');
        }

        return [
            'results'                          => ['required', 'array', 'min:1'],
            'results.*.lab_request_item_id'    => [
                'nullable',
                'integer',
                Rule::exists('lab_request_items', 'id')
                    ->where('lab_request_id', $labRequestId),
            ],
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
