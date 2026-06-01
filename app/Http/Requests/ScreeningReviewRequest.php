<?php

namespace App\Http\Requests;

class ScreeningReviewRequest extends BaseEncounterRequest
{
    public function rules(): array
    {
        return [
            'final_diagnosis'       => ['required', 'string', 'max:1000'],
            'clinical_findings'     => ['nullable', 'string', 'max:2000'],
            'physical_examination'  => ['nullable', 'string', 'max:2000'],
            'assessment_notes'      => ['nullable', 'string', 'max:2000'],
            'plan'                  => ['nullable', 'string', 'max:2000'],
            'review_notes'          => ['nullable', 'string', 'max:2000'],

            // Prescription ─────────────────────────────────────────────────
            'prescription_notes'    => ['nullable', 'string', 'max:1000'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.drug_name'     => ['required', 'string', 'max:255'],
            'items.*.strength'      => ['nullable', 'string', 'max:100'],
            'items.*.formulation'   => ['nullable', 'string', 'max:100'],
            'items.*.dose'          => ['required', 'string', 'max:100'],
            'items.*.frequency'     => ['required', 'string', 'max:100'],
            'items.*.duration'      => ['required', 'string', 'max:100'],
            'items.*.quantity_prescribed' => ['required', 'integer', 'min:1'],
            'items.*.route'         => ['nullable', 'string', 'max:100'],
            'items.*.instructions'  => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'final_diagnosis.required'          => 'A final diagnosis is required before queuing to pharmacy.',
            'items.required'                     => 'At least one prescription item is required.',
            'items.min'                          => 'At least one prescription item is required.',
            'items.*.drug_name.required'         => 'Each prescription item needs a drug name.',
            'items.*.dose.required'              => 'Each item needs a dose.',
            'items.*.frequency.required'         => 'Each item needs a frequency.',
            'items.*.duration.required'          => 'Each item needs a duration.',
            'items.*.quantity_prescribed.required'=> 'Each item needs a quantity.',
        ];
    }
}
