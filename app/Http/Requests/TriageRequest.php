<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class TriageRequest extends BaseEncounterRequest
{
    public function rules(): array
    {
        return [
            // ── Vitals ──────────────────────────────────────────────────────
            'weight'            => ['nullable', 'numeric', 'min:0.5', 'max:300'],
            'height'            => ['nullable', 'numeric', 'min:30',  'max:250'],
            'temperature'       => ['nullable', 'numeric', 'min:30',  'max:45'],
            'pulse'             => ['nullable', 'integer', 'min:20',  'max:300'],
            'respiratory_rate'  => ['nullable', 'integer', 'min:5',   'max:80'],
            'systolic_bp'       => ['nullable', 'integer', 'min:50',  'max:300'],
            'diastolic_bp'      => ['nullable', 'integer', 'min:20',  'max:200'],
            'oxygen_saturation' => ['nullable', 'numeric', 'min:50',  'max:100'],
            'blood_sugar'       => ['nullable', 'numeric', 'min:0.5', 'max:50'],
            'pain_scale'        => ['nullable', 'integer', 'min:0',   'max:10'],

            // ── Clinical notes ───────────────────────────────────────────────
            'chief_complaint_brief'       => ['nullable', 'string', 'max:500'],
            'startup_interventions_notes' => ['nullable', 'string', 'max:2000'],
            'startup_medications_notes'   => ['nullable', 'string', 'max:2000'],
            'triage_notes'                => ['nullable', 'string', 'max:2000'],

            // ── Queue-to-screening handover note ────────────────────────────
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'weight.min'            => 'Weight must be at least 0.5 kg.',
            'weight.max'            => 'Weight cannot exceed 300 kg.',
            'height.min'            => 'Height must be at least 30 cm.',
            'height.max'            => 'Height cannot exceed 250 cm.',
            'temperature.min'       => 'Temperature must be at least 30 °C.',
            'temperature.max'       => 'Temperature cannot exceed 45 °C.',
            'pulse.min'             => 'Pulse must be at least 20 bpm.',
            'pulse.max'             => 'Pulse cannot exceed 300 bpm.',
            'oxygen_saturation.min' => 'SpO₂ must be at least 50%.',
            'oxygen_saturation.max' => 'SpO₂ cannot exceed 100%.',
            'pain_scale.min'        => 'Pain scale must be between 0 and 10.',
            'pain_scale.max'        => 'Pain scale must be between 0 and 10.',
        ];
    }
}
