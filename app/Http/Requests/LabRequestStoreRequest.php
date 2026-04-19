<?php

namespace App\Http\Requests;

class LabRequestStoreRequest extends BaseEncounterRequest
{
    public function rules(): array
    {
        return [
            // ── Request items (tests ordered) ─────────────────────────────
            'items'                     => ['nullable', 'array'],
            'items.*.test_name'         => ['required_with:items', 'string', 'max:200'],
            'items.*.test_code'         => ['nullable', 'string', 'max:60'],
            'items.*.specimen_type'     => ['nullable', 'string', 'max:100'],
            'items.*.test_group'        => ['nullable', 'string', 'max:100'],
            'items.*.instructions'      => ['nullable', 'string', 'max:500'],

            // ── Sample collection ─────────────────────────────────────────
            'samples'                   => ['nullable', 'array'],
            'samples.*.sample_type'     => ['required_with:samples', 'string', 'max:100'],
            'samples.*.sample_label'    => ['nullable', 'string', 'max:100'],
            'samples.*.collection_notes'=> ['nullable', 'string', 'max:500'],

            // ── Request-level notes ───────────────────────────────────────
            'request_notes'             => ['nullable', 'string', 'max:2000'],
            'priority_level'            => ['nullable', 'string', 'in:normal,urgent,stat'],
        ];
    }
}
