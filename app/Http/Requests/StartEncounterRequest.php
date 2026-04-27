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
            'nrc_number'      => [
                'nullable', 'string', 'max:30',
                $isNewPatient ? Rule::unique('patients', 'nrc_number') : 'nullable',
            ],
            'phone_number'    => [
                'nullable', 'string', 'max:30',
                $isNewPatient ? Rule::unique('patients', 'phone_number') : 'nullable',
            ],
            'email'           => ['nullable', 'email', 'max:255'],
            'create_household'=> ['nullable', 'boolean'],
            'household_id'    => ['nullable', 'string', 'exists:households,household_id'],
            'village'         => [
                Rule::requiredIf(fn () => $isNewPatient && $this->boolean('create_household')),
                'nullable',
                'string',
                'max:255',
                'exists:villages,name',
            ],
            'payment_plan'    => ['nullable', Rule::in(['monthly', 'annual'])],
            'payment_mode'    => ['nullable', Rule::in(['cash', 'mobile_money'])],
            'payment_amount'  => ['nullable', 'integer', Rule::in([500, 6000])],

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
            'nrc_number.unique' => 'This NRC number is already registered to another patient.',
            'phone_number.unique' => 'This phone number is already registered to another patient.',
            'village.required' => 'Please select a village for the household.',
            'village.exists' => 'Please select a village from the list.',
            'payment_plan.required' => 'Please choose household payment plan.',
            'payment_plan.in' => 'Payment plan must be monthly or annual.',
            'payment_mode.required' => 'Please choose payment mode.',
            'payment_mode.in' => 'Payment mode must be cash or mobile money.',
            'payment_amount.required' => 'Household payment amount is required.',
            'payment_amount.in' => 'Payment amount must be K500 (monthly) or K6000 (annual).',
            'priority_level.in' => 'Priority must be normal, urgent, or emergency.',
        ];
    }
}
