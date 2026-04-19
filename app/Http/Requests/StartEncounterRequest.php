<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StartEncounterRequest extends BaseEncounterRequest
{
    public function rules(): array
    {
        $isNewPatient = empty($this->input('patient_id'));

        return [
            // ── Patient identification ──────────────────────────────────────
            // Supply patient_id (integer FK) to reuse an existing patient,
            // OR supply full_name (and optional demographics) to create one.
            'patient_id'      => ['nullable', 'integer', 'exists:patients,id'],

            // Required only when creating a new patient
            'full_name'       => [$isNewPatient ? 'required' : 'nullable', 'string', 'max:255'],
            'gender'          => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth'   => ['nullable', 'date', 'before:today'],
            'nrc_number'      => ['nullable', 'string', 'max:30'],
            'phone_number'    => ['nullable', 'string', 'max:30'],
            'email'           => ['nullable', 'email', 'max:255'],

            // ── Encounter metadata ──────────────────────────────────────────
            'visit_type'      => ['nullable', 'string', 'max:100'],
            'priority_level'  => ['nullable', Rule::in(['normal', 'urgent', 'emergency'])],

            // ── Registration context ────────────────────────────────────────
            'search_reference'    => ['nullable', 'string', 'max:255'],
            'registration_notes'  => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.exists' => 'The selected patient does not exist in the system.',
            'full_name.required'=> 'A patient name is required when registering a new patient.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'priority_level.in' => 'Priority must be normal, urgent, or emergency.',
        ];
    }
}
