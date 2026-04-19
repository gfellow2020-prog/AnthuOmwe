<?php

namespace App\Http\Requests;

class ScreeningRequest extends BaseEncounterRequest
{
    public function rules(): array
    {
        return [
            // ── Clinical history ──────────────────────────────────────────
            'complaints'                    => ['nullable', 'string', 'max:3000'],
            'history_of_presenting_illness' => ['nullable', 'string', 'max:5000'],
            'past_medical_history'          => ['nullable', 'string', 'max:5000'],
            'medication_history'            => ['nullable', 'string', 'max:3000'],
            'allergy_history'               => ['nullable', 'string', 'max:3000'],

            // ── Examination ───────────────────────────────────────────────
            'physical_examination'          => ['nullable', 'string', 'max:5000'],
            'clinical_findings'             => ['nullable', 'string', 'max:5000'],

            // ── Diagnosis & plan ──────────────────────────────────────────
            'provisional_diagnosis'         => ['nullable', 'string', 'max:2000'],
            'final_diagnosis'               => ['nullable', 'string', 'max:2000'],
            'assessment_notes'              => ['nullable', 'string', 'max:3000'],
            'plan'                          => ['nullable', 'string', 'max:3000'],

            // ── Lab request flag ──────────────────────────────────────────
            'lab_requested'                 => ['nullable', 'boolean'],

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
