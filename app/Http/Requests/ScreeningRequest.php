<?php

namespace App\Http\Requests;

class ScreeningRequest extends BaseEncounterRequest
{
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
}
