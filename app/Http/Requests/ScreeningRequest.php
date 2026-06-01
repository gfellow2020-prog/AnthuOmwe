<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

class ScreeningRequest extends BaseEncounterRequest
{
    protected function prepareForValidation(): void
    {
        if (is_array($this->input('prescriptions'))) {
            $this->merge([
                'prescriptions' => json_encode($this->input('prescriptions')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            // ── Complaints & Histories ────────────────────────────────────
            'complaints'                    => ['nullable', 'string', 'max:3000'],
            'tb_symptoms'                   => ['nullable', 'array'],
            'tb_symptoms.*'                 => ['string', 'max:100'],
            'constitutional_symptoms'       => ['nullable', 'string', 'max:255'],
            'presumptive_tb_case_no'        => ['nullable', 'string', 'max:100'],
            'review_of_systems'             => ['nullable', 'string', 'max:5000'],
            'history_of_presenting_illness' => ['nullable', 'string', 'max:5000'],
            'past_medical_history'          => ['nullable', 'string', 'max:5000'],
            'medication_history'            => ['nullable', 'string', 'max:3000'],
            'allergy_history'               => ['nullable', 'string', 'max:3000'],
            'chronic_conditions'            => ['nullable', 'string', 'max:3000'],
            'family_history'                => ['nullable', 'string', 'max:3000'],
            'social_history'                => ['nullable', 'string', 'max:3000'],

            // ── Paediatric History ────────────────────────────────────────
            'birth_weight'                  => ['nullable', 'numeric', 'min:0.1', 'max:15'],
            'birth_length'                  => ['nullable', 'numeric', 'min:1', 'max:100'],
            'head_circumference'            => ['nullable', 'numeric', 'min:1', 'max:100'],
            'chest_circumference'           => ['nullable', 'numeric', 'min:1', 'max:100'],
            'general_condition'             => ['nullable', 'string', 'max:100'],
            'is_breast_feeding_well'        => ['nullable', 'boolean'],
            'other_feeding_option'          => ['nullable', 'string', 'max:100'],
            'delivery_time'                 => ['nullable', 'string', 'max:20'],
            'vaccination_outside'           => ['nullable', 'string', 'max:255'],
            'tetanus_at_birth'              => ['nullable', 'string', 'max:100'],
            'birth_outcome'                 => ['nullable', 'string', 'max:100'],
            'birth_notes'                   => ['nullable', 'string', 'max:3000'],
            'immunization_history'          => ['nullable', 'string', 'max:5000'],
            'feeding_code'                  => ['nullable', 'string', 'max:150'],
            'feeding_comments'              => ['nullable', 'string', 'max:3000'],
            'development_history'           => ['nullable', 'string', 'max:5000'],

            // ── Examination ───────────────────────────────────────────────
            'physical_examination'          => ['nullable', 'string', 'max:5000'],
            'clinical_findings'             => ['nullable', 'string', 'max:5000'],

            // ── Diagnosis & plan ──────────────────────────────────────────
            'provisional_diagnosis'         => ['nullable', 'string', 'max:2000'],
            'final_diagnosis'               => ['nullable', 'string', 'max:2000'],
            'assessment_notes'              => ['nullable', 'string', 'max:3000'],
            'plan'                          => ['nullable', 'string', 'max:3000'],
            'treatment_plan'                => ['nullable', 'string', 'max:3000'],

            // ── Lab request flag ──────────────────────────────────────────
            'lab_requested'                 => ['nullable', 'boolean'],

            // ── Prescriptions (JSON-encoded cart) ─────────────────────────
            'prescriptions'                 => ['nullable', 'string'],

            // ── Handover note ─────────────────────────────────────────────
            'notes'                         => ['nullable', 'string', 'max:1000'],

            // ── Staff assignments (optional) ──────────────────────────────
            'staff_assignments'             => ['nullable', 'array'],
            'staff_assignments.*.user_id'   => ['required_with:staff_assignments', 'integer', 'exists:users,id'],
            'staff_assignments.*.role_name' => ['nullable', 'string', 'max:60'],
            'staff_assignments.*.participation_type' => ['nullable', 'string', 'max:60'],
            'staff_assignments.*.notes'     => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $rawPrescriptions = $this->input('prescriptions');

            if ($rawPrescriptions === null || $rawPrescriptions === '') {
                return;
            }

            if (! is_string($rawPrescriptions)) {
                return;
            }

            $decodedPrescriptions = json_decode($rawPrescriptions, true);

            if (
                json_last_error() !== JSON_ERROR_NONE
                || ! is_array($decodedPrescriptions)
                || ! array_is_list($decodedPrescriptions)
            ) {
                $validator->errors()->add('prescriptions', 'The prescriptions field must contain a valid JSON array.');

                return;
            }

            if ($decodedPrescriptions === []) {
                return;
            }

            $prescriptionValidator = ValidatorFacade::make(
                ['items' => $decodedPrescriptions],
                [
                    'items' => ['array', 'min:1'],
                    'items.*.drug_name' => ['required', 'string', 'max:255'],
                    'items.*.strength' => ['nullable', 'string', 'max:100'],
                    'items.*.formulation' => ['nullable', 'string', 'max:100'],
                    'items.*.dose' => ['required', 'string', 'max:100'],
                    'items.*.item_per_dose' => ['nullable', 'integer', 'min:0'],
                    'items.*.frequency' => ['required', 'string', 'max:100'],
                    'items.*.time_per' => ['nullable', 'string', 'max:100'],
                    'items.*.frequency_unit' => ['nullable', 'string', 'max:100'],
                    'items.*.duration' => ['required', 'string', 'max:100'],
                    'items.*.duration_unit' => ['nullable', 'string', 'max:100'],
                    'items.*.start_date' => ['nullable', 'date'],
                    'items.*.end_date' => ['nullable', 'date'],
                    'items.*.quantity_prescribed' => ['required', 'integer', 'min:1'],
                    'items.*.route' => ['nullable', 'string', 'max:100'],
                    'items.*.is_passer_by' => ['nullable', 'boolean'],
                    'items.*.instructions' => ['nullable', 'string', 'max:500'],
                ],
            );

            if ($prescriptionValidator->fails()) {
                $validator->errors()->add('prescriptions', 'Each prescription item must include a drug name, dose, frequency, duration, and quantity.');
            }
        });
    }
}
