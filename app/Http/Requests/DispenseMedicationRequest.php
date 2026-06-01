<?php

namespace App\Http\Requests;

class DispenseMedicationRequest extends BaseEncounterRequest
{
    public function rules(): array
    {
        return [
            'dispensing_notes'   => ['nullable', 'string'],
            'counseling_notes'   => ['nullable', 'string'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.drug_name'  => ['required', 'string', 'max:255'],
            'items.*.quantity_dispensed' => ['required', 'integer', 'min:1'],
            'items.*.pharmacy_prescription_item_id' => ['nullable', 'integer'],
            'items.*.batch_no'          => ['nullable', 'string', 'max:100'],
            'items.*.stock_reference'   => ['nullable', 'string', 'max:100'],
            'items.*.instructions'      => ['nullable', 'string'],
        ];
    }
}
