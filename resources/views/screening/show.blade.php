@extends('layouts.dashboard')

@section('title', 'Screening — ' . $encounter->encounter_number)

@push('styles')
<style>
    .field-label  { display:block; font-size:13px; font-weight:600; color:#171717; margin-bottom:4px; }
    .field-label .unit { font-weight:400; color:#a3a3a3; font-size:11px; margin-left:4px; }
    .field-input  { width:100%; padding:9px 13px; font-size:14px; border:1px solid #d4d4d4; border-radius:4px;
                    background:#fff; color:#171717; outline:none; transition:border-color .15s; }
    .field-input:focus  { border-color:#171717; box-shadow:0 0 0 3px rgba(23,23,23,.08); }
    .field-input::placeholder { color:#a3a3a3; }
    textarea.field-input { resize:vertical; min-height:70px; }
    .btn-primary  { display:inline-flex; align-items:center; gap:6px; padding:9px 22px; font-size:14px;
                    font-weight:600; background:#171717; color:#fff; border-radius:4px; border:none; cursor:pointer; }
    .btn-primary:hover  { background:#262626; }
    .btn-green    { display:inline-flex; align-items:center; gap:6px; padding:9px 22px; font-size:14px;
                    font-weight:600; background:#404040; color:#fff; border-radius:4px; border:none; cursor:pointer; }
    .btn-green:hover    { background:#525252; }
    .btn-secondary{ display:inline-flex; align-items:center; gap:6px; padding:9px 20px; font-size:14px;
                    font-weight:600; background:#f5f5f5; color:#374151; border-radius:4px; border:1px solid #d4d4d4; cursor:pointer; }
    .section-title{ font-size:12px; font-weight:700; color:#525252; text-transform:uppercase; letter-spacing:.05em;
                    margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid #e5e5e5; }
    .detail-row   { display:flex; gap:12px; padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13px; }
    .detail-row:last-child { border-bottom:none; }
    .detail-label { flex-shrink:0; width:140px; font-size:11px; font-weight:600; color:#737373; text-transform:uppercase; }

    /* Tab styling — dark pill bar (matches registration) */
    .tab-nav { display:flex; align-items:center; gap:4px; background:#171717; border-radius:4px; padding:4px; overflow-x:auto; }
    .tab-btn { display:flex; align-items:center; justify-content:center; gap:6px; flex:1; padding:8px 14px; font-size:12px;
               font-weight:600; color:#a3a3a3; border-radius:4px; cursor:pointer; white-space:nowrap;
               background:none; border:none; transition:all .2s; }
    .tab-btn:hover { color:#d4d4d4; }
    .tab-btn.active { background:#404040; color:#fff; box-shadow:0 1px 3px rgba(0,0,0,.3); }
    .tab-btn svg { width:14px; height:14px; flex-shrink:0; }
    .tab-panel { display:none; }
    .tab-panel.active { display:block; }

    /* Section card inside tabs */
    .section-card { background:#fafafa; border:1px solid #e5e5e5; border-radius:4px; padding:16px; margin-bottom:16px; }
    .section-card-title { font-size:13px; font-weight:600; color:#171717; margin-bottom:12px; display:flex; align-items:center; gap:8px; }
    .section-card-title svg { width:16px; height:16px; color:#525252; flex-shrink:0; }

    /* Checkbox grid for TB symptoms */
    .checkbox-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:8px; }
    .checkbox-label { display:flex; align-items:center; gap:8px; font-size:13px; color:#374151; cursor:pointer; padding:6px 10px;
                      border:1px solid #e5e5e5; border-radius:4px; background:#fff; transition:all .15s; }
    .checkbox-label:hover { border-color:#a3a3a3; }
    .checkbox-label input:checked + span { font-weight:600; color:#171717; }
    .checkbox-label:has(input:checked) { border-color:#171717; background:#f5f5f5; }
    main.blurred { filter: blur(3px); cursor: pointer; transition: filter .3s; }
</style>
@endpush

@section('navbar-extras')
<div class="relative flex justify-center">
    <button type="button" onclick="event.stopPropagation(); toggleReviewPanel()"
            class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-b-lg bg-neutral-900 hover:bg-neutral-800 text-white shadow-lg transition -mt-[1px] relative z-[999]">
        <svg id="reviewChevronIcon" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
        Passed Screening Review
    </button>

    {{-- ── Passed Screening Review Slide-down Panel ──── --}}
    <div id="reviewPanel" class="absolute top-full left-0 right-0 bg-white dark:bg-neutral-900 shadow-2xl z-[998] overflow-hidden transition-[max-height] duration-300 ease-in-out" style="max-height:0;">
        <div class="overflow-y-auto" style="max-height:50vh;">
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-wide">Passed Screening Review — Previous Encounters</h2>
            <button type="button" onclick="toggleReviewPanel()" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        @if($pastEncounters->isEmpty())
        <div class="text-center py-8">
            <svg class="w-10 h-10 text-neutral-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-neutral-400">No previous encounters for this patient</p>
        </div>
        @else
        @foreach($pastEncounters as $past)
        <div class="border border-neutral-200 dark:border-neutral-700 rounded-lg overflow-hidden">
            {{-- Encounter header --}}
            <div class="bg-neutral-50 dark:bg-neutral-800 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-sm font-bold text-neutral-900 dark:text-white">{{ $past->encounter_number }}</span>
                    <span class="text-xs text-neutral-500">{{ $past->visit_type ?? 'OPD' }} · {{ ucfirst($past->priority_level ?? 'Normal') }}</span>
                </div>
                <span class="text-xs text-neutral-400">{{ $past->started_at->format('d M Y H:i') }}</span>
            </div>

            <div class="p-4 space-y-4">
                {{-- Triage Vitals --}}
                @if($past->triageRecord)
                @php $pt = $past->triageRecord; @endphp
                <div>
                    <h4 class="text-[11px] font-semibold text-neutral-500 uppercase tracking-wide mb-2">Vital Signs</h4>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                        @foreach([
                            ['Weight',     $pt->weight ? $pt->weight.' kg' : '—'],
                            ['Height',     $pt->height ? $pt->height.' cm' : '—'],
                            ['BMI',        $pt->bmi ?? '—'],
                            ['Temp',       $pt->temperature ? $pt->temperature.' °C' : '—'],
                            ['Pulse',      $pt->pulse ? $pt->pulse.' bpm' : '—'],
                            ['RR',         $pt->respiratory_rate ? $pt->respiratory_rate.'/min' : '—'],
                            ['BP',         $pt->bloodPressure()],
                            ['O₂ Sat',     $pt->oxygen_saturation ? $pt->oxygen_saturation.'%' : '—'],
                            ['MUAC',       $pt->muac ? $pt->muac.' cm' : '—'],
                        ] as [$label, $val])
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-2 py-1.5">
                            <div class="text-sm font-bold text-neutral-900 dark:text-white">{{ $val }}</div>
                            <div class="text-[10px] text-neutral-500">{{ $label }}</div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Chief Complaint & Startup Notes --}}
                    @if($pt->chief_complaint_brief || $pt->startup_interventions_notes)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-3">
                        @if($pt->chief_complaint_brief)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Chief Complaint</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $pt->chief_complaint_brief }}</p>
                        </div>
                        @endif
                        @if($pt->startup_interventions_notes)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Startup Interventions</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $pt->startup_interventions_notes }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endif

                {{-- Startup Medications --}}
                @if($past->startupMedications->count())
                <div>
                    <h4 class="text-[11px] font-semibold text-neutral-500 uppercase tracking-wide mb-2">Startup Medications</h4>
                    <div class="overflow-hidden rounded border border-neutral-200 dark:border-neutral-700">
                        <table class="w-full text-sm">
                            <thead class="bg-neutral-50 dark:bg-neutral-800">
                                <tr>
                                    <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Drug</th>
                                    <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Dosage</th>
                                    <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Route</th>
                                    <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Frequency</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                                @foreach($past->startupMedications as $med)
                                <tr>
                                    <td class="px-3 py-1.5 font-medium text-neutral-800 dark:text-neutral-200">{{ $med->medication_name ?? $med->name }}</td>
                                    <td class="px-3 py-1.5 text-neutral-600 dark:text-neutral-400">{{ $med->dosage ?? '—' }}</td>
                                    <td class="px-3 py-1.5 text-neutral-600 dark:text-neutral-400">{{ $med->route ?? '—' }}</td>
                                    <td class="px-3 py-1.5 text-neutral-600 dark:text-neutral-400">{{ $med->frequency ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Screening Data --}}
                @if($past->screeningRecord)
                @php $ps = $past->screeningRecord; @endphp

                {{-- Complaints & Histories --}}
                <div>
                    <h4 class="text-[11px] font-semibold text-neutral-500 uppercase tracking-wide mb-2">Complaints & Histories</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @if($ps->complaints)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Presenting Complaints</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->complaints }}</p>
                        </div>
                        @endif
                        @if($ps->history_of_presenting_illness)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">History of Presenting Illness</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->history_of_presenting_illness }}</p>
                        </div>
                        @endif
                        @if(!empty($ps->tb_symptoms))
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2 md:col-span-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">TB Constitutional Symptoms</p>
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                @php
                                    $tbLabels = [
                                        'lethargy' => 'Lethargy',
                                        'cough' => 'Cough',
                                        'fever' => 'Fever',
                                        'weight_loss' => 'Weight Loss',
                                        'blood_stained_sputum' => 'Blood-stained sputum',
                                        'shortness_of_breath' => 'Shortness of breath',
                                        'chest_pain' => 'Chest Pain',
                                        'night_sweats' => 'Night Sweats',
                                        'fatigue' => 'Fatigue',

                                        // Legacy values already stored in older encounters.
                                        'cough_2weeks' => 'Cough',
                                        'hemoptysis' => 'Blood-stained sputum',
                                        'loss_of_appetite' => 'Loss of Appetite',
                                    ];
                                @endphp
                                @foreach((is_array($ps->tb_symptoms) ? $ps->tb_symptoms : json_decode($ps->tb_symptoms, true) ?? []) as $sym)
                                <span class="inline-block px-2 py-0.5 bg-neutral-200 dark:bg-neutral-700 rounded text-xs font-medium text-neutral-700 dark:text-neutral-300">{{ $tbLabels[$sym] ?? $sym }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @if($ps->review_of_systems)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Review of Systems</p>
                            @php
                                $reviewSystems = is_array($ps->review_of_systems)
                                    ? $ps->review_of_systems
                                    : (json_decode($ps->review_of_systems, true) ?? null);
                            @endphp
                            @if(is_array($reviewSystems) && !empty($reviewSystems))
                            <div class="space-y-1">
                                @foreach($reviewSystems as $entry)
                                    @if(is_array($entry) && !empty($entry['system']) && !empty($entry['notes']))
                                    <p class="text-sm text-neutral-700 dark:text-neutral-300">
                                        <span class="font-semibold">{{ $entry['system'] }}:</span> {{ $entry['notes'] }}
                                    </p>
                                    @endif
                                @endforeach
                            </div>
                            @else
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->review_of_systems }}</p>
                            @endif
                        </div>
                        @endif
                        @if($ps->past_medical_history)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Past Medical History</p>
                            @php
                                $pastMedical = is_array($ps->past_medical_history)
                                    ? $ps->past_medical_history
                                    : (json_decode($ps->past_medical_history, true) ?? null);
                            @endphp
                            @if(is_array($pastMedical) && (!empty($pastMedical['drug_history']) || !empty($pastMedical['admission_history']) || !empty($pastMedical['surgical_history'])))
                            <div class="space-y-1">
                                @if(!empty($pastMedical['drug_history']))
                                <p class="text-sm text-neutral-700 dark:text-neutral-300"><span class="font-semibold">Drug History:</span> {{ $pastMedical['drug_history'] }}</p>
                                @endif
                                @if(!empty($pastMedical['admission_history']))
                                <p class="text-sm text-neutral-700 dark:text-neutral-300"><span class="font-semibold">Admission History:</span> {{ $pastMedical['admission_history'] }}</p>
                                @endif
                                @if(!empty($pastMedical['surgical_history']))
                                <p class="text-sm text-neutral-700 dark:text-neutral-300"><span class="font-semibold">Surgical History:</span> {{ $pastMedical['surgical_history'] }}</p>
                                @endif
                            </div>
                            @else
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->past_medical_history }}</p>
                            @endif
                        </div>
                        @endif
                        @if($ps->chronic_conditions)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Chronic / Non-Chronic Conditions</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->chronic_conditions }}</p>
                        </div>
                        @endif
                        @if($ps->medication_history)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Medication History</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->medication_history }}</p>
                        </div>
                        @endif
                        @if($ps->allergy_history)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Allergies</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->allergy_history }}</p>
                        </div>
                        @endif
                        @if($ps->family_history)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Family History</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->family_history }}</p>
                        </div>
                        @endif
                        @if($ps->social_history)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Social History</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->social_history }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Paediatric History --}}
                @if($ps->birth_weight || $ps->birth_outcome || $ps->birth_notes || $ps->immunization_history || $ps->feeding_code || $ps->feeding_comments || $ps->development_history)
                <div>
                    <h4 class="text-[11px] font-semibold text-neutral-500 uppercase tracking-wide mb-2">Paediatric History</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @if($ps->birth_weight || $ps->birth_outcome)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Birth History</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">
                                @if($ps->birth_weight)Weight: {{ $ps->birth_weight }} kg @endif
                                @if($ps->birth_outcome)· Outcome: {{ $ps->birth_outcome }}@endif
                            </p>
                            @if($ps->birth_notes)<p class="text-xs text-neutral-500 mt-0.5">{{ $ps->birth_notes }}</p>@endif
                        </div>
                        @endif
                        @if($ps->immunization_history)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Immunization History</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->immunization_history }}</p>
                        </div>
                        @endif
                        @if($ps->feeding_code || $ps->feeding_comments)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Feeding History</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->feeding_code }}</p>
                            @if($ps->feeding_comments)<p class="text-xs text-neutral-500 mt-0.5">{{ $ps->feeding_comments }}</p>@endif
                        </div>
                        @endif
                        @if($ps->development_history)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Development History</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->development_history }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Examination & Diagnosis --}}
                <div>
                    <h4 class="text-[11px] font-semibold text-neutral-500 uppercase tracking-wide mb-2">Examination & Diagnosis</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @if($ps->physical_examination)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Physical Examination</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->physical_examination }}</p>
                        </div>
                        @endif
                        @if($ps->clinical_findings)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Clinical Findings</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->clinical_findings }}</p>
                        </div>
                        @endif
                        @if($ps->provisional_diagnosis)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Provisional Diagnosis</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->provisional_diagnosis }}</p>
                        </div>
                        @endif
                        @if($ps->final_diagnosis)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Final Diagnosis</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->final_diagnosis }}</p>
                        </div>
                        @endif
                        @if($ps->assessment_notes)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2 md:col-span-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Assessment Notes</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->assessment_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Plan --}}
                @if($ps->treatment_plan || $ps->plan)
                <div>
                    <h4 class="text-[11px] font-semibold text-neutral-500 uppercase tracking-wide mb-2">Plan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @if($ps->treatment_plan)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Treatment Plan</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->treatment_plan }}</p>
                        </div>
                        @endif
                        @if($ps->plan)
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-3 py-2">
                            <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Management Plan</p>
                            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $ps->plan }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @endif

                @if(!$past->triageRecord && !$past->screeningRecord)
                <p class="text-xs text-neutral-400 italic">No clinical data recorded</p>
                @endif
            </div>
        </div>
        @endforeach
        @endif
    </div>

    {{-- Bottom edge handle --}}
    <button type="button" onclick="toggleReviewPanel()"
            class="w-full flex items-center justify-center py-1.5 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 border-t border-neutral-200 dark:border-neutral-700 transition">
        <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
        </svg>
    </button>
    </div>
    </div>
</div>
@endsection

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('screening.queue') }}" class="hover:text-neutral-700 transition">Screening</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">{{ $encounter->encounter_number }}</span>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-neutral-100 border border-neutral-300 text-neutral-800 rounded text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-neutral-900 border border-neutral-700 text-white rounded text-sm">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-neutral-900 border border-neutral-700 text-white rounded text-sm">
    <ul class="list-disc pl-4 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

@php $s = $encounter->screeningRecord; @endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    {{-- ── Right sidebar: Recent Data Summary ──────────────────────────── --}}
    <div class="lg:col-span-3 lg:order-2 space-y-4">

        {{-- Patient Info --}}
        <div class="bg-white border border-neutral-200 rounded-lg shadow-sm">
            <div class="px-4 py-3 border-b border-neutral-100">
                <h3 class="text-xs font-bold text-neutral-700 uppercase tracking-wide">Patient</h3>
            </div>
            <div class="p-4 text-sm space-y-1">
                <p class="font-semibold text-neutral-900">{{ $encounter->patient->full_name }}</p>
                <p class="text-neutral-500 text-xs">{{ $encounter->patient->patient_id }}</p>
                <p class="text-neutral-500 text-xs">
                    {{ ucfirst($encounter->patient->gender ?? '—') }}
                    @if($encounter->patient->date_of_birth)
                     · {{ $encounter->patient->date_of_birth->format('d M Y') }}
                     · {{ $encounter->patient->date_of_birth->age }}y
                    @endif
                </p>
                @if($encounter->patient->phone_number)
                <p class="text-neutral-500 text-xs">{{ $encounter->patient->phone_number }}</p>
                @endif
                @if($encounter->patient->allergies)
                <div class="mt-2 px-3 py-2 bg-neutral-100 border border-neutral-300 rounded text-xs font-medium text-neutral-800">
                    ⚠ Allergies: {{ $encounter->patient->allergies }}
                </div>
                @endif
            </div>
        </div>

        {{-- Triage Vitals Summary --}}
        @if($encounter->triageRecord)
        @php $t = $encounter->triageRecord; @endphp
        <div class="bg-white border border-neutral-200 rounded-lg shadow-sm">
            <div class="px-4 py-3 border-b border-neutral-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <h3 class="text-xs font-bold text-neutral-700 uppercase tracking-wide">Vitals</h3>
            </div>
            <div class="p-4 space-y-2 text-sm">
                @foreach([
                    ['Weight', $t->weight ? $t->weight.' kg' : '—'],
                    ['Height', $t->height ? $t->height.' cm' : '—'],
                    ['BMI', $t->bmi ?? '—'],
                    ['Temp', $t->temperature ? $t->temperature.'°C' : '—'],
                    ['BP', $t->bloodPressure()],
                    ['Pulse', $t->pulse ? $t->pulse.' bpm' : '—'],
                    ['RR', $t->respiratory_rate ? $t->respiratory_rate.'/min' : '—'],
                    ['O₂ Sat', $t->oxygen_saturation ? $t->oxygen_saturation.'%' : '—'],
                    ['MUAC', $t->muac ? $t->muac.' cm' : '—'],
                ] as [$lbl, $val])
                <div class="flex justify-between">
                    <span class="text-neutral-500 font-medium text-xs">{{ $lbl }}</span>
                    <span class="font-semibold text-neutral-800 text-xs">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Startup Medications --}}
        @if($encounter->startupMedications->count())
        <div class="bg-white border border-neutral-200 rounded-lg shadow-sm">
            <div class="px-4 py-3 border-b border-neutral-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                <h3 class="text-xs font-bold text-neutral-700 uppercase tracking-wide">Startup Medications</h3>
            </div>
            <div class="p-4 space-y-2 text-sm">
                @foreach($encounter->startupMedications as $med)
                <div class="flex justify-between items-start py-1 {{ !$loop->last ? 'border-b border-neutral-100' : '' }}">
                    <div>
                        <p class="font-semibold text-neutral-800 text-xs">{{ $med->medication_name }}</p>
                        <p class="text-[10px] text-neutral-500">{{ $med->route ?? '' }} · {{ $med->frequency ?? '' }}</p>
                    </div>
                    <span class="text-xs font-medium text-neutral-600">{{ $med->dosage ?? '—' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Encounter Info --}}
        <div class="bg-white border border-neutral-200 rounded-lg shadow-sm">
            <div class="px-4 py-3 border-b border-neutral-100">
                <h3 class="text-xs font-bold text-neutral-700 uppercase tracking-wide">Encounter</h3>
            </div>
            <div class="p-4 text-sm space-y-2">
                <div class="detail-row"><span class="detail-label">Number</span><span class="font-mono font-semibold text-neutral-700 text-xs">{{ $encounter->encounter_number }}</span></div>
                <div class="detail-row"><span class="detail-label">Visit Type</span><span class="text-xs">{{ $encounter->visit_type ?? '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Priority</span><span class="text-xs">{{ ucfirst($encounter->priority_level ?? 'Normal') }}</span></div>
                <div class="detail-row"><span class="detail-label">Started</span><span class="text-xs">{{ $encounter->started_at->format('d M Y H:i') }}</span></div>
            </div>
        </div>
    </div>

    {{-- ── Left: Tabbed Clinical Form ──────────────────────────────────── --}}
    <div class="lg:col-span-9 lg:order-1">
        <div class="bg-white border border-neutral-200 rounded-lg shadow-sm">

            {{-- Tab Navigation — dark pill bar --}}
            <div class="tab-nav mx-6 mt-5 mb-2">
                <button class="tab-btn active" data-tab="complaints" type="button">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    Complaints & Histories
                </button>
                <button class="tab-btn" data-tab="paediatric" type="button">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Paediatric History
                </button>
                <button class="tab-btn" data-tab="examination" type="button">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Examination & Diagnosis
                </button>
                <button class="tab-btn" data-tab="prescription" type="button">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Prescription
                </button>
                <button class="tab-btn" data-tab="plan" type="button">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Plan
                </button>
            </div>

            {{-- Single form wrapping all tabs --}}
            <form method="POST" action="{{ route('screening.complete', $encounter) }}" id="screeningForm"
                  x-data="{ labRequested: {{ $s?->lab_requested ? 'true' : 'false' }} }">
                @csrf

                {{-- ═══════════════════════════════════════════════════════════
                     TAB 1: Complaints & Histories
                     ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-panel active p-6 space-y-4" data-tab="complaints">

                    {{-- Presenting Complaints & History --}}
                    <div class="section-card"
                         x-data="complaintsHistoryModal()"
                         x-init="initFromText($el.dataset.initialComplaints, $el.dataset.initialHistory)"
                         data-initial-complaints="{{ old('complaints', $s?->complaints ?? '') }}"
                         data-initial-history="{{ old('history_of_presenting_illness', $s?->history_of_presenting_illness ?? '') }}">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            Complaints & Histories
                        </div>

                        <div class="flex justify-end mb-3">
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">Open Form</button>
                        </div>

                        {{-- Summary display --}}
                        <div class="space-y-2 text-xs text-neutral-600">
                            <template x-if="complaints || history">
                                <div class="space-y-2">
                                    <template x-if="complaints">
                                        <div>
                                            <p class="font-semibold text-neutral-700 mb-0.5">Presenting Complaint</p>
                                            <p x-text="complaints" class="whitespace-pre-line"></p>
                                        </div>
                                    </template>
                                    <template x-if="history">
                                        <div>
                                            <p class="font-semibold text-neutral-700 mb-0.5">History of Presenting Complaint</p>
                                            <p x-text="history" class="whitespace-pre-line"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!complaints && !history">
                                <p class="text-neutral-500">No complaints or history recorded yet.</p>
                            </template>
                        </div>

                        <input type="hidden" name="complaints" :value="complaints">
                        <input type="hidden" name="history_of_presenting_illness" :value="history">

                        {{-- Modal --}}
                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                            <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" @click.stop>
                                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0">
                                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">Complaints & Histories</h3>
                                    <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="overflow-y-auto flex-1 p-6">
                                    <div class="border border-neutral-200 rounded p-4 space-y-4">
                                        <p class="text-xs font-bold text-blue-700 uppercase tracking-wide">Present History</p>
                                        <div>
                                            <label class="field-label">Presenting Complaint <span class="text-red-500">*</span></label>
                                            <textarea x-model="complaints" rows="4" class="field-input" placeholder="Enter Presenting Complaint"></textarea>
                                        </div>
                                        <div>
                                            <label class="field-label">History of Presenting Complaint <span class="text-red-500">*</span></label>
                                            <textarea x-model="history" rows="4" class="field-input" placeholder="Enter History of Presenting Complaint"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 px-6 py-4 border-t border-neutral-200 flex-shrink-0">
                                    <button type="button" @click="showModal = false" class="btn-secondary text-xs px-4 py-2">Close</button>
                                    <button type="button" @click="showModal = false" class="btn-primary text-xs px-4 py-2">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TB Constitutional Symptoms --}}
                    @php
                        $tbChecked = old('tb_symptoms', $s?->tb_symptoms ?? []);
                    @endphp
                    <div class="section-card"
                         x-data="tbSymptomsModal({{ json_encode($tbChecked) }}, @js(old('constitutional_symptoms', $s?->constitutional_symptoms ?? '')), @js(old('presumptive_tb_case_no', $s?->presumptive_tb_case_no ?? '')))">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            TB Constitutional Symptoms
                        </div>

                        <div class="flex justify-end mb-3">
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">Open Form</button>
                        </div>

                        {{-- Summary display --}}
                        <div class="text-xs text-neutral-600 space-y-1">
                            <template x-if="checkedSymptoms.length > 0">
                                <p><span class="font-semibold text-neutral-700">TB Symptoms:</span> <span x-text="checkedSymptoms.join(', ')"></span></p>
                            </template>
                            <template x-if="constitutionalSymptoms">
                                <p><span class="font-semibold text-neutral-700">Constitutional Symptoms:</span> <span x-text="constitutionalSymptoms"></span></p>
                            </template>
                            <template x-if="presumptiveTbCaseNo">
                                <p><span class="font-semibold text-neutral-700">Presumptive TB Case No:</span> <span x-text="presumptiveTbCaseNo"></span></p>
                            </template>
                            <template x-if="checkedSymptoms.length === 0 && !constitutionalSymptoms && !presumptiveTbCaseNo">
                                <p class="text-neutral-500">No TB symptoms recorded yet.</p>
                            </template>
                        </div>

                        {{-- Hidden inputs --}}
                        <template x-for="sym in checkedSymptoms" :key="sym">
                            <input type="hidden" name="tb_symptoms[]" :value="sym">
                        </template>
                        <input type="hidden" name="constitutional_symptoms" :value="constitutionalSymptoms">
                        <input type="hidden" name="presumptive_tb_case_no" :value="presumptiveTbCaseNo">

                        {{-- Modal --}}
                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                            <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" @click.stop>
                                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0">
                                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">TB Constitutional Symptoms</h3>
                                    <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="overflow-y-auto flex-1 p-6 space-y-4">
                                    {{-- TB Symptoms --}}
                                    <div class="border border-neutral-200 rounded p-4">
                                        <p class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-3">TB Symptoms</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <template x-for="option in tbOptions" :key="option.key">
                                                <label class="flex items-center gap-2 cursor-pointer text-sm text-neutral-700 border border-neutral-100 rounded px-3 py-2 hover:bg-neutral-50">
                                                    <input type="checkbox"
                                                           :value="option.key"
                                                           :checked="symptoms[option.key]"
                                                           @change="symptoms[option.key] = $event.target.checked"
                                                           class="w-4 h-4 rounded border-neutral-300 text-neutral-700 focus:ring-neutral-500">
                                                    <span x-text="option.label"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Constitutional Symptoms --}}
                                    <div class="border border-neutral-200 rounded p-4">
                                        <p class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-3">Constitutional Symptoms</p>
                                        <select x-model="constitutionalSymptoms" class="field-input">
                                            <option value="">Constitutional Symptoms</option>
                                            <option>Fever</option>
                                            <option>Night Sweats</option>
                                            <option>Weight Loss</option>
                                            <option>Fatigue / Lethargy</option>
                                            <option>Loss of Appetite</option>
                                            <option>Generalised Weakness</option>
                                        </select>
                                    </div>

                                    {{-- Presumptive TB Case No --}}
                                    <div class="border border-neutral-200 rounded p-4">
                                        <label class="field-label mb-1">Presumptive TB Case No</label>
                                        <input type="text" x-model="presumptiveTbCaseNo" class="field-input bg-neutral-50" placeholder="">
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 px-6 py-4 border-t border-neutral-200 flex-shrink-0">
                                    <button type="button" @click="showModal = false" class="btn-secondary text-xs px-4 py-2">Close</button>
                                    <button type="button" @click="showModal = false" class="btn-primary text-xs px-4 py-2">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Review of Systems --}}
                    <div class="section-card"
                         x-data="systemExaminationModal()"
                         x-init="initFromText($el.dataset.initialReviewSystems)"
                         data-initial-review-systems="{{ old('review_of_systems', $s?->review_of_systems ?? '') }}">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            Review of Systems
                        </div>

                        <div class="flex justify-end gap-2 mb-3">
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">Open Form</button>
                            <button type="button" x-show="entries.length > 0" @click="showModal = true" class="btn-secondary text-xs px-3 py-1.5" style="display:none;">Edit Record</button>
                        </div>

                        <template x-if="entries.length > 0">
                            <div class="border border-neutral-200 rounded overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-neutral-100">
                                        <tr>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">System</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-100">
                                        <template x-for="(entry, idx) in entries" :key="idx">
                                            <tr>
                                                <td class="px-3 py-1.5 text-neutral-800 text-xs font-medium" x-text="entry.system"></td>
                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.notes"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template x-if="entries.length === 0 && !initialRaw">
                            <p class="text-xs text-neutral-500">No review of systems record captured yet.</p>
                        </template>

                        <input type="hidden" name="review_of_systems" :value="serialized">

                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                            <div class="bg-white rounded-lg shadow-2xl w-full max-w-5xl" @click.stop>
                                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
                                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">Review of Systems</h3>
                                    <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="p-6 border border-neutral-200 rounded mx-6 mt-4">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="field-label">System <span class="text-red-500">*</span></label>
                                            <select x-model="form.system" class="field-input">
                                                <option value="">--Select--</option>
                                                <option value="Other">Other</option>
                                                <option value="Endocrine system">Endocrine system</option>
                                                <option value="Systemic Symptoms">Systemic Symptoms</option>
                                                <option value="Ear, Nose, and Throat">Ear, Nose, and Throat</option>
                                                <option value="Respiratory System">Respiratory System</option>
                                                <option value="Gastro-Intestinal System">Gastro-Intestinal System</option>
                                                <option value="Cardiovascular System">Cardiovascular System</option>
                                                <option value="Genito-Urinary System">Genito-Urinary System</option>
                                                <option value="Musculoskeletal System">Musculoskeletal System</option>
                                                <option value="Integumentary system">Integumentary system</option>
                                                <option value="Nervous System">Nervous System</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Notes <span class="text-red-500">*</span></label>
                                            <textarea x-model="form.notes" rows="2" class="field-input" placeholder="Enter Notes"></textarea>
                                        </div>
                                        <button type="button" @click="addEntry()" class="btn-primary text-xs px-3 py-1.5">Add</button>
                                        <p x-show="error" class="text-xs text-red-600" x-text="error"></p>
                                    </div>

                                    <template x-if="entries.length > 0">
                                        <div class="mt-4 border border-neutral-200 rounded overflow-hidden">
                                            <table class="w-full text-sm">
                                                <thead class="bg-neutral-100">
                                                    <tr>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">System</th>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Notes</th>
                                                        <th class="px-2 py-1.5 w-8"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-neutral-100">
                                                    <template x-for="(entry, idx) in entries" :key="idx">
                                                        <tr>
                                                            <td class="px-3 py-1.5 text-neutral-800 text-xs font-medium" x-text="entry.system"></td>
                                                            <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.notes"></td>
                                                            <td class="px-2 py-1.5 text-center">
                                                                <button type="button" @click="removeEntry(idx)" class="text-neutral-400 hover:text-neutral-700 transition">×</button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex items-center justify-center gap-3 px-6 py-4 mt-3 border-t border-neutral-200">
                                    <button type="button" @click="showModal = false" class="btn-secondary">Close</button>
                                    <button type="button" @click="showModal = false" class="btn-primary">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Past Medical History --}}
                    <div class="section-card"
                         x-data="pastMedicalHistoryModal()"
                         x-init="initFromText($el.dataset.initialPastMedicalHistory)"
                         data-initial-past-medical-history="{{ old('past_medical_history', $s?->past_medical_history ?? '') }}">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Past Medical History
                        </div>

                        <div class="flex justify-end gap-2 mb-3">
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">Open Form</button>
                            <button type="button" x-show="hasData" @click="showModal = true" class="btn-secondary text-xs px-3 py-1.5" style="display:none;">Edit Record</button>
                        </div>

                        <template x-if="hasData">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <div class="bg-neutral-50 rounded px-3 py-2 border border-neutral-200">
                                    <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Drug History</p>
                                    <p class="text-sm text-neutral-800" x-text="form.drug_history || '—'"></p>
                                </div>
                                <div class="bg-neutral-50 rounded px-3 py-2 border border-neutral-200">
                                    <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Admission History</p>
                                    <p class="text-sm text-neutral-800" x-text="form.admission_history || '—'"></p>
                                </div>
                                <div class="bg-neutral-50 rounded px-3 py-2 border border-neutral-200">
                                    <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Surgical History</p>
                                    <p class="text-sm text-neutral-800" x-text="form.surgical_history || '—'"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="!hasData && !initialRaw">
                            <p class="text-xs text-neutral-500">No past medical history record captured yet.</p>
                        </template>

                        <input type="hidden" name="past_medical_history" :value="serialized">

                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                            <div class="bg-white rounded-lg shadow-2xl w-full max-w-5xl" @click.stop>
                                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
                                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">Past Medical History</h3>
                                    <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="p-6 border border-neutral-200 rounded mx-6 mt-4">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="field-label">Drug History <span class="text-red-500">*</span></label>
                                            <textarea x-model="form.drug_history" rows="2" class="field-input" placeholder="Enter Drug History"></textarea>
                                        </div>
                                        <div>
                                            <label class="field-label">Admission History <span class="text-red-500">*</span></label>
                                            <textarea x-model="form.admission_history" rows="2" class="field-input" placeholder="Enter Admission History"></textarea>
                                        </div>
                                        <div>
                                            <label class="field-label">Surgical History <span class="text-red-500">*</span></label>
                                            <textarea x-model="form.surgical_history" rows="2" class="field-input" placeholder="Enter Surgical History"></textarea>
                                        </div>
                                        <p x-show="error" class="text-xs text-red-600" x-text="error"></p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-center gap-3 px-6 py-4 mt-3 border-t border-neutral-200">
                                    <button type="button" @click="showModal = false" class="btn-secondary">Close</button>
                                    <button type="button" @click="saveRecord()" class="btn-primary">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Chronic / Non-Chronic Conditions --}}
                    <div class="section-card"
                         x-data="chronicConditionsModal(@js($icd11Library ?? []))"
                         x-init="initFromText($el.dataset.initialChronicConditions)"
                         data-initial-chronic-conditions="{{ old('chronic_conditions', $s?->chronic_conditions ?? '') }}">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            Chronic / Non-Chronic Conditions
                        </div>

                        <div class="flex justify-end gap-2 mb-3">
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">Open Form</button>
                            <button type="button" x-show="entries.length > 0" @click="showModal = true" class="btn-secondary text-xs px-3 py-1.5" style="display:none;">Edit Record</button>
                        </div>

                        <template x-if="entries.length > 0">
                            <div class="border border-neutral-200 rounded overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-neutral-100">
                                        <tr>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Condition</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Classification</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Date Diagnosed</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Status</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Certainty</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-100">
                                        <template x-for="(entry, idx) in entries" :key="idx">
                                            <tr>
                                                <td class="px-3 py-1.5 text-neutral-800 text-xs font-medium" x-text="entry.condition"></td>
                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.path"></td>
                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.date_diagnosed"></td>
                                                <td class="px-3 py-1.5 text-xs">
                                                    <span x-show="entry.still_ongoing" class="inline-block px-1.5 py-0.5 bg-neutral-200 text-neutral-700 rounded text-[10px] font-semibold">Ongoing</span>
                                                    <span x-show="!entry.still_ongoing" x-text="entry.date_resolved || '—'" class="text-neutral-700"></span>
                                                </td>
                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.certainty"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template x-if="entries.length === 0 && !initialRaw">
                            <p class="text-xs text-neutral-500">No chronic / non-chronic conditions recorded yet.</p>
                        </template>

                        <template x-if="entries.length === 0 && initialRaw">
                            <p class="text-xs text-neutral-600 whitespace-pre-line" x-text="initialRaw"></p>
                        </template>

                        <input type="hidden" name="chronic_conditions" :value="serialized">

                        {{-- Modal --}}
                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                            <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" @click.stop>
                                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0">
                                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">Chronic / Non-Chronic Conditions</h3>
                                    <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="overflow-y-auto flex-1 p-6">
                                    <div class="border border-neutral-200 rounded p-4">
                                        {{-- Type tabs --}}
                                        <div class="grid grid-cols-2 gap-3 mb-4">
                                            <button type="button" @click="form.type='National Treatment Guideline'" :class="form.type==='National Treatment Guideline' ? 'btn-primary' : 'btn-secondary'" class="text-xs py-2">National Treatment Guideline</button>
                                            <button type="button" @click="form.type='ICD 11'" :class="form.type==='ICD 11' ? 'btn-primary' : 'btn-secondary'" class="text-xs py-2">ICD 11</button>
                                        </div>

                                        <div class="space-y-3">
                                            {{-- NTG levels --}}
                                            <template x-if="form.type === 'National Treatment Guideline'">
                                                <div class="space-y-3">
                                                    <div>
                                                        <label class="field-label">NTG Level 1 <span class="text-red-500">*</span></label>
                                                        <select x-model="form.level1" @change="onLevel1Change()" class="field-input">
                                                            <option value="">Search</option>
                                                            <option value="Anaemia And Nutritional Conditions">Anaemia And Nutritional Conditions</option>
                                                            <option value="Cardiovascular Disorders">Cardiovascular Disorders</option>
                                                            <option value="Conditions Of The Ear, Nose And Oropharynx">Conditions Of The Ear, Nose And Oropharynx</option>
                                                            <option value="Dermatological Conditions">Dermatological Conditions</option>
                                                            <option value="Disorders Of The Renal System">Disorders Of The Renal System</option>
                                                            <option value="Urology disorders">Urology disorders</option>
                                                            <option value="Eye Disease">Eye Disease</option>
                                                            <option value="Gastro-intestinal conditions">Gastro-intestinal conditions</option>
                                                            <option value="Infections">Infections</option>
                                                            <option value="Malignancies">Malignancies</option>
                                                            <option value="Orthopedic conditions">Orthopedic conditions</option>
                                                            <option value="Obstetric &amp; Gynaecological conditions">Obstetric &amp; Gynaecological conditions</option>
                                                            <option value="Poisoning">Poisoning</option>
                                                            <option value="Surgical conditions">Surgical conditions</option>
                                                            <option value="Haematological conditions">Haematological conditions</option>
                                                            <option value="Dental conditions">Dental conditions</option>
                                                            <option value="Mental health and psychiatric disorders">Mental health and psychiatric disorders</option>
                                                            <option value="Endocrine disorders">Endocrine disorders</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="field-label">NTG Level 2 <span class="text-red-500">*</span></label>
                                                        <select x-model="form.level2" @change="onLevel2Change()" class="field-input">
                                                            <option value="">Search</option>
                                                            <template x-for="option in level2Options" :key="option">
                                                                <option :value="option" x-text="option"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="field-label">NTG Level 3 <span class="text-red-500">*</span></label>
                                                        <select x-model="form.level3" class="field-input">
                                                            <option value="">Search</option>
                                                            <template x-for="option in level3Options" :key="option">
                                                                <option :value="option" x-text="option"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- ICD 11 search --}}
                                            <template x-if="form.type === 'ICD 11'">
                                                <div>
                                                    <label class="field-label">ICD 11 <span class="text-red-500">*</span></label>
                                                    <div class="relative">
                                                        <input type="text"
                                                               x-model="form.icd11"
                                                               @focus="showIcd11Dropdown = form.icd11.trim().length > 0"
                                                               @input="showIcd11Dropdown = form.icd11.trim().length > 0"
                                                               @keydown.escape="showIcd11Dropdown = false"
                                                               class="field-input"
                                                               placeholder="Type to search ICD 11">
                                                        <div x-show="showIcd11Dropdown"
                                                             x-transition
                                                             @click.outside="showIcd11Dropdown = false"
                                                             class="absolute z-20 mt-1 w-full bg-white border border-neutral-300 rounded shadow max-h-48 overflow-y-auto"
                                                             style="display:none;">
                                                            <template x-for="item in filteredIcd11" :key="item">
                                                                <button type="button" @click="selectIcd11(item)"
                                                                        class="block w-full text-left px-3 py-2 text-sm hover:bg-neutral-100 text-neutral-800"
                                                                        x-text="item"></button>
                                                            </template>
                                                            <p x-show="filteredIcd11.length === 0" class="px-3 py-2 text-xs text-neutral-500">No match found</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- Condition + Date Diagnosed + Still Ongoing --}}
                                            <div>
                                                <label class="field-label">Condition <span class="text-red-500">*</span></label>
                                                <select x-model="form.condition" class="field-input">
                                                    <option value="">--Select--</option>
                                                    <option>Chronic Condition</option>
                                                    <option>Non Chronic Condition</option>
                                                </select>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3 items-end">
                                                <div>
                                                    <label class="field-label">Date Diagnosed <span class="text-red-500">*</span></label>
                                                    <input type="date" x-model="form.date_diagnosed" class="field-input">
                                                </div>
                                                <div class="flex items-center gap-2 pb-2">
                                                    <input type="checkbox" id="cc-still-ongoing" x-model="form.still_ongoing" class="w-4 h-4 accent-neutral-900">
                                                    <label for="cc-still-ongoing" class="text-sm text-neutral-700 cursor-pointer">Still Ongoing</label>
                                                </div>
                                            </div>
                                            <template x-if="!form.still_ongoing">
                                                <div>
                                                    <label class="field-label">Date Resolved</label>
                                                    <input type="date" x-model="form.date_resolved" class="field-input">
                                                </div>
                                            </template>
                                            <div>
                                                <label class="field-label">Certainty <span class="text-red-500">*</span></label>
                                                <select x-model="form.certainty" class="field-input">
                                                    <option value="">--Select--</option>
                                                    <option>Confirmed</option>
                                                    <option>Probable</option>
                                                    <option>Possible</option>
                                                    <option>Rule Out</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="field-label">Comments</label>
                                                <textarea x-model="form.comments" rows="2" class="field-input" placeholder="Enter Comments"></textarea>
                                            </div>

                                            <button type="button" @click="addEntry()" class="btn-primary text-xs px-3 py-1.5">&#10010; Add</button>
                                            <p x-show="error" class="text-xs text-red-600" x-text="error" style="display:none;"></p>
                                        </div>

                                        <template x-if="entries.length > 0">
                                            <div class="mt-4 border border-neutral-200 rounded overflow-hidden">
                                                <table class="w-full text-sm">
                                                    <thead class="bg-neutral-100">
                                                        <tr>
                                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Condition</th>
                                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Classification</th>
                                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Date Diagnosed</th>
                                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Status</th>
                                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Certainty</th>
                                                            <th class="px-2 py-1.5 w-8"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-neutral-100">
                                                        <template x-for="(entry, idx) in entries" :key="idx">
                                                            <tr>
                                                                <td class="px-3 py-1.5 text-neutral-800 text-xs font-medium" x-text="entry.condition"></td>
                                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.path"></td>
                                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.date_diagnosed"></td>
                                                                <td class="px-3 py-1.5 text-xs">
                                                                    <span x-show="entry.still_ongoing" class="inline-block px-1.5 py-0.5 bg-neutral-200 text-neutral-700 rounded text-[10px] font-semibold">Ongoing</span>
                                                                    <span x-show="!entry.still_ongoing" x-text="entry.date_resolved || '—'" class="text-neutral-700"></span>
                                                                </td>
                                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.certainty"></td>
                                                                <td class="px-2 py-1.5 text-center">
                                                                    <button type="button" @click="removeEntry(idx)" class="text-neutral-400 hover:text-neutral-700 transition text-base leading-none">×</button>
                                                                </td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex items-center justify-center gap-3 px-6 py-4 border-t border-neutral-200 flex-shrink-0">
                                    <button type="button" @click="showModal = false" class="btn-secondary">Close</button>
                                    <button type="button" @click="showModal = false" class="btn-primary">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Medication History --}}
                    <div class="section-card">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            Medication History
                        </div>
                        <textarea name="medication_history" rows="2" class="field-input" placeholder="Current medications, dosages, duration...">{{ old('medication_history', $s?->medication_history) }}</textarea>
                    </div>

                    {{-- Allergies --}}
                    <div class="section-card"
                         x-data="allergiesModal()"
                         x-init="initFromText($el.dataset.initialAllergies)"
                         data-initial-allergies="{{ old('allergy_history', $s?->allergy_history ?? '') }}">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            Allergies
                        </div>

                        <div class="flex justify-end gap-2 mb-3">
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">Open Form</button>
                        </div>

                        <template x-if="entries.length > 0">
                            <div class="border border-neutral-200 rounded overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-neutral-100">
                                        <tr>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Allergy Type</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Severity</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Drug Type</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-100">
                                        <template x-for="(entry, idx) in entries" :key="idx">
                                            <tr>
                                                <td class="px-3 py-1.5 text-neutral-800 text-xs font-medium" x-text="entry.allergy_type"></td>
                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.severity"></td>
                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.drug_type || '—'"></td>
                                                <td class="px-3 py-1.5 text-right">
                                                    <button type="button" @click="removeEntry(idx)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template x-if="entries.length === 0 && !initialRaw">
                            <p class="text-xs text-neutral-500">No allergies recorded yet.</p>
                        </template>

                        <template x-if="entries.length === 0 && initialRaw">
                            <p class="text-xs text-neutral-600 whitespace-pre-line" x-text="initialRaw"></p>
                        </template>

                        <input type="hidden" name="allergy_history" :value="serialized">

                        {{-- Modal --}}
                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                            <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" @click.stop>
                                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0">
                                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">Allergies</h3>
                                    <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="overflow-y-auto flex-1 p-6 space-y-4">
                                    <div class="border border-neutral-200 rounded p-4 space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="field-label">Allergy Type <span class="text-red-500">*</span></label>
                                                <select x-model="form.allergy_type" @change="form.drug_type = ''" class="field-input">
                                                    <option value="">--Select--</option>
                                                    <option>Drug</option>
                                                    <option>Blood</option>
                                                    <option>Dust</option>
                                                    <option>Food</option>
                                                    <option>Plants</option>
                                                    <option>Other</option>
                                                    <option>Animals</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="field-label">Severity <span class="text-red-500">*</span></label>
                                                <select x-model="form.severity" class="field-input">
                                                    <option value="">--Select--</option>
                                                    <option>Mild</option>
                                                    <option>Moderate</option>
                                                    <option>Severe</option>
                                                    <option>Life-threatening</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="field-label">Drug Type</label>
                                            <select x-model="form.drug_type" :disabled="form.allergy_type !== 'Drug'" :class="form.allergy_type !== 'Drug' ? 'opacity-50 cursor-not-allowed bg-neutral-100' : ''" class="field-input">
                                                <option value="">--Select--</option>
                                                <option>Penicillin</option>
                                                <option>Amoxicillin</option>
                                                <option>Ampicillin</option>
                                                <option>Cephalosporins</option>
                                                <option>Sulphonamides</option>
                                                <option>Tetracyclines</option>
                                                <option>Macrolides (Erythromycin)</option>
                                                <option>Quinolones (Ciprofloxacin)</option>
                                                <option>Metronidazole</option>
                                                <option>Aspirin</option>
                                                <option>NSAIDs (Ibuprofen / Diclofenac)</option>
                                                <option>Paracetamol</option>
                                                <option>Codeine</option>
                                                <option>Morphine</option>
                                                <option>Tramadol</option>
                                                <option>ACE Inhibitors</option>
                                                <option>Beta-blockers</option>
                                                <option>Calcium Channel Blockers</option>
                                                <option>Statins</option>
                                                <option>Insulin</option>
                                                <option>Metformin</option>
                                                <option>Antiretrovirals (ARVs)</option>
                                                <option>Anti-TB drugs</option>
                                                <option>Contrast dye (X-ray / CT)</option>
                                                <option>Anaesthetic agents</option>
                                                <option>Other</option>
                                            </select>
                                        </div>

                                        <div>
                                            <button type="button" @click="addEntry()" class="btn-primary text-xs px-3 py-1.5">
                                                <svg class="w-3.5 h-3.5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Add
                                            </button>
                                            <p x-show="error" class="text-xs text-red-600 mt-1" x-text="error"></p>
                                        </div>
                                    </div>

                                    <template x-if="entries.length > 0">
                                        <div class="border border-neutral-200 rounded overflow-hidden">
                                            <table class="w-full text-sm">
                                                <thead class="bg-neutral-100">
                                                    <tr>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Allergy Type</th>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Severity</th>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Drug Type</th>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-neutral-100">
                                                    <template x-for="(entry, idx) in entries" :key="idx">
                                                        <tr>
                                                            <td class="px-3 py-1.5 text-neutral-800 text-xs font-medium" x-text="entry.allergy_type"></td>
                                                            <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.severity"></td>
                                                            <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.drug_type || '—'"></td>
                                                            <td class="px-3 py-1.5 text-right">
                                                                <button type="button" @click="removeEntry(idx)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex justify-end gap-3 px-6 py-4 border-t border-neutral-200 flex-shrink-0">
                                    <button type="button" @click="showModal = false" class="btn-secondary text-xs px-4 py-2">Close</button>
                                    <button type="button" @click="showModal = false" class="btn-primary text-xs px-4 py-2">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Family & Social History --}}
                    <div class="section-card"
                         x-data="familySocialHistoryModal()"
                         x-init="initFromText($el.dataset.initialFamily, $el.dataset.initialSocial)"
                         data-initial-family="{{ old('family_history', $s?->family_history ?? '') }}"
                         data-initial-social="{{ old('social_history', $s?->social_history ?? '') }}">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Family & Social History
                        </div>

                        <div class="flex justify-end mb-3">
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">Open Form</button>
                        </div>

                        {{-- Summary display --}}
                        <div class="space-y-2 text-xs text-neutral-600">
                            <template x-if="familyHistory || ncdRiskFactors !== null">
                                <div>
                                    <p class="font-semibold text-neutral-700 mb-0.5">Family Medical History</p>
                                    <p x-show="familyHistory" x-text="familyHistory" class="whitespace-pre-line"></p>
                                    <p x-show="ncdRiskFactors !== null">
                                        NCD Risk Factors: <span class="font-medium" x-text="ncdRiskFactors ? 'Yes' : 'No'"></span>
                                    </p>
                                </div>
                            </template>
                            <template x-if="smokes !== null || drinksAlcohol !== null">
                                <div>
                                    <p class="font-semibold text-neutral-700 mb-0.5">Smoking & Alcohol</p>
                                    <p x-show="smokes !== null">Smokes: <span class="font-medium" x-text="smokes ? 'Yes' : 'No'"></span></p>
                                    <p x-show="drinksAlcohol !== null">Drinks Alcohol: <span class="font-medium" x-text="drinksAlcohol ? 'Yes' : 'No'"></span></p>
                                </div>
                            </template>
                            <template x-if="familyHistory === '' && ncdRiskFactors === null && smokes === null && drinksAlcohol === null && !initialFamilyRaw && !initialSocialRaw">
                                <p class="text-neutral-500">No family or social history recorded yet.</p>
                            </template>
                            <template x-if="(familyHistory === '' && ncdRiskFactors === null && smokes === null && drinksAlcohol === null) && (initialFamilyRaw || initialSocialRaw)">
                                <div>
                                    <p x-show="initialFamilyRaw" class="whitespace-pre-line" x-text="initialFamilyRaw"></p>
                                    <p x-show="initialSocialRaw" class="whitespace-pre-line" x-text="initialSocialRaw"></p>
                                </div>
                            </template>
                        </div>

                        <input type="hidden" name="family_history" :value="serializedFamily">
                        <input type="hidden" name="social_history" :value="serializedSocial">

                        {{-- Modal --}}
                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                            <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" @click.stop>
                                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0">
                                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">Family & Social History</h3>
                                    <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="overflow-y-auto flex-1 p-6">
                                    {{-- Tabs --}}
                                    <div class="grid grid-cols-2 gap-3 mb-4">
                                        <button type="button" @click="tab = 'family'" :class="tab === 'family' ? 'btn-primary' : 'btn-secondary'" class="text-xs py-2">Family Medical History</button>
                                        <button type="button" @click="tab = 'social'" :class="tab === 'social' ? 'btn-primary' : 'btn-secondary'" class="text-xs py-2">Smoking & Alcohol History</button>
                                    </div>

                                    {{-- Family Medical History tab --}}
                                    <template x-if="tab === 'family'">
                                        <div class="border border-neutral-200 rounded p-4 space-y-4">
                                            <div>
                                                <label class="field-label">Family Medical History <span class="text-red-500">*</span></label>
                                                <textarea x-model="familyHistory" rows="4" class="field-input" placeholder="Enter Family Medical History"></textarea>
                                            </div>
                                            <div>
                                                <p class="field-label mb-2">Does the patient have any NCD risk factors?</p>
                                                <div class="flex items-center gap-6">
                                                    <label class="flex items-center gap-2 cursor-pointer text-sm text-neutral-700">
                                                        <input type="radio" :checked="ncdRiskFactors === true" @change="ncdRiskFactors = true" class="w-4 h-4 accent-blue-600"> Yes
                                                    </label>
                                                    <label class="flex items-center gap-2 cursor-pointer text-sm text-neutral-700">
                                                        <input type="radio" :checked="ncdRiskFactors === false" @change="ncdRiskFactors = false" class="w-4 h-4 accent-blue-600"> No
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Smoking & Alcohol tab --}}
                                    <template x-if="tab === 'social'">
                                        <div class="border border-neutral-200 rounded p-4 space-y-4">
                                            <div class="border border-neutral-100 rounded p-3">
                                                <p class="field-label mb-2">Does the patient smoke?</p>
                                                <div class="flex items-center gap-6">
                                                    <label class="flex items-center gap-2 cursor-pointer text-sm text-neutral-700">
                                                        <input type="radio" :checked="smokes === true" @change="smokes = true" class="w-4 h-4 accent-blue-600"> Yes
                                                    </label>
                                                    <label class="flex items-center gap-2 cursor-pointer text-sm text-neutral-700">
                                                        <input type="radio" :checked="smokes === false" @change="smokes = false" class="w-4 h-4 accent-blue-600"> No
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="border border-neutral-100 rounded p-3">
                                                <p class="field-label mb-2">Does the patient drink alcohol?</p>
                                                <div class="flex items-center gap-6">
                                                    <label class="flex items-center gap-2 cursor-pointer text-sm text-neutral-700">
                                                        <input type="radio" :checked="drinksAlcohol === true" @change="drinksAlcohol = true" class="w-4 h-4 accent-blue-600"> Yes
                                                    </label>
                                                    <label class="flex items-center gap-2 cursor-pointer text-sm text-neutral-700">
                                                        <input type="radio" :checked="drinksAlcohol === false" @change="drinksAlcohol = false" class="w-4 h-4 accent-blue-600"> No
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex justify-end gap-3 px-6 py-4 border-t border-neutral-200 flex-shrink-0">
                                    <button type="button" @click="showModal = false" class="btn-secondary text-xs px-4 py-2">Close</button>
                                    <button type="button" @click="showModal = false" class="btn-primary text-xs px-4 py-2">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════════════════
                     TAB 2: Paediatric History
                     ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-panel p-6 space-y-4" data-tab="paediatric">

                    {{-- ── Paediatric section rows ── --}}
                    <div class="section-card divide-y divide-neutral-100 !p-0 overflow-hidden">

                        {{-- Birth History --}}
                        <div class="flex items-center justify-between px-5 py-4"
                             x-data="birthHistoryModal(
                                 @js(old('birth_weight',          $s?->birth_weight          ?? '')),
                                 @js(old('birth_length',          $s?->birth_length          ?? '')),
                                 @js(old('birth_outcome',         $s?->birth_outcome         ?? '')),
                                 @js(old('head_circumference',    $s?->head_circumference    ?? '')),
                                 @js(old('chest_circumference',   $s?->chest_circumference   ?? '')),
                                 @js(old('general_condition',     $s?->general_condition     ?? '')),
                                 @js(old('is_breast_feeding_well',$s?->is_breast_feeding_well ?? false)),
                                 @js(old('other_feeding_option',  $s?->other_feeding_option  ?? '')),
                                 @js(old('delivery_time',         $s?->delivery_time         ?? '')),
                                 @js(old('vaccination_outside',   $s?->vaccination_outside   ?? '')),
                                 @js(old('tetanus_at_birth',      $s?->tetanus_at_birth      ?? '')),
                                 @js(old('birth_notes',           $s?->birth_notes           ?? ''))
                             )">
                            <span class="text-sm font-medium text-neutral-700">Birth History</span>
                            <div class="flex gap-2">
                                <button type="button" x-show="hasData" @click="showModal = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-300 rounded hover:bg-neutral-200 transition" style="display:none;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit Record
                                </button>
                                <button type="button" @click="showModal = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Record
                                </button>
                            </div>

                            <input type="hidden" name="birth_weight"          :value="birthWeight">
                            <input type="hidden" name="birth_length"          :value="birthLength">
                            <input type="hidden" name="birth_outcome"         :value="birthOutcome">
                            <input type="hidden" name="head_circumference"    :value="headCircumference">
                            <input type="hidden" name="chest_circumference"   :value="chestCircumference">
                            <input type="hidden" name="general_condition"     :value="generalCondition">
                            <input type="hidden" name="is_breast_feeding_well" :value="isBreastFeedingWell ? '1' : '0'">
                            <input type="hidden" name="other_feeding_option"  :value="otherFeedingOption">
                            <input type="hidden" name="delivery_time"         :value="deliveryTime">
                            <input type="hidden" name="vaccination_outside"   :value="vaccinationOutside">
                            <input type="hidden" name="tetanus_at_birth"      :value="tetanusAtBirth">
                            <input type="hidden" name="birth_notes"           :value="birthNotes">

                            <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                                <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" @click.stop>
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0">
                                        <h3 class="text-base font-bold text-neutral-900">Birth History</h3>
                                        <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="overflow-y-auto flex-1 p-6 space-y-4">
                                        {{-- Row 1: Weight / Length / Outcome --}}
                                        <div class="grid grid-cols-3 gap-4">
                                            <div>
                                                <label class="field-label">Birth Weight (kg) <span class="text-red-500">*</span></label>
                                                <input type="number" x-model="birthWeight" step="0.01" min="0.1" max="15" class="field-input" placeholder="Enter Birth Weight (kg)">
                                            </div>
                                            <div>
                                                <label class="field-label">Birth Length (cm)</label>
                                                <input type="number" x-model="birthLength" step="0.1" min="1" max="100" class="field-input" placeholder="Enter Birth Length (cm)">
                                            </div>
                                            <div>
                                                <label class="field-label">Birth Outcome <span class="text-red-500">*</span></label>
                                                <select x-model="birthOutcome" class="field-input">
                                                    <option value="">--Select--</option>
                                                    <option>Live Birth</option>
                                                    <option>Stillbirth</option>
                                                    <option>Preterm</option>
                                                    <option>Low Birth Weight</option>
                                                    <option>Neonatal Death</option>
                                                </select>
                                            </div>
                                        </div>
                                        {{-- Row 2: Head / Chest circumference --}}
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="field-label">Head Circumference</label>
                                                <input type="number" x-model="headCircumference" step="0.1" min="1" max="100" class="field-input" placeholder="Enter Head Circumference">
                                            </div>
                                            <div>
                                                <label class="field-label">Chest Circumference</label>
                                                <input type="number" x-model="chestCircumference" step="0.1" min="1" max="100" class="field-input" placeholder="Enter Chest Circumference">
                                            </div>
                                        </div>
                                        {{-- Row 3: General Condition / Is Breast Feeding Well --}}
                                        <div class="grid grid-cols-2 gap-4 items-end">
                                            <div>
                                                <label class="field-label">General Condition <span class="text-red-500">*</span></label>
                                                <select x-model="generalCondition" class="field-input">
                                                    <option value="">--Select--</option>
                                                    <option>Good</option>
                                                    <option>Fair</option>
                                                    <option>Poor</option>
                                                    <option>Critical</option>
                                                </select>
                                            </div>
                                            <div class="pb-2">
                                                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                                    <input type="checkbox" x-model="isBreastFeedingWell" class="w-4 h-4 rounded border-neutral-300 text-blue-600">
                                                    <span class="text-sm text-neutral-700">Is Breast Feeding Well</span>
                                                </label>
                                            </div>
                                        </div>
                                        {{-- Row 4: Other Feeding Option / Delivery Time --}}
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="field-label">Other Feeding Option <span class="text-red-500">*</span></label>
                                                <select x-model="otherFeedingOption" class="field-input">
                                                    <option value="">--Select--</option>
                                                    <option>Exclusive breast feeding</option>
                                                    <option>Exclusive alternative infant formula</option>
                                                    <option>Supplementary food(No Breastfeeding)</option>
                                                    <option>Mixed Feeding food(breast milk and other foods)</option>
                                                    <option>Complimentary Feeding and continue breastfeeding upto 2 year or more</option>
                                                    <option>Mixed based feed after 6 months in addition to other foods</option>
                                                    <option>Other</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="field-label">Delivery Time <span class="text-red-500">*</span></label>
                                                <input type="time" x-model="deliveryTime" class="field-input" placeholder="Select Time">
                                            </div>
                                        </div>
                                        {{-- Row 5: Vaccination Outside / Tetanus At Birth --}}
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="field-label">Vaccination Outside</label>
                                                <input type="text" x-model="vaccinationOutside" class="field-input" placeholder="Enter Vaccination Outside">
                                            </div>
                                            <div>
                                                <label class="field-label">Tetanus At Birth <span class="text-red-500">*</span></label>
                                                <select x-model="tetanusAtBirth" class="field-input">
                                                    <option value="">--Select--</option>
                                                    <option>Yes</option>
                                                    <option>No</option>
                                                    <option>Unknown</option>
                                                </select>
                                            </div>
                                        </div>
                                        {{-- Row 6: Note --}}
                                        <div>
                                            <label class="field-label">Note</label>
                                            <textarea x-model="birthNotes" rows="3" class="field-input" placeholder="Enter Note"></textarea>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3 px-6 py-4 border-t border-neutral-200 flex-shrink-0">
                                        <button type="button" @click="showModal = false" class="btn-secondary text-xs px-5 py-2">Close</button>
                                        <button type="button" @click="showModal = false" class="btn-primary text-xs px-5 py-2">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Immunization History --}}
                        <div class="flex items-center justify-between px-5 py-4"
                             x-data="immunizationManager()">
                            <span class="text-sm font-medium text-neutral-700">Immunization History</span>
                            <div class="flex gap-2">
                                <button type="button" x-show="entries.length > 0" @click="showModal = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-300 rounded hover:bg-neutral-200 transition" style="display:none;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit Record
                                </button>
                                <button type="button" @click="showModal = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Record
                                </button>
                            </div>

                            <input type="hidden" name="immunization_history" :value="JSON.stringify(entries)">

                            <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                                <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" @click.stop>
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0">
                                        <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">Immunization History</h3>
                                        <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="overflow-y-auto flex-1 p-6 space-y-4">
                                        {{-- Add entry form --}}
                                        <div class="border border-neutral-200 rounded p-4 space-y-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="field-label">Vaccine Type <span class="text-red-500">*</span></label>
                                                    <select x-model="form.vaccine_type" @change="form.vaccine = ''; form.dose = ''" class="field-input">
                                                        <option value="">--Select--</option>
                                                        <template x-for="vt in vaccineTypes" :key="vt.name">
                                                            <option :value="vt.name" x-text="vt.name"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="field-label">Vaccine <span class="text-red-500">*</span></label>
                                                    <select x-model="form.vaccine" class="field-input">
                                                        <option value="">--Select--</option>
                                                        <template x-for="v in availableVaccines" :key="v">
                                                            <option :value="v" x-text="v"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="field-label">Vaccine Dose <span class="text-red-500">*</span></label>
                                                    <select x-model="form.dose" class="field-input">
                                                        <option value="">--Select--</option>
                                                        <template x-for="d in availableDoses" :key="d">
                                                            <option :value="d" x-text="d"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="field-label">Batch Number</label>
                                                    <input type="text" x-model="form.batch_number" class="field-input" placeholder="Enter Batch Number">
                                                </div>
                                                <div>
                                                    <label class="field-label">Date Given <span class="text-red-500">*</span></label>
                                                    <input type="date" x-model="form.date_given" class="field-input" max="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>
                                            <button type="button" @click="addEntry()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-neutral-900 text-white rounded hover:bg-neutral-800 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Add
                                            </button>
                                        </div>

                                        {{-- Entries table --}}
                                        <template x-if="entries.length > 0">
                                            <div class="border border-neutral-200 rounded overflow-hidden">
                                                <table class="w-full text-sm">
                                                    <thead class="bg-neutral-100">
                                                        <tr>
                                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Vaccine Type</th>
                                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Vaccine</th>
                                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Dose</th>
                                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Batch #</th>
                                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Date Given</th>
                                                            <th class="px-2 py-1.5 w-8"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-neutral-100">
                                                        <template x-for="(entry, idx) in entries" :key="idx">
                                                            <tr>
                                                                <td class="px-3 py-1.5 text-neutral-800 text-xs" x-text="entry.vaccine_type"></td>
                                                                <td class="px-3 py-1.5 text-neutral-800 text-xs font-medium" x-text="entry.vaccine"></td>
                                                                <td class="px-3 py-1.5 text-neutral-600 text-xs" x-text="entry.dose"></td>
                                                                <td class="px-3 py-1.5 text-neutral-600 text-xs" x-text="entry.batch_number || '—'"></td>
                                                                <td class="px-3 py-1.5 text-neutral-600 text-xs" x-text="entry.date_given || '—'"></td>
                                                                <td class="px-2 py-1.5 text-center">
                                                                    <button type="button" @click="removeEntry(idx)" class="text-red-400 hover:text-red-700 transition">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex justify-end gap-3 px-6 py-4 border-t border-neutral-200 flex-shrink-0">
                                        <button type="button" @click="showModal = false" class="btn-secondary text-xs px-4 py-2">Close</button>
                                        <button type="button" @click="showModal = false" class="btn-primary text-xs px-4 py-2">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Feeding History --}}
                        <div class="flex items-center justify-between px-5 py-4"
                             x-data="feedingHistoryModal(@js(old('feeding_code', $s?->feeding_code ?? '')), @js(old('feeding_comments', $s?->feeding_comments ?? '')))">
                            <span class="text-sm font-medium text-neutral-700">Feeding History</span>
                            <div class="flex gap-2">
                                <button type="button" x-show="hasData" @click="showModal = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-300 rounded hover:bg-neutral-200 transition" style="display:none;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit Record
                                </button>
                                <button type="button" @click="showModal = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Record
                                </button>
                            </div>

                            <input type="hidden" name="feeding_code" :value="feedingCode">
                            <input type="hidden" name="feeding_comments" :value="feedingComments">

                            <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                                <div class="bg-white rounded-lg shadow-2xl w-full max-w-xl flex flex-col" @click.stop>
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
                                        <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">Feeding History</h3>
                                        <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="field-label">Infant Feeding Code</label>
                                            <select x-model="feedingCode" class="field-input">
                                                <option value="">— Select —</option>
                                                <option value="Exclusive breastfeeding">Exclusive breastfeeding (in the 1st 6 months, breastfeeding only, no water, no other fluids except medicines)</option>
                                                <option value="Exclusive alternative infant formula">Exclusive alternative infant formula</option>
                                                <option value="Animal milk">Animal milk</option>
                                                <option value="Mixed feeding">Mixed feeding (breast milk and other foods)</option>
                                                <option value="Continued breastfeeding after 6 months">Continued breastfeeding after 6 months in addition to other foods</option>
                                                <option value="Milk based feed after 6 months">Milk based feed after 6 months in addition to other foods</option>
                                                <option value="Complimentary feeding after 6 months">Complimentary feeding after 6 months</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Comments</label>
                                            <textarea x-model="feedingComments" rows="3" class="field-input" placeholder="Additional feeding details..."></textarea>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3 px-6 py-4 border-t border-neutral-200">
                                        <button type="button" @click="showModal = false" class="btn-secondary text-xs px-4 py-2">Close</button>
                                        <button type="button" @click="showModal = false" class="btn-primary text-xs px-4 py-2">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Development History --}}
                        <div class="flex items-center justify-between px-5 py-4"
                             x-data="developmentHistory()">
                            <span class="text-sm font-medium text-neutral-700">Development History</span>
                            <div class="flex gap-2">
                                <button type="button" x-show="filledCount > 0" @click="showModal = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-300 rounded hover:bg-neutral-200 transition" style="display:none;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit Record
                                </button>
                                <button type="button" @click="showModal = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Record
                                </button>
                            </div>

                            <input type="hidden" name="development_history" :value="JSON.stringify(milestones.filter(m => m.achieved).map(m => ({key: m.key, name: m.name, limits: m.limits, achieved: m.achieved, unit: m.unit})))">

                            <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                                <div class="bg-white rounded-lg shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col" @click.stop>
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0">
                                        <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">Development History</h3>
                                        <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="overflow-y-auto flex-1 px-6 py-4">
                                        <table class="w-full">
                                            <thead class="sticky top-0 bg-white">
                                                <tr class="border-b-2 border-neutral-200">
                                                    <th class="text-left py-2 text-xs font-bold text-neutral-700 uppercase w-2/5">Milestone</th>
                                                    <th class="text-left py-2 text-xs font-bold text-neutral-700 uppercase w-1/4">Normal Limits</th>
                                                    <th class="text-left py-2 text-xs font-bold text-neutral-700 uppercase w-1/3">Age Achieved</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-neutral-100">
                                                <template x-for="m in milestones" :key="m.key">
                                                    <tr class="hover:bg-neutral-50">
                                                        <td class="py-2.5 pr-3 text-sm text-neutral-800" x-text="m.name"></td>
                                                        <td class="py-2.5 pr-3 text-sm text-neutral-500" x-text="m.limits"></td>
                                                        <td class="py-2.5">
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-[11px] text-neutral-500 font-medium w-12" x-text="m.unit"></span>
                                                                <input type="number" min="0" step="1" x-model="m.achieved"
                                                                       class="field-input !py-1.5 !text-sm w-28"
                                                                       :placeholder="'Enter ' + m.unit">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="flex justify-end gap-3 px-6 py-4 border-t border-neutral-200 flex-shrink-0">
                                        <button type="button" @click="showModal = false" class="btn-secondary text-xs px-4 py-2">Close</button>
                                        <button type="button" @click="showModal = false" class="btn-primary text-xs px-4 py-2">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════════════════
                     TAB 3: Examination & Diagnosis
                     ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-panel p-6 space-y-4" data-tab="examination">

                    {{-- General Assessment --}}
                    <div class="section-card"
                        x-data="generalAssessmentModal()"
                        x-init="initFromText($el.dataset.initialAssessment)"
                        data-initial-assessment="{{ old('physical_examination', $s?->physical_examination ?? '') }}">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            General Assessment
                        </div>

                        <div class="flex justify-end gap-2 mb-3">
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">
                                Open Form
                            </button>
                            <button type="button" x-show="hasData" @click="showModal = true" class="btn-secondary text-xs px-3 py-1.5" style="display:none;">
                                Edit Record
                            </button>
                        </div>

                        <template x-if="hasData">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <template x-for="row in summaryRows" :key="row.label">
                                    <div class="bg-neutral-50 rounded px-3 py-2 border border-neutral-200">
                                        <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5" x-text="row.label"></p>
                                        <p class="text-sm text-neutral-800" x-text="row.value"></p>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="!hasData">
                            <p class="text-xs text-neutral-500">No general assessment record captured yet.</p>
                        </template>

                        <input type="hidden" name="physical_examination" :value="serialized">

                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                            <div class="bg-white rounded-lg shadow-2xl w-full max-w-5xl" @click.stop>
                                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
                                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">General Assessment</h3>
                                    <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="p-6 border border-neutral-200 rounded mx-6 mt-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="field-label">General Condition <span class="text-red-500">*</span></label>
                                            <select x-model="form.general_condition" class="field-input">
                                                <option value="">--Select--</option>
                                                <option value="Good">Good</option>
                                                <option value="Stable">Stable</option>
                                                <option value="Critical">Critical</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Pallor</label>
                                            <select x-model="form.pallor" class="field-input">
                                                <option value="">--Select--</option>
                                                <option value="Nil">Nil</option>
                                                <option value="Mild">Mild</option>
                                                <option value="Moderate">Moderate</option>
                                                <option value="Severe">Severe</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Edema</label>
                                            <select x-model="form.edema" class="field-input">
                                                <option value="">--Select--</option>
                                                <option value="Nil">Nil</option>
                                                <option value="1+">1+</option>
                                                <option value="2+">2+</option>
                                                <option value="3+">3+</option>
                                                <option value="4+">4+</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Clubbing</label>
                                            <select x-model="form.clubbing" class="field-input">
                                                <option value="">--Select--</option>
                                                <option value="Nil">Nil</option>
                                                <option value="1+">1+</option>
                                                <option value="2+">2+</option>
                                                <option value="3+">3+</option>
                                                <option value="4+">4+</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Jaundice</label>
                                            <select x-model="form.jaundice" class="field-input">
                                                <option value="">--Select--</option>
                                                <option value="Yellow">Yellow</option>
                                                <option value="Not present">Not present</option>
                                                <option value="Tinge">Tinge</option>
                                                <option value="Green">Green</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Cyanosis</label>
                                            <select x-model="form.cyanosis" class="field-input">
                                                <option value="">--Select--</option>
                                                <option value="Nil">Nil</option>
                                                <option value="Mild">Mild</option>
                                                <option value="Moderate">Moderate</option>
                                                <option value="Severe">Severe</option>
                                            </select>
                                        </div>
                                    </div>
                                    <p x-show="error" class="text-xs text-red-600 mt-3" x-text="error"></p>
                                </div>

                                <div class="flex items-center justify-center gap-3 px-6 py-4 mt-3 border-t border-neutral-200">
                                    <button type="button" @click="showModal = false" class="btn-secondary">Close</button>
                                    <button type="button" @click="saveAssessment()" class="btn-primary">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- System Examination --}}
                    <div class="section-card"
                         x-data="systemExaminationModal()"
                         x-init="initFromText($el.dataset.initialFindings)"
                         data-initial-findings="{{ old('clinical_findings', $s?->clinical_findings ?? '') }}">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            System Examination
                        </div>

                        <div class="flex justify-end gap-2 mb-3">
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">Open Form</button>
                            <button type="button" x-show="entries.length > 0" @click="showModal = true" class="btn-secondary text-xs px-3 py-1.5" style="display:none;">Edit Record</button>
                        </div>

                        <template x-if="entries.length > 0">
                            <div class="border border-neutral-200 rounded overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-neutral-100">
                                        <tr>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">System</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-100">
                                        <template x-for="(entry, idx) in entries" :key="idx">
                                            <tr>
                                                <td class="px-3 py-1.5 text-neutral-800 text-xs font-medium" x-text="entry.system"></td>
                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.notes"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template x-if="entries.length === 0 && !initialRaw">
                            <p class="text-xs text-neutral-500">No system examination record captured yet.</p>
                        </template>

                        <input type="hidden" name="clinical_findings" :value="serialized">

                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                            <div class="bg-white rounded-lg shadow-2xl w-full max-w-5xl" @click.stop>
                                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
                                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">System Examination</h3>
                                    <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="p-6 border border-neutral-200 rounded mx-6 mt-4">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="field-label">System <span class="text-red-500">*</span></label>
                                            <select x-model="form.system" class="field-input">
                                                <option value="">--Select--</option>
                                                <option value="Other">Other</option>
                                                <option value="Endocrine system">Endocrine system</option>
                                                <option value="Systemic Symptoms">Systemic Symptoms</option>
                                                <option value="Ear, Nose, and Throat">Ear, Nose, and Throat</option>
                                                <option value="Respiratory System">Respiratory System</option>
                                                <option value="Gastro-Intestinal System">Gastro-Intestinal System</option>
                                                <option value="Cardiovascular System">Cardiovascular System</option>
                                                <option value="Genito-Urinary System">Genito-Urinary System</option>
                                                <option value="Musculoskeletal System">Musculoskeletal System</option>
                                                <option value="Integumentary system">Integumentary system</option>
                                                <option value="Nervous System">Nervous System</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Notes <span class="text-red-500">*</span></label>
                                            <textarea x-model="form.notes" rows="2" class="field-input" placeholder="Enter Notes"></textarea>
                                        </div>
                                        <button type="button" @click="addEntry()" class="btn-primary text-xs px-3 py-1.5">Add</button>
                                        <p x-show="error" class="text-xs text-red-600" x-text="error"></p>
                                    </div>

                                    <template x-if="entries.length > 0">
                                        <div class="mt-4 border border-neutral-200 rounded overflow-hidden">
                                            <table class="w-full text-sm">
                                                <thead class="bg-neutral-100">
                                                    <tr>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">System</th>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Notes</th>
                                                        <th class="px-2 py-1.5 w-8"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-neutral-100">
                                                    <template x-for="(entry, idx) in entries" :key="idx">
                                                        <tr>
                                                            <td class="px-3 py-1.5 text-neutral-800 text-xs font-medium" x-text="entry.system"></td>
                                                            <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.notes"></td>
                                                            <td class="px-2 py-1.5 text-center">
                                                                <button type="button" @click="removeEntry(idx)" class="text-neutral-400 hover:text-neutral-700 transition">×</button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex items-center justify-center gap-3 px-6 py-4 mt-3 border-t border-neutral-200">
                                    <button type="button" @click="showModal = false" class="btn-secondary">Close</button>
                                    <button type="button" @click="showModal = false" class="btn-primary">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Glasgow Coma Scale --}}
                    <div class="section-card"
                         x-data="glasgowComaScaleModal()"
                         x-init="initFromText($el.dataset.initialGcs)"
                         data-initial-gcs="{{ old('assessment_notes', $s?->assessment_notes ?? '') }}">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Glasgow Coma Scale
                        </div>

                        <div class="flex justify-end gap-2 mb-3">
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">Open Form</button>
                            <button type="button" x-show="hasData" @click="showModal = true" class="btn-secondary text-xs px-3 py-1.5" style="display:none;">Edit Record</button>
                        </div>

                        <template x-if="hasData">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div class="bg-neutral-50 rounded px-3 py-2 border border-neutral-200">
                                    <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Eye Score</p>
                                    <p class="text-sm text-neutral-800" x-text="selectedEyeLabel"></p>
                                </div>
                                <div class="bg-neutral-50 rounded px-3 py-2 border border-neutral-200">
                                    <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Verbal Score</p>
                                    <p class="text-sm text-neutral-800" x-text="selectedVerbalLabel"></p>
                                </div>
                                <div class="bg-neutral-50 rounded px-3 py-2 border border-neutral-200">
                                    <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Motor Score</p>
                                    <p class="text-sm text-neutral-800" x-text="selectedMotorLabel"></p>
                                </div>
                                <div class="bg-neutral-50 rounded px-3 py-2 border border-neutral-200">
                                    <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Score</p>
                                    <p class="text-sm text-neutral-800" x-text="totalScore + ' / 15'"></p>
                                </div>
                                <div class="bg-neutral-50 rounded px-3 py-2 border border-neutral-200 md:col-span-2">
                                    <p class="text-[10px] font-semibold text-neutral-500 uppercase mb-0.5">Result</p>
                                    <p class="text-sm text-neutral-800" x-text="form.result"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="!hasData && !initialRaw">
                            <p class="text-xs text-neutral-500">No Glasgow Coma Scale record captured yet.</p>
                        </template>

                        <input type="hidden" name="assessment_notes" :value="serialized">

                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                            <div class="bg-white rounded-lg shadow-2xl w-full max-w-5xl" @click.stop>
                                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
                                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">Glasgow Coma Scale</h3>
                                    <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="p-6 border border-neutral-200 rounded mx-6 mt-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="field-label">Eye Score <span class="text-red-500">*</span></label>
                                            <select x-model="form.eye" class="field-input">
                                                <option value="">--Select--</option>
                                                <template x-for="opt in eyeOptions" :key="opt.label">
                                                    <option :value="opt.value" x-text="opt.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Verbal Score <span class="text-red-500">*</span></label>
                                            <select x-model="form.verbal" class="field-input">
                                                <option value="">--Select--</option>
                                                <template x-for="opt in verbalOptions" :key="opt.label">
                                                    <option :value="opt.value" x-text="opt.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Motor Score <span class="text-red-500">*</span></label>
                                            <select x-model="form.motor" class="field-input">
                                                <option value="">--Select--</option>
                                                <template x-for="opt in motorOptions" :key="opt.label">
                                                    <option :value="opt.value" x-text="opt.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Score <span class="text-red-500">*</span></label>
                                            <input type="text" class="field-input bg-neutral-100" :value="totalScore" readonly>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="field-label">Result <span class="text-red-500">*</span></label>
                                            <textarea x-model="form.result" rows="2" class="field-input" placeholder="Result"></textarea>
                                        </div>
                                    </div>
                                    <p x-show="error" class="text-xs text-red-600 mt-3" x-text="error"></p>
                                </div>

                                <div class="flex items-center justify-center gap-3 px-6 py-4 mt-3 border-t border-neutral-200">
                                    <button type="button" @click="showModal = false" class="btn-secondary">Close</button>
                                    <button type="button" @click="saveGcs()" class="btn-primary">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Diagnosis --}}
                    <div class="section-card"
                        x-data="diagnosisModal(@js($icd11Library ?? []))"
                         x-init="initFromText($el.dataset.initialProvisional, $el.dataset.initialFinal)"
                         data-initial-provisional="{{ old('provisional_diagnosis', $s?->provisional_diagnosis ?? '') }}"
                         data-initial-final="{{ old('final_diagnosis', $s?->final_diagnosis ?? '') }}">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            Diagnosis
                        </div>

                        <div class="flex justify-end gap-2 mb-3">
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">Open Form</button>
                            <button type="button" x-show="entries.length > 0" @click="showModal = true" class="btn-secondary text-xs px-3 py-1.5" style="display:none;">Edit Record</button>
                        </div>

                        <template x-if="entries.length > 0">
                            <div class="border border-neutral-200 rounded overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-neutral-100">
                                        <tr>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Type</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Path</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Certainty</th>
                                            <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Attendance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-100">
                                        <template x-for="(entry, idx) in entries" :key="idx">
                                            <tr>
                                                <td class="px-3 py-1.5 text-neutral-800 text-xs font-medium" x-text="entry.type"></td>
                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.path"></td>
                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.certainty"></td>
                                                <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.attendance"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template x-if="entries.length === 0">
                            <p class="text-xs text-neutral-500">No diagnosis records captured yet.</p>
                        </template>

                        <input type="hidden" name="provisional_diagnosis" :value="serializedProvisional">
                        <input type="hidden" name="final_diagnosis" :value="serializedFinal">

                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false" style="display:none;">
                            <div class="bg-white rounded-lg shadow-2xl w-full max-w-6xl" @click.stop>
                                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
                                    <h3 class="text-sm font-bold text-neutral-900 uppercase tracking-wide">Diagnosis</h3>
                                    <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="p-6 border border-neutral-200 rounded mx-6 mt-4">
                                    <div class="grid grid-cols-2 gap-3 mb-4">
                                        <button type="button" @click="form.type='National Treatment Guideline'" :class="form.type==='National Treatment Guideline' ? 'btn-primary' : 'btn-secondary'" class="text-xs py-2">National Treatment Guideline</button>
                                        <button type="button" @click="form.type='ICD 11'" :class="form.type==='ICD 11' ? 'btn-primary' : 'btn-secondary'" class="text-xs py-2">ICD 11</button>
                                    </div>

                                    <div class="space-y-3">
                                        <template x-if="form.type === 'National Treatment Guideline'">
                                            <div class="space-y-3">
                                                <div>
                                                    <label class="field-label">NTG Level 1 <span class="text-red-500">*</span></label>
                                                    <select x-model="form.level1" @change="onLevel1Change()" class="field-input">
                                                        <option value="">Search</option>
                                                        <option value="Anaemia And Nutritional Conditions">Anaemia And Nutritional Conditions</option>
                                                        <option value="Cardiovascular Disorders">Cardiovascular Disorders</option>
                                                        <option value="Conditions Of The Ear, Nose And Oropharynx">Conditions Of The Ear, Nose And Oropharynx</option>
                                                        <option value="Dermatological Conditions">Dermatological Conditions</option>
                                                        <option value="Disorders Of The Renal System">Disorders Of The Renal System</option>
                                                        <option value="Urology disorders">Urology disorders</option>
                                                        <option value="Eye Disease">Eye Disease</option>
                                                        <option value="Gastro-intestinal conditions">Gastro-intestinal conditions</option>
                                                        <option value="Infections">Infections</option>
                                                        <option value="Malignancies">Malignancies</option>
                                                        <option value="Orthopedic conditions">Orthopedic conditions</option>
                                                        <option value="Obstetric &amp; Gynaecological conditions">Obstetric &amp; Gynaecological conditions</option>
                                                        <option value="Poisoning">Poisoning</option>
                                                        <option value="Surgical conditions">Surgical conditions</option>
                                                        <option value="Haematological conditions">Haematological conditions</option>
                                                        <option value="Dental conditions">Dental conditions</option>
                                                        <option value="Mental health and psychiatric disorders">Mental health and psychiatric disorders</option>
                                                        <option value="Endocrine disorders">Endocrine disorders</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="field-label">NTG Level 2 <span class="text-red-500">*</span></label>
                                                    <select x-model="form.level2" @change="onLevel2Change()" class="field-input">
                                                        <option value="">Search</option>
                                                        <template x-for="option in level2Options" :key="option">
                                                            <option :value="option" x-text="option"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="field-label">NTG Level 3 <span class="text-red-500">*</span></label>
                                                    <select x-model="form.level3" class="field-input">
                                                        <option value="">Search</option>
                                                        <template x-for="option in level3Options" :key="option">
                                                            <option :value="option" x-text="option"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="form.type === 'ICD 11'">
                                            <div>
                                                <label class="field-label">ICD 11 <span class="text-red-500">*</span></label>
                                                <div class="relative">
                                                    <input type="text"
                                                           x-model="form.icd11"
                                                           @focus="showIcd11Dropdown = form.icd11.trim().length > 0"
                                                           @input="showIcd11Dropdown = form.icd11.trim().length > 0"
                                                           @keydown.escape="showIcd11Dropdown = false"
                                                           class="field-input"
                                                           placeholder="Type search text">
                                                    <div x-show="showIcd11Dropdown"
                                                         x-transition
                                                         @click.outside="showIcd11Dropdown = false"
                                                         class="absolute z-20 mt-1 w-full bg-white border border-neutral-300 rounded shadow max-h-56 overflow-y-auto"
                                                         style="display:none;">
                                                        <template x-for="item in filteredIcd11" :key="item">
                                                            <button type="button"
                                                                    @click="selectIcd11(item)"
                                                                    class="block w-full text-left px-3 py-2 text-sm hover:bg-neutral-100 text-neutral-800"
                                                                    x-text="item"></button>
                                                        </template>
                                                        <p x-show="filteredIcd11.length === 0" class="px-3 py-2 text-xs text-neutral-500">No match found</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <div>
                                            <label class="field-label">Certainty <span class="text-red-500">*</span></label>
                                            <select x-model="form.certainty" class="field-input">
                                                <option value="">--Select--</option>
                                                <option value="Confirmed">Confirmed</option>
                                                <option value="Probable">Probable</option>
                                                <option value="Possible">Possible</option>
                                                <option value="Rule Out">Rule Out</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Attendance <span class="text-red-500">*</span></label>
                                            <select x-model="form.attendance" class="field-input">
                                                <option value="">--Select--</option>
                                                <option value="First visit">First visit</option>
                                                <option value="Follow-up">Follow-up</option>
                                                <option value="Emergency">Emergency</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Comments</label>
                                            <textarea x-model="form.comments" rows="2" class="field-input" placeholder="Enter Comments"></textarea>
                                        </div>

                                        <button type="button" @click="addEntry()" class="btn-primary text-xs px-3 py-1.5">Add</button>
                                        <p x-show="error" class="text-xs text-red-600" x-text="error"></p>
                                    </div>

                                    <template x-if="entries.length > 0">
                                        <div class="mt-4 border border-neutral-200 rounded overflow-hidden">
                                            <table class="w-full text-sm">
                                                <thead class="bg-neutral-100">
                                                    <tr>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Type</th>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Path</th>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Certainty</th>
                                                        <th class="text-left px-3 py-1.5 text-[10px] font-semibold text-neutral-500 uppercase">Attendance</th>
                                                        <th class="px-2 py-1.5 w-8"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-neutral-100">
                                                    <template x-for="(entry, idx) in entries" :key="idx">
                                                        <tr>
                                                            <td class="px-3 py-1.5 text-neutral-800 text-xs font-medium" x-text="entry.type"></td>
                                                            <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.path"></td>
                                                            <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.certainty"></td>
                                                            <td class="px-3 py-1.5 text-neutral-700 text-xs" x-text="entry.attendance"></td>
                                                            <td class="px-2 py-1.5 text-center"><button type="button" @click="removeEntry(idx)" class="text-neutral-400 hover:text-neutral-700 transition">×</button></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex items-center justify-center gap-3 px-6 py-4 mt-3 border-t border-neutral-200">
                                    <button type="button" @click="showModal = false" class="btn-secondary">Close</button>
                                    <button type="button" @click="showModal = false" class="btn-primary">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════════════════
                     TAB 4: Prescription
                     ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-panel p-6 space-y-4" data-tab="prescription"
                     x-data="prescriptionCart()">

                    <input type="hidden" name="prescriptions" :value="JSON.stringify(cart)">

                    {{-- Prescription table card with Add button in header --}}
                    <div class="section-card !p-0 overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-neutral-200 flex items-center justify-between">
                            <span class="text-sm font-bold text-neutral-800">Prescription List</span>
                            <button type="button" @click="showModal = true" class="btn-primary text-xs px-3 py-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add Prescription
                            </button>
                        </div>

                        @php
                            $savedItems = $encounter->prescription?->items ?? collect();
                            $savedPrescription = $encounter->prescription;
                        @endphp

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-blue-50">
                                    <tr>
                                        <th class="text-left px-3 py-2.5 text-[10px] font-bold text-neutral-600 uppercase whitespace-nowrap">Prescription Date</th>
                                        <th class="text-left px-3 py-2.5 text-[10px] font-bold text-neutral-600 uppercase whitespace-nowrap">Clinician</th>
                                        <th class="text-left px-3 py-2.5 text-[10px] font-bold text-neutral-600 uppercase whitespace-nowrap">Drug Name</th>
                                        <th class="text-left px-3 py-2.5 text-[10px] font-bold text-neutral-600 uppercase whitespace-nowrap">Is Passer By</th>
                                        <th class="text-left px-3 py-2.5 text-[10px] font-bold text-neutral-600 uppercase whitespace-nowrap">Frequency</th>
                                        <th class="text-left px-3 py-2.5 text-[10px] font-bold text-neutral-600 uppercase whitespace-nowrap">Qty</th>
                                        <th class="text-left px-3 py-2.5 text-[10px] font-bold text-neutral-600 uppercase whitespace-nowrap">Duration</th>
                                        <th class="text-left px-3 py-2.5 text-[10px] font-bold text-neutral-600 uppercase whitespace-nowrap">Route</th>
                                        <th class="text-left px-3 py-2.5 text-[10px] font-bold text-neutral-600 uppercase whitespace-nowrap">Comment</th>
                                        <th class="px-2 py-2.5 w-8"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100">
                                    {{-- Previously saved items --}}
                                    @foreach($savedItems as $item)
                                    <tr class="hover:bg-neutral-50">
                                        <td class="px-3 py-2.5 text-neutral-700 text-xs whitespace-nowrap">{{ $savedPrescription?->prescribed_at?->format('d-m-Y') ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-neutral-700 text-xs whitespace-nowrap">{{ $savedPrescription?->prescribedBy?->name ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-neutral-900 text-xs font-medium">{{ $item->drug_name }}</td>
                                        <td class="px-3 py-2.5 text-xs">
                                            <span class="{{ $item->is_passer_by ? 'text-green-700 bg-green-100' : 'text-neutral-600 bg-neutral-100' }} px-2 py-0.5 rounded-full text-[10px] font-semibold">
                                                {{ $item->is_passer_by ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2.5 text-neutral-600 text-xs">{{ $item->frequency ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-neutral-600 text-xs">{{ $item->quantity_prescribed ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-neutral-600 text-xs">{{ $item->duration }}{{ $item->duration_unit ? ' '.$item->duration_unit : '' }}</td>
                                        <td class="px-3 py-2.5 text-neutral-600 text-xs">{{ $item->route ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-neutral-500 text-xs max-w-[160px] truncate">{{ $item->instructions ?? '—' }}</td>
                                        <td class="px-2 py-2.5 text-center text-neutral-300 text-xs">—</td>
                                    </tr>
                                    @endforeach

                                    {{-- Cart items (unsaved, Alpine) --}}
                                    <template x-for="(item, idx) in cart" :key="idx">
                                        <tr class="bg-blue-50/50 hover:bg-blue-50">
                                            <td class="px-3 py-2.5 text-neutral-400 text-xs whitespace-nowrap italic">Pending</td>
                                            <td class="px-3 py-2.5 text-neutral-400 text-xs">—</td>
                                            <td class="px-3 py-2.5 text-neutral-900 text-xs font-medium" x-text="item.drug_name"></td>
                                            <td class="px-3 py-2.5 text-xs">
                                                <span :class="item.is_passer_by == '1' ? 'text-green-700 bg-green-100' : 'text-neutral-600 bg-neutral-100'"
                                                      class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                                      x-text="item.is_passer_by == '1' ? 'Yes' : 'No'"></span>
                                            </td>
                                            <td class="px-3 py-2.5 text-neutral-600 text-xs" x-text="item.frequency || '—'"></td>
                                            <td class="px-3 py-2.5 text-neutral-600 text-xs" x-text="item.quantity_prescribed || '—'"></td>
                                            <td class="px-3 py-2.5 text-neutral-600 text-xs" x-text="(item.duration || '—') + (item.duration_unit ? ' '+item.duration_unit : '')"></td>
                                            <td class="px-3 py-2.5 text-neutral-600 text-xs" x-text="item.route || '—'"></td>
                                            <td class="px-3 py-2.5 text-neutral-500 text-xs max-w-[160px] truncate" x-text="item.instructions || '—'"></td>
                                            <td class="px-2 py-2.5 text-center">
                                                <button type="button" @click="removeFromCart(idx)" class="text-red-400 hover:text-red-700 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>

                                    {{-- Empty state --}}
                                    <template x-if="cart.length === 0 && {{ $savedItems->isEmpty() ? 'true' : 'false' }}">
                                        <tr>
                                            <td colspan="10" class="px-4 py-10 text-center text-neutral-400 text-sm">
                                                No prescriptions added yet. Click <strong>Add Prescription</strong> to begin.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-end gap-2 px-4 py-2.5 border-t border-neutral-100 text-xs text-neutral-500">
                            <span>Show</span>
                            <select class="border border-neutral-300 rounded px-1.5 py-0.5 text-xs w-14 focus:outline-none">
                                <option>5</option><option>10</option><option>25</option>
                            </select>
                        </div>
                    </div>

                    {{-- ── Add Prescription Modal ── --}}
                    <div x-show="showModal" x-transition.opacity
                         class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4"
                         @click.self="showModal = false" style="display:none;">
                        <div class="bg-white rounded-lg shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col" @click.stop>

                            {{-- Modal header --}}
                            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0">
                                <h3 class="text-base font-bold text-neutral-900">Add Prescription</h3>
                                <button type="button" @click="showModal = false" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 transition">
                                    <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            {{-- Modal body --}}
                            <div class="overflow-y-auto flex-1 p-6 space-y-4">

                                {{-- Row 1: General Drug / Dosage / Item Per Dose --}}
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="field-label">General Drug <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="form.drug_name" class="field-input" placeholder="Enter General Drug">
                                    </div>
                                    <div>
                                        <label class="field-label">Dosage <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="form.dose" class="field-input" placeholder="Enter Dosage">
                                    </div>
                                    <div>
                                        <label class="field-label">Item Per Dose <span class="text-red-500">*</span></label>
                                        <input type="number" x-model="form.item_per_dose" min="0" class="field-input" placeholder="0">
                                    </div>
                                </div>

                                {{-- Row 2: Frequency / Time Per / Frequency Unit --}}
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="field-label">Frequency <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="form.frequency" class="field-input" placeholder="Enter Frequency">
                                    </div>
                                    <div>
                                        <label class="field-label">Time Per (Time Unit) <span class="text-red-500">*</span></label>
                                        <select x-model="form.time_per" class="field-input">
                                            <option value="">--Select--</option>
                                            <option>Hourly</option>
                                            <option>Every 2 Hours</option>
                                            <option>Every 4 Hours</option>
                                            <option>Every 6 Hours</option>
                                            <option>Every 8 Hours</option>
                                            <option>Every 12 Hours</option>
                                            <option>Daily</option>
                                            <option>Weekly</option>
                                            <option>Monthly</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="field-label">Frequency Unit (if not Time unit) <span class="text-red-500">*</span></label>
                                        <select x-model="form.frequency_unit" class="field-input">
                                            <option value="">--Select--</option>
                                            <option>Once Daily</option>
                                            <option>Twice Daily</option>
                                            <option>Three Times Daily</option>
                                            <option>Four Times Daily</option>
                                            <option>Every Morning</option>
                                            <option>Every Night</option>
                                            <option>With Meals</option>
                                            <option>Before Meals</option>
                                            <option>After Meals</option>
                                            <option>As Required (PRN)</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Row 3: Duration / Duration Unit / Route --}}
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="field-label">Duration <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="form.duration" class="field-input" placeholder="Enter Duration">
                                    </div>
                                    <div>
                                        <label class="field-label">Duration Unit</label>
                                        <select x-model="form.duration_unit" class="field-input">
                                            <option value="">--Select--</option>
                                            <option>Days</option>
                                            <option>Weeks</option>
                                            <option>Months</option>
                                            <option>Years</option>
                                            <option>Indefinite</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="field-label">Route <span class="text-red-500">*</span></label>
                                        <select x-model="form.route" class="field-input">
                                            <option value="">--Select--</option>
                                            <option>Oral</option>
                                            <option>Intravenous (IV)</option>
                                            <option>Intramuscular (IM)</option>
                                            <option>Subcutaneous (SC)</option>
                                            <option>Topical</option>
                                            <option>Inhaled</option>
                                            <option>Sublingual</option>
                                            <option>Rectal</option>
                                            <option>Nasal</option>
                                            <option>Ophthalmic</option>
                                            <option>Otic</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Row 4: Start Date / End Date / Quantity --}}
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="field-label">Start Date <span class="text-red-500">*</span></label>
                                        <input type="date" x-model="form.start_date" class="field-input">
                                    </div>
                                    <div>
                                        <label class="field-label">End Date <span class="text-red-500">*</span></label>
                                        <input type="date" x-model="form.end_date" class="field-input">
                                    </div>
                                    <div>
                                        <label class="field-label">Quantity</label>
                                        <input type="text" x-model="form.quantity_prescribed" class="field-input" placeholder="Enter Quantity">
                                    </div>
                                </div>

                                {{-- Row 5: Is Passer By --}}
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="field-label">Is Passer By</label>
                                        <select x-model="form.is_passer_by" class="field-input">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Row 6: Comments --}}
                                <div>
                                    <label class="field-label">Comments</label>
                                    <textarea x-model="form.instructions" rows="3" class="field-input" placeholder="Enter Comments"></textarea>
                                </div>

                                {{-- Validation error --}}
                                <p x-show="showError" x-text="errorMsg" class="text-xs text-red-500 font-medium" style="display:none;"></p>
                            </div>

                            {{-- Modal footer --}}
                            <div class="flex justify-end gap-3 px-6 py-4 border-t border-neutral-200 flex-shrink-0">
                                <button type="button" @click="showModal = false" class="btn-secondary text-xs px-4 py-2">Close</button>
                                <button type="button" @click="addToCart()" class="btn-primary text-xs px-4 py-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ═══════════════════════════════════════════════════════════
                     TAB 5: Plan
                     ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-panel p-6 space-y-4" data-tab="plan">

                    {{-- Treatment Plan --}}
                    <div class="section-card">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Treatment Plan
                        </div>
                        <textarea name="treatment_plan" rows="3" class="field-input" placeholder="Proposed treatment approach, interventions...">{{ old('treatment_plan', $s?->treatment_plan) }}</textarea>
                    </div>

                    {{-- Management Plan --}}
                    <div class="section-card">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            Management Plan
                        </div>
                        <textarea name="plan" rows="3" class="field-input" placeholder="Follow-up, referral, counselling, lifestyle advice...">{{ old('plan', $s?->plan) }}</textarea>
                    </div>

                    {{-- Lab / Next Step Decision --}}
                    <div class="section-card border-neutral-300">
                        <div class="section-card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            Next Step
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer select-none mb-3">
                            <input type="checkbox" name="lab_requested" value="1"
                                   x-model="labRequested"
                                   {{ old('lab_requested', $s?->lab_requested) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-neutral-300 text-neutral-700 focus:ring-neutral-500">
                            <span class="text-sm font-semibold text-neutral-800">Request Lab Tests</span>
                        </label>

                        <div x-show="labRequested" x-cloak class="bg-neutral-100 border border-neutral-200 rounded px-4 py-2 text-sm text-neutral-700 mb-3">
                            Patient will be queued to <strong>Lab</strong> after submission.
                        </div>
                        <div x-show="!labRequested" x-cloak class="bg-neutral-100 border border-neutral-200 rounded px-4 py-2 text-sm text-neutral-700 mb-3">
                            No lab required — patient will be queued directly to <strong>Pharmacy</strong>.
                        </div>

                        <div>
                            <label class="field-label">Handover Note <span class="unit">(optional)</span></label>
                            <textarea name="notes" rows="2" class="field-input" placeholder="Anything the next department should know…">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="flex items-center gap-3 pt-3 border-t border-neutral-200">
                        <button type="submit" x-show="labRequested" x-cloak class="btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                            Save &amp; Queue to Lab
                        </button>
                        <button type="submit" x-show="!labRequested" x-cloak class="btn-green">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                            Save &amp; Queue to Pharmacy
                        </button>
                        <a href="{{ route('screening.queue') }}" class="btn-secondary">Back</a>
                    </div>
                </div>

            </form>
        </div>

        {{-- Tab navigation: Back / Next buttons below form --}}
        <div class="flex items-center justify-center gap-3 mt-4">
            <button type="button" id="prevTabBtn" class="btn-secondary" style="display:none;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </button>
            <button type="button" id="nextTabBtn" class="btn-primary">
                Next
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>



</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = ['complaints', 'paediatric', 'examination', 'prescription', 'plan'];
    let currentIndex = 0;

    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');
    const prevBtn = document.getElementById('prevTabBtn');
    const nextBtn = document.getElementById('nextTabBtn');

    function activateTab(index) {
        currentIndex = index;

        tabBtns.forEach((btn, i) => {
            btn.classList.toggle('active', i === index);
        });
        tabPanels.forEach((panel, i) => {
            panel.classList.toggle('active', i === index);
        });

        // Update nav buttons
        prevBtn.style.display = index === 0 ? 'none' : 'inline-flex';

        if (index === tabs.length - 1) {
            nextBtn.style.display = 'none';
        } else {
            nextBtn.style.display = 'inline-flex';
        }

        // Scroll to top of form
        document.querySelector('.tab-nav').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Tab button clicks
    tabBtns.forEach((btn, i) => {
        btn.addEventListener('click', () => activateTab(i));
    });

    // Prev / Next
    prevBtn.addEventListener('click', () => {
        if (currentIndex > 0) activateTab(currentIndex - 1);
    });
    nextBtn.addEventListener('click', () => {
        if (currentIndex < tabs.length - 1) activateTab(currentIndex + 1);
    });

    activateTab(0);
});

// ── Review panel slide-down ─────────────────────────────────────────────────────
function toggleReviewPanel() {
    const panel = document.getElementById('reviewPanel');
    const icon = document.getElementById('reviewChevronIcon');
    const main = document.querySelector('#mainPanel > main');
    const isOpen = panel.style.maxHeight !== '0px' && panel.style.maxHeight !== '';
    if (isOpen) {
        panel.style.maxHeight = '0px';
        icon.classList.remove('rotate-180');
        if (main) main.classList.remove('blurred');
    } else {
        panel.style.maxHeight = '50vh';
        icon.classList.add('rotate-180');
        if (main) {
            main.classList.add('blurred');
            setTimeout(() => {
                main.addEventListener('click', closeReviewPanel, { once: true });
            }, 50);
        }
    }
}
function closeReviewPanel() {
    const panel = document.getElementById('reviewPanel');
    if (panel && panel.style.maxHeight !== '0px') toggleReviewPanel();
}

// ── General Assessment modal helper ─────────────────────────────────────────
function generalAssessmentModal() {
    const parsed = {
        general_condition: '',
        pallor: '',
        edema: '',
        clubbing: '',
        jaundice: '',
        cyanosis: '',
    };

    const map = {
        'General Condition': 'general_condition',
        'Pallor': 'pallor',
        'Edema': 'edema',
        'Clubbing': 'clubbing',
        'Jaundice': 'jaundice',
        'Cyanosis': 'cyanosis',
    };

    return {
        showModal: false,
        error: '',
        initialRaw: '',
        form: parsed,
        initFromText(initialText) {
            this.initialRaw = typeof initialText === 'string' ? initialText : '';
            if (typeof initialText === 'string' && initialText.includes(':')) {
                initialText.split(';').forEach((part) => {
                    const [label, ...rest] = part.trim().split(':');
                    if (!label || !rest.length) return;
                    const key = map[label.trim()];
                    if (key) this.form[key] = rest.join(':').trim();
                });
            }
        },
        get hasData() {
            return Object.values(this.form).some((v) => !!v);
        },
        get summaryRows() {
            return [
                { label: 'General Condition', value: this.form.general_condition },
                { label: 'Pallor', value: this.form.pallor },
                { label: 'Edema', value: this.form.edema },
                { label: 'Clubbing', value: this.form.clubbing },
                { label: 'Jaundice', value: this.form.jaundice },
                { label: 'Cyanosis', value: this.form.cyanosis },
            ].filter((row) => !!row.value);
        },
        get serialized() {
            if (!this.hasData) return this.initialRaw || '';
            const parts = [];
            if (this.form.general_condition) parts.push(`General Condition: ${this.form.general_condition}`);
            if (this.form.pallor) parts.push(`Pallor: ${this.form.pallor}`);
            if (this.form.edema) parts.push(`Edema: ${this.form.edema}`);
            if (this.form.clubbing) parts.push(`Clubbing: ${this.form.clubbing}`);
            if (this.form.jaundice) parts.push(`Jaundice: ${this.form.jaundice}`);
            if (this.form.cyanosis) parts.push(`Cyanosis: ${this.form.cyanosis}`);
            return parts.join('; ');
        },
        saveAssessment() {
            this.error = '';
            if (!this.form.general_condition) {
                this.error = 'General Condition is required.';
                return;
            }
            this.showModal = false;
        },
    };
}

// ── Glasgow Coma Scale modal helper ────────────────────────────────────────
function glasgowComaScaleModal() {
    const eyeOptions = [
        { value: '1', label: 'No response (1 point)' },
        { value: '2', label: 'To pain only (not applied to face) (2 points)' },
        { value: '3', label: 'To verbal stimuli, command, speech (3 points)' },
        { value: '4', label: 'Spontaneous - open with blinking at baseline (4 points)' },
    ];
    const verbalOptions = [
        { value: '1', label: 'No response (1point)' },
        { value: '2', label: 'Incomprehensible sounds (2 points)' },
        { value: '3', label: 'Inappropriate words (3 points)' },
        { value: '4', label: 'Confused, disoriented (4 points)' },
        { value: '5', label: 'Oriented (5 points)' },
    ];
    const motorOptions = [
        { value: '1', label: 'No response (1 point)' },
        { value: '2', label: 'Extension response in response to pain (2 point)' },
        { value: '3', label: 'Flexion in response to pain (decorticate posturing) (3 points)' },
        { value: '4', label: 'Withdraws in response to pain (4 points)' },
        { value: '5', label: 'Purposeful movement to painful stimulus (5 points)' },
        { value: '6', label: 'Obeys commands for movement (6 points)' },
    ];

    return {
        showModal: false,
        error: '',
        initialRaw: '',
        eyeOptions,
        verbalOptions,
        motorOptions,
        form: {
            eye: '',
            verbal: '',
            motor: '',
            result: '',
        },
        initFromText(initialText) {
            this.initialRaw = typeof initialText === 'string' ? initialText : '';
            if (typeof initialText !== 'string' || !initialText.startsWith('GCS - Eye:')) return;

            const m = initialText.match(/GCS - Eye:\s*(.*?);\s*Verbal:\s*(.*?);\s*Motor:\s*(.*?);\s*Score:\s*(\d+)\/15;\s*Result:\s*(.*)$/i);
            if (!m) return;

            const eyeLabel = m[1].trim();
            const verbalLabel = m[2].trim();
            const motorLabel = m[3].trim();
            const result = m[5].trim();

            const eye = this.eyeOptions.find((o) => o.label === eyeLabel);
            const verbal = this.verbalOptions.find((o) => o.label === verbalLabel);
            const motor = this.motorOptions.find((o) => o.label === motorLabel);

            this.form.eye = eye ? eye.value : '';
            this.form.verbal = verbal ? verbal.value : '';
            this.form.motor = motor ? motor.value : '';
            this.form.result = result;
        },
        get selectedEyeLabel() {
            return this.eyeOptions.find((o) => o.value === this.form.eye)?.label ?? '—';
        },
        get selectedVerbalLabel() {
            return this.verbalOptions.find((o) => o.value === this.form.verbal)?.label ?? '—';
        },
        get selectedMotorLabel() {
            return this.motorOptions.find((o) => o.value === this.form.motor)?.label ?? '—';
        },
        get totalScore() {
            const e = Number(this.form.eye || 0);
            const v = Number(this.form.verbal || 0);
            const m = Number(this.form.motor || 0);
            return e + v + m;
        },
        get hasData() {
            return !!(this.form.eye || this.form.verbal || this.form.motor || this.form.result.trim());
        },
        get serialized() {
            if (this.form.eye && this.form.verbal && this.form.motor && this.form.result.trim()) {
                return `GCS - Eye: ${this.selectedEyeLabel}; Verbal: ${this.selectedVerbalLabel}; Motor: ${this.selectedMotorLabel}; Score: ${this.totalScore}/15; Result: ${this.form.result.trim()}`;
            }
            return this.initialRaw;
        },
        saveGcs() {
            this.error = '';
            if (!this.form.eye || !this.form.verbal || !this.form.motor || !this.form.result.trim()) {
                this.error = 'Eye, Verbal, Motor and Result are all required.';
                return;
            }
            this.initialRaw = '';
            this.showModal = false;
        },
    };
}

// ── Diagnosis modal helper ──────────────────────────────────────────────────
function diagnosisModal(icd11Library = []) {

    return {
        showModal: false,
        error: '',
        showIcd11Dropdown: false,
        icd11Library,
        entries: [],
        initialProvisional: '',
        initialFinal: '',
        form: {
            type: 'National Treatment Guideline',
            icd11: '',
            level1: '',
            level2: '',
            level3: '',
            certainty: '',
            attendance: '',
            comments: '',
        },
        level2OptionsByLevel1: {
            'Anaemia And Nutritional Conditions': [
                'Anaemia',
                'Malnutrition',
                'Vitamin deficiencies',
            ],
            'Cardiovascular Disorders': [
                'Angina pectoris',
                'Cardiac arrhythmias',
                'Cardiopulmonary resuscitation and advanced cardiac life support',
                'Congestive heart failure',
                'Hypertension',
                'Infective endocarditis',
                'Myocardial infarction',
                'Pulmonery oedema',
                'Rheumatic fever',
            ],
            'Conditions Of The Ear, Nose And Oropharynx': [
                'Ear conditions',
                'Nasal diseases',
                'oral disease',
                'Pharyngeal diseases',
            ],
            'Dermatological Conditions': [
                'bacterial infections',
                'Fungal Infection',
                'Parasitic infections',
                'viral skin infections',
            ],
            'Disorders Of The Renal System': [
                'GLOMERULAR DISORDERS',
                'Catheter-related bloodstream infections (CRBSI)',
                'hypertension',
                'Metabolic disorders',
                'Renal and pancreatic transplant',
            ],
            'Urology disorders': [
                'Renal and pancreatic transplant',
            ],
            'Eye Disease': [
                'Glaucoma',
                'ocular emergencies',
                'Optics & refraction (Refractive errors and low vision)',
                'strabismus (squint)',
                'systemic eye disease and the eye',
                'the red eye',
                'Trachoma',
            ],
            'Gastro-intestinal conditions': [
                'Abdominal Pain',
                'Cholera',
                'Diarrhoea',
                'Dysentery',
                'Epilepsy',
                'Febrile Convulsions',
                'Giardiasis',
                'Helminth Infestation',
                'Mental health and psychiatric illnesses',
                'Mood disorders',
                'Pyschotic Disorder',
                'Rabies',
            ],
            'Infections': [
                'Anthrax',
                'Malaria',
                'Meningitis',
                'Sexually transmitted infections',
                'The plague',
                'Tuberculosis',
                'Cholera',
            ],
            'Malignancies': [
                'Anal cancer',
                'Astrocytomas',
                'Carcinoma of the Breast',
                'Cervical cancer',
                'Colorectal carcinoma',
                'endometrial cancer',
                'Gastric cancer',
                'leukaemias',
                'Lung cancer',
                'lymphomas',
                'Nasopharyngeal carcinoma',
                'non-melanoma skin cancer',
                'Oesophageal Cacinoma',
                'Other head and neck cancer',
                'Ovarian cancer',
                'Paediatric cancer',
                'pancreatic cancer',
                'penile cancer',
                'prostate cancer',
                'testicular cancer',
                'thyroid cancer',
                'vulval cancer',
            ],
            'Orthopedic conditions': [
                'Renal and pancreatic transplant',
            ],
            'Obstetric & Gynaecological conditions': [
                'Abortion',
                'Antenatal care',
                'Antepartum haemorrhage (APH)',
                'Lower obstructive airway diseases',
                'medical abortion (termination of pregnancy)',
                'Medical disease in pregnancy',
                'Menstrual disorders',
                'Normal labour',
                'Postpartum haemorrhage (PPH)',
                'Pre-eclapsia and eclampsia',
                'respiratory tract infections',
                'The plague',
                'Unconscious obstetric patient',
            ],
            'Poisoning': [
                'Management of a poisned patient',
                'treatment of specific common poisoning',
            ],
            'Surgical conditions': [
                'Acute mumps orchitis',
                'Bites',
                'Hydrocele',
                'injuries',
                'Strangulated hernia',
                'Testicular torsion',
                'testicular tumours',
                'varicocele',
            ],
            'Haematological conditions': [
                'Renal and pancreatic transplant',
            ],
            'Dental conditions': [
                'Facial fractures',
                'Hard tissue conditions',
                'Soft tissue conditions',
                'Tumors-benign',
                'Tumors-malignant',
            ],
            'Mental health and psychiatric disorders': [],
            'Endocrine disorders': [],
        },
        level3OptionsByLevel2: {
            // Anaemia And Nutritional Conditions
            'Anaemia': ['Iron deficiency anaemia', 'Megaloblastic anaemia', 'Normocytic anaemia', 'Aplastic anaemia', 'Haemolytic anaemia', 'Sickle cell anaemia'],
            'Malnutrition': ['Kwashiorkor', 'Marasmus', 'Marasmic kwashiorkor', 'Wasting', 'Stunting', 'Obesity'],
            'Vitamin deficiencies': ['Vitamin A deficiency', 'Vitamin B1 (Thiamine) deficiency', 'Vitamin B12 deficiency', 'Vitamin C deficiency (Scurvy)', 'Vitamin D deficiency (Rickets)'],
            // Cardiovascular Disorders
            'Angina pectoris': ['Stable angina', 'Unstable angina'],
            'Cardiac arrhythmias': ['Atrial fibrillation', 'Atrial flutter', 'Supraventricular tachycardia', 'Ventricular tachycardia', 'Ventricular fibrillation', 'Heart block', 'Sinus bradycardia'],
            'Cardiopulmonary resuscitation and advanced cardiac life support': ['Basic life support', 'Advanced cardiac life support', 'Post-resuscitation care'],
            'Congestive heart failure': ['Left ventricular failure', 'Right ventricular failure', 'Biventricular failure'],
            'Hypertension': ['Mild hypertension', 'Moderate hypertension', 'Severe hypertension', 'Hypertensive emergency', 'Hypertensive urgency'],
            'Infective endocarditis': ['Native valve endocarditis', 'Prosthetic valve endocarditis'],
            'Myocardial infarction': ['ST-elevation myocardial infarction (STEMI)', 'Non-ST-elevation myocardial infarction (NSTEMI)'],
            'Pulmonery oedema': ['Acute pulmonary oedema'],
            'Rheumatic fever': ['Acute rheumatic fever', 'Rheumatic heart disease'],
            // Conditions Of The Ear, Nose And Oropharynx
            'Ear conditions': ['Acute otitis media', 'Chronic suppurative otitis media', 'Otitis externa', 'Serous otitis media', 'Hearing loss'],
            'Nasal diseases': ['Allergic rhinitis', 'Epistaxis', 'Acute sinusitis', 'Chronic sinusitis', 'Nasal polyps'],
            'oral disease': ['Gingivitis', 'Periodontitis', 'Oral candidiasis', 'Aphthous ulcers', 'Dental abscess'],
            'Pharyngeal diseases': ['Acute pharyngitis', 'Tonsillitis', 'Peritonsillar abscess', 'Epiglottitis'],
            // Dermatological Conditions
            'bacterial infections': ['Impetigo', 'Cellulitis', 'Erysipelas', 'Folliculitis', 'Boil (furuncle)', 'Carbuncle', 'Leprosy'],
            'Fungal Infection': ['Tinea capitis', 'Tinea corporis (ringworm)', 'Tinea pedis (athlete\'s foot)', 'Pityriasis versicolor', 'Candidiasis'],
            'Parasitic infections': ['Scabies', 'Pediculosis (lice)', 'Cutaneous larva migrans'],
            'viral skin infections': ['Herpes zoster (shingles)', 'Herpes simplex', 'Chickenpox', 'Warts', 'Molluscum contagiosum'],
            // Disorders Of The Renal System
            'GLOMERULAR DISORDERS': ['Acute glomerulonephritis', 'Nephrotic syndrome', 'Chronic kidney disease'],
            'Catheter-related bloodstream infections (CRBSI)': ['Prevention of CRBSI', 'Treatment of CRBSI'],
            'hypertension': ['Renovascular hypertension', 'Renal hypertension'],
            'Metabolic disorders': ['Renal calculi (kidney stones)', 'Gout', 'Hyperuricaemia'],
            'Renal and pancreatic transplant': ['Pre-transplant management', 'Post-transplant care', 'Rejection management'],
            // Eye Disease
            'Glaucoma': ['Primary open-angle glaucoma', 'Acute angle-closure glaucoma', 'Secondary glaucoma', 'Congenital glaucoma'],
            'ocular emergencies': ['Chemical eye injury', 'Penetrating eye injury', 'Retinal detachment', 'Central retinal artery occlusion'],
            'Optics & refraction (Refractive errors and low vision)': ['Myopia', 'Hyperopia', 'Astigmatism', 'Presbyopia', 'Low vision'],
            'strabismus (squint)': ['Esotropia', 'Exotropia', 'Mixed strabismus'],
            'systemic eye disease and the eye': ['Diabetic retinopathy', 'Hypertensive retinopathy', 'HIV-related eye disease', 'Thyroid eye disease'],
            'the red eye': ['Conjunctivitis', 'Keratitis', 'Anterior uveitis', 'Episcleritis'],
            'Trachoma': ['Active trachoma', 'Cicatricial trachoma', 'Trachomatous trichiasis'],
            // Gastro-intestinal conditions
            'Abdominal Pain': ['Acute appendicitis', 'Peptic ulcer disease', 'Irritable bowel syndrome', 'Intestinal obstruction', 'Cholecystitis', 'Pancreatitis'],
            'Cholera': ['Cholera (severe dehydration)', 'Cholera (some dehydration)', 'Cholera (no dehydration)'],
            'Diarrhoea': ['Acute watery diarrhoea', 'Persistent diarrhoea', 'Chronic diarrhoea'],
            'Dysentery': ['Amoebic dysentery', 'Bacillary dysentery (Shigellosis)'],
            'Epilepsy': ['Generalised tonic-clonic seizures', 'Absence seizures', 'Partial seizures', 'Status epilepticus'],
            'Febrile Convulsions': ['Simple febrile convulsions', 'Complex febrile convulsions'],
            'Giardiasis': ['Giardia intestinalis infection'],
            'Helminth Infestation': ['Ascariasis', 'Hookworm infection', 'Tapeworm (Taeniasis)', 'Trichuriasis', 'Strongyloides'],
            'Mental health and psychiatric illnesses': ['Major depressive disorder', 'Anxiety disorder', 'Schizophrenia', 'Bipolar disorder'],
            'Mood disorders': ['Major depressive disorder', 'Persistent depressive disorder (dysthymia)', 'Bipolar I disorder', 'Bipolar II disorder'],
            'Pyschotic Disorder': ['Schizophrenia', 'Schizophreniform disorder', 'Brief psychotic disorder', 'Schizoaffective disorder'],
            'Rabies': ['Post-exposure prophylaxis', 'Established rabies'],
            // Infections
            'Anthrax': ['Cutaneous anthrax', 'Inhalation anthrax', 'Gastrointestinal anthrax'],
            'Malaria': ['Uncomplicated malaria', 'Severe malaria', 'Malaria in pregnancy', 'Congenital malaria'],
            'Meningitis': ['Bacterial meningitis', 'Viral meningitis', 'Tuberculous meningitis', 'Cryptococcal meningitis'],
            'Sexually transmitted infections': ['Gonorrhoea', 'Syphilis', 'Chlamydia', 'Trichomoniasis', 'Genital herpes', 'Genital warts', 'Chancroid'],
            'The plague': ['Bubonic plague', 'Pneumonic plague', 'Septicaemic plague'],
            'Tuberculosis': ['Pulmonary tuberculosis', 'Extrapulmonary tuberculosis', 'TB/HIV co-infection', 'Drug-resistant TB (MDR-TB)'],
            // Malignancies
            'Anal cancer': ['Squamous cell carcinoma of the anus'],
            'Astrocytomas': ['Low-grade astrocytoma', 'High-grade astrocytoma (glioblastoma)'],
            'Carcinoma of the Breast': ['Early breast cancer', 'Locally advanced breast cancer', 'Metastatic breast cancer'],
            'Cervical cancer': ['Cervical intraepithelial neoplasia (CIN)', 'Stage I cervical cancer', 'Stage II cervical cancer', 'Stage III–IV cervical cancer'],
            'Colorectal carcinoma': ['Stage I colorectal cancer', 'Stage II colorectal cancer', 'Stage III colorectal cancer', 'Stage IV colorectal cancer'],
            'endometrial cancer': ['Endometrial adenocarcinoma'],
            'Gastric cancer': ['Early gastric cancer', 'Advanced gastric cancer'],
            'leukaemias': ['Acute lymphoblastic leukaemia (ALL)', 'Acute myeloid leukaemia (AML)', 'Chronic lymphocytic leukaemia (CLL)', 'Chronic myeloid leukaemia (CML)'],
            'Lung cancer': ['Small cell lung cancer', 'Non-small cell lung cancer'],
            'lymphomas': ['Hodgkin lymphoma', 'Non-Hodgkin lymphoma'],
            'Nasopharyngeal carcinoma': ['Nasopharyngeal carcinoma'],
            'non-melanoma skin cancer': ['Basal cell carcinoma', 'Squamous cell carcinoma of the skin'],
            'Oesophageal Cacinoma': ['Squamous cell carcinoma of the oesophagus', 'Adenocarcinoma of the oesophagus'],
            'Other head and neck cancer': ['Laryngeal cancer', 'Oral cavity cancer', 'Salivary gland cancer'],
            'Ovarian cancer': ['Epithelial ovarian cancer'],
            'Paediatric cancer': ['Wilms\' tumour (nephroblastoma)', 'Retinoblastoma', 'Neuroblastoma', 'Medulloblastoma'],
            'pancreatic cancer': ['Adenocarcinoma of the pancreas'],
            'penile cancer': ['Squamous cell carcinoma of the penis'],
            'prostate cancer': ['Localised prostate cancer', 'Locally advanced prostate cancer', 'Metastatic prostate cancer'],
            'testicular cancer': ['Seminoma', 'Non-seminomatous germ cell tumour'],
            'thyroid cancer': ['Papillary thyroid carcinoma', 'Follicular thyroid carcinoma', 'Medullary thyroid carcinoma'],
            'vulval cancer': ['Squamous cell carcinoma of the vulva'],
            // Obstetric & Gynaecological conditions
            'Abortion': ['Threatened abortion', 'Inevitable abortion', 'Incomplete abortion', 'Septic abortion', 'Missed abortion'],
            'Antenatal care': ['Routine antenatal care', 'High-risk pregnancy monitoring'],
            'Antepartum haemorrhage (APH)': ['Placenta praevia', 'Placental abruption'],
            'Lower obstructive airway diseases': ['Asthma in pregnancy', 'Chronic obstructive pulmonary disease'],
            'medical abortion (termination of pregnancy)': ['First trimester medical abortion', 'Second trimester medical abortion'],
            'Medical disease in pregnancy': ['Hypertension in pregnancy', 'Diabetes in pregnancy', 'Anaemia in pregnancy', 'Malaria in pregnancy', 'HIV in pregnancy'],
            'Menstrual disorders': ['Dysmenorrhoea', 'Menorrhagia', 'Amenorrhoea', 'Polycystic ovary syndrome (PCOS)'],
            'Normal labour': ['First stage of labour', 'Second stage of labour', 'Third stage of labour'],
            'Postpartum haemorrhage (PPH)': ['Primary PPH', 'Secondary PPH'],
            'Pre-eclapsia and eclampsia': ['Pre-eclampsia', 'Severe pre-eclampsia', 'Eclampsia', 'HELLP syndrome'],
            'respiratory tract infections': ['Upper respiratory tract infection', 'Lower respiratory tract infection', 'Pneumonia'],
            'Unconscious obstetric patient': ['Eclampsia', 'Severe sepsis in pregnancy', 'Other obstetric emergency'],
            // Poisoning
            'Management of a poisned patient': ['General approach to poisoning', 'Gastric decontamination', 'Antidote therapy'],
            'treatment of specific common poisoning': ['Organophosphate poisoning', 'Paracetamol overdose', 'Carbon monoxide poisoning', 'Snake bite', 'Alcohol intoxication', 'Rodenticide poisoning'],
            // Surgical conditions
            'Acute mumps orchitis': ['Orchitis management'],
            'Bites': ['Dog bite', 'Snake bite', 'Insect bite', 'Human bite'],
            'Hydrocele': ['Congenital hydrocele', 'Secondary hydrocele'],
            'injuries': ['Head injury', 'Chest injury', 'Abdominal injury', 'Fractures', 'Burns', 'Soft tissue injuries'],
            'Strangulated hernia': ['Inguinal hernia', 'Femoral hernia', 'Umbilical hernia'],
            'Testicular torsion': ['Testicular torsion'],
            'testicular tumours': ['Seminoma', 'Teratoma'],
            'varicocele': ['Varicocele'],
            // Dental conditions
            'Facial fractures': ['Mandibular fracture', 'Maxillary fracture', 'Zygomatic fracture'],
            'Hard tissue conditions': ['Dental caries', 'Dental abscess', 'Periodontitis', 'Tooth erosion'],
            'Soft tissue conditions': ['Aphthous ulcers', 'Ranula', 'Epulis', 'Sialadenitis', "Ludwig's angina"],
            'Tumors-benign': ['Ameloblastoma', 'Fibroma', 'Papilloma', 'Dentigerous cyst'],
            'Tumors-malignant': ['Oral squamous cell carcinoma', 'Mucoepidermoid carcinoma'],
        },
        get level2Options() {
            if (!this.form.level1) {
                return [];
            }
            return this.level2OptionsByLevel1[this.form.level1] || [];
        },
        get level3Options() {
            if (!this.form.level2) {
                return [];
            }
            return this.level3OptionsByLevel2[this.form.level2] || [];
        },
        onLevel1Change() {
            this.form.level2 = '';
            this.form.level3 = '';
        },
        onLevel2Change() {
            this.form.level3 = '';
        },
        initFromText(initialProvisional, initialFinal) {
            this.initialProvisional = typeof initialProvisional === 'string' ? initialProvisional : '';
            this.initialFinal = typeof initialFinal === 'string' ? initialFinal : '';
            try {
                const parsed = JSON.parse(this.initialProvisional);
                if (Array.isArray(parsed)) {
                    this.entries = parsed;
                }
            } catch (e) {
                // keep legacy values untouched in hidden fields if not JSON
            }
        },
        get filteredIcd11() {
            const query = this.form.icd11.trim().toLowerCase();

            if (!query) {
                return [];
            }

            return this.icd11Library
                .filter((item) => item.toLowerCase().includes(query))
                .slice(0, 300);
        },
        selectIcd11(item) {
            this.form.icd11 = item;
            this.showIcd11Dropdown = false;
        },
        addEntry() {
            this.error = '';

            if (this.form.type === 'National Treatment Guideline') {
                if (!this.form.level1 || !this.form.level2 || !this.form.level3) {
                    this.error = 'NTG Levels 1, 2 and 3 are required.';
                    return;
                }
            }

            if (this.form.type === 'ICD 11' && !this.form.icd11.trim()) {
                this.error = 'ICD 11 is required.';
                return;
            }

            if (!this.form.certainty || !this.form.attendance) {
                this.error = 'Certainty and Attendance are required.';
                return;
            }

            const path = this.form.type === 'ICD 11'
                ? this.form.icd11.trim()
                : `${this.form.level1} > ${this.form.level2} > ${this.form.level3}`;

            this.entries.push({
                type: this.form.type,
                icd11: this.form.icd11,
                level1: this.form.level1,
                level2: this.form.level2,
                level3: this.form.level3,
                path: path,
                certainty: this.form.certainty,
                attendance: this.form.attendance,
                comments: this.form.comments.trim(),
            });

            this.form.icd11 = '';
            this.form.level1 = '';
            this.form.level2 = '';
            this.form.level3 = '';
            this.form.certainty = '';
            this.form.attendance = '';
            this.form.comments = '';
            this.showIcd11Dropdown = false;
            this.initialProvisional = '';
            this.initialFinal = '';
        },
        removeEntry(idx) {
            this.entries.splice(idx, 1);
        },
        get serializedProvisional() {
            if (this.entries.length > 0) {
                return JSON.stringify(this.entries);
            }
            return this.initialProvisional;
        },
        get serializedFinal() {
            if (this.entries.length > 0) {
                const last = this.entries[this.entries.length - 1];
                const note = last.comments ? `; ${last.comments}` : '';
                return `${last.path} (${last.certainty}, ${last.attendance})${note}`;
            }
            return this.initialFinal;
        },
    };
}

// ── System Examination modal helper ────────────────────────────────────────
function systemExaminationModal() {
    return {
        showModal: false,
        error: '',
        initialRaw: '',
        entries: [],
        form: {
            system: '',
            notes: '',
        },
        initFromText(initialText) {
            this.initialRaw = typeof initialText === 'string' ? initialText : '';
            if (!this.initialRaw) return;
            try {
                const parsed = JSON.parse(this.initialRaw);
                if (Array.isArray(parsed)) {
                    this.entries = parsed
                        .filter((e) => e && typeof e === 'object' && e.system && e.notes)
                        .map((e) => ({ system: String(e.system), notes: String(e.notes) }));
                }
            } catch (e) {
                // keep legacy free-text in initialRaw if not JSON
            }
        },
        get serialized() {
            if (this.entries.length > 0) {
                return JSON.stringify(this.entries);
            }
            return this.initialRaw;
        },
        addEntry() {
            this.error = '';
            if (!this.form.system || !this.form.notes.trim()) {
                this.error = 'System and Notes are required.';
                return;
            }
            this.entries.push({
                system: this.form.system,
                notes: this.form.notes.trim(),
            });
            this.initialRaw = '';
            this.form.system = '';
            this.form.notes = '';
        },
        removeEntry(idx) {
            this.entries.splice(idx, 1);
        },
    };
}

// ── Past Medical History modal helper ──────────────────────────────────────
function pastMedicalHistoryModal() {
    return {
        showModal: false,
        error: '',
        initialRaw: '',
        form: {
            drug_history: '',
            admission_history: '',
            surgical_history: '',
        },
        initFromText(initialText) {
            this.initialRaw = typeof initialText === 'string' ? initialText : '';
            if (!this.initialRaw) return;

            try {
                const parsed = JSON.parse(this.initialRaw);
                if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                    this.form.drug_history = String(parsed.drug_history || '');
                    this.form.admission_history = String(parsed.admission_history || '');
                    this.form.surgical_history = String(parsed.surgical_history || '');
                }
            } catch (e) {
                // Keep legacy free text unchanged unless user saves modal.
            }
        },
        get hasData() {
            return !!(
                this.form.drug_history.trim() ||
                this.form.admission_history.trim() ||
                this.form.surgical_history.trim()
            );
        },
        get serialized() {
            if (this.hasData) {
                return JSON.stringify({
                    drug_history: this.form.drug_history.trim(),
                    admission_history: this.form.admission_history.trim(),
                    surgical_history: this.form.surgical_history.trim(),
                });
            }

            return this.initialRaw;
        },
        saveRecord() {
            this.error = '';

            if (
                !this.form.drug_history.trim() ||
                !this.form.admission_history.trim() ||
                !this.form.surgical_history.trim()
            ) {
                this.error = 'Drug History, Admission History and Surgical History are required.';
                return;
            }

            this.initialRaw = '';
            this.showModal = false;
        },
    };
}

// ── Chronic / Non-Chronic Conditions modal helper ────────────────────────────
function chronicConditionsModal(icd11Library = []) {
    const level2OptionsByLevel1 = {
        'Anaemia And Nutritional Conditions': ['Anaemia', 'Malnutrition', 'Vitamin deficiencies'],
        'Cardiovascular Disorders': ['Angina pectoris', 'Cardiac arrhythmias', 'Congestive heart failure', 'Hypertension', 'Infective endocarditis', 'Myocardial infarction', 'Pulmonery oedema', 'Rheumatic fever'],
        'Conditions Of The Ear, Nose And Oropharynx': ['Ear conditions', 'Nasal diseases', 'oral disease', 'Pharyngeal diseases'],
        'Dermatological Conditions': ['bacterial infections', 'Fungal Infection', 'Parasitic infections', 'viral skin infections'],
        'Disorders Of The Renal System': ['GLOMERULAR DISORDERS', 'Catheter-related bloodstream infections (CRBSI)', 'hypertension', 'Metabolic disorders', 'Renal and pancreatic transplant'],
        'Urology disorders': ['Renal and pancreatic transplant'],
        'Eye Disease': ['Glaucoma', 'ocular emergencies', 'Optics & refraction (Refractive errors and low vision)', 'strabismus (squint)', 'systemic eye disease and the eye', 'the red eye', 'Trachoma'],
        'Gastro-intestinal conditions': ['Abdominal Pain', 'Cholera', 'Diarrhoea', 'Dysentery', 'Epilepsy', 'Febrile Convulsions', 'Giardiasis', 'Helminth Infestation', 'Mental health and psychiatric illnesses', 'Mood disorders', 'Pyschotic Disorder', 'Rabies'],
        'Infections': ['Anthrax', 'Malaria', 'Meningitis', 'Sexually transmitted infections', 'The plague', 'Tuberculosis', 'Cholera'],
        'Malignancies': ['Anal cancer', 'Astrocytomas', 'Carcinoma of the Breast', 'Cervical cancer', 'Colorectal carcinoma', 'endometrial cancer', 'Gastric cancer', 'leukaemias', 'Lung cancer', 'lymphomas', 'Nasopharyngeal carcinoma', 'Oesophageal Cacinoma', 'Ovarian cancer', 'Paediatric cancer', 'pancreatic cancer', 'prostate cancer', 'testicular cancer', 'thyroid cancer'],
        'Orthopedic conditions': ['Renal and pancreatic transplant'],
        'Obstetric & Gynaecological conditions': ['Abortion', 'Antenatal care', 'Antepartum haemorrhage (APH)', 'Medical disease in pregnancy', 'Menstrual disorders', 'Normal labour', 'Postpartum haemorrhage (PPH)', 'Pre-eclapsia and eclampsia'],
        'Poisoning': ['Management of a poisned patient', 'treatment of specific common poisoning'],
        'Surgical conditions': ['Acute mumps orchitis', 'Bites', 'Hydrocele', 'injuries', 'Strangulated hernia', 'Testicular torsion', 'testicular tumours', 'varicocele'],
        'Haematological conditions': ['Renal and pancreatic transplant'],
        'Dental conditions': ['Facial fractures', 'Hard tissue conditions', 'Soft tissue conditions', 'Tumors-benign', 'Tumors-malignant'],
        'Mental health and psychiatric disorders': [],
        'Endocrine disorders': [],
    };

    const level3OptionsByLevel2 = {
        // Anaemia And Nutritional Conditions
        'Anaemia': ['Iron deficiency anaemia', 'Megaloblastic anaemia', 'Normocytic anaemia', 'Aplastic anaemia', 'Haemolytic anaemia', 'Sickle cell anaemia'],
        'Malnutrition': ['Kwashiorkor', 'Marasmus', 'Marasmic kwashiorkor', 'Wasting', 'Stunting', 'Obesity'],
        'Vitamin deficiencies': ['Vitamin A deficiency', 'Vitamin B1 (Thiamine) deficiency', 'Vitamin B12 deficiency', 'Vitamin C deficiency (Scurvy)', 'Vitamin D deficiency (Rickets)'],
        // Cardiovascular Disorders
        'Angina pectoris': ['Stable angina', 'Unstable angina'],
        'Cardiac arrhythmias': ['Atrial fibrillation', 'Atrial flutter', 'Supraventricular tachycardia', 'Ventricular tachycardia', 'Ventricular fibrillation', 'Heart block', 'Sinus bradycardia'],
        'Cardiopulmonary resuscitation and advanced cardiac life support': ['Basic life support', 'Advanced cardiac life support', 'Post-resuscitation care'],
        'Congestive heart failure': ['Left ventricular failure', 'Right ventricular failure', 'Biventricular failure'],
        'Hypertension': ['Mild hypertension', 'Moderate hypertension', 'Severe hypertension', 'Hypertensive emergency', 'Hypertensive urgency'],
        'Infective endocarditis': ['Native valve endocarditis', 'Prosthetic valve endocarditis'],
        'Myocardial infarction': ['ST-elevation myocardial infarction (STEMI)', 'Non-ST-elevation myocardial infarction (NSTEMI)'],
        'Pulmonery oedema': ['Acute pulmonary oedema'],
        'Rheumatic fever': ['Acute rheumatic fever', 'Rheumatic heart disease'],
        // Conditions Of The Ear, Nose And Oropharynx
        'Ear conditions': ['Acute otitis media', 'Chronic suppurative otitis media', 'Otitis externa', 'Serous otitis media', 'Hearing loss'],
        'Nasal diseases': ['Allergic rhinitis', 'Epistaxis', 'Acute sinusitis', 'Chronic sinusitis', 'Nasal polyps'],
        'oral disease': ['Gingivitis', 'Periodontitis', 'Oral candidiasis', 'Aphthous ulcers', 'Dental abscess'],
        'Pharyngeal diseases': ['Acute pharyngitis', 'Tonsillitis', 'Peritonsillar abscess', 'Epiglottitis'],
        // Dermatological Conditions
        'bacterial infections': ['Impetigo', 'Cellulitis', 'Erysipelas', 'Folliculitis', 'Boil (furuncle)', 'Carbuncle', 'Leprosy'],
        'Fungal Infection': ['Tinea capitis', 'Tinea corporis (ringworm)', 'Tinea pedis (athlete\'s foot)', 'Pityriasis versicolor', 'Candidiasis'],
        'Parasitic infections': ['Scabies', 'Pediculosis (lice)', 'Cutaneous larva migrans'],
        'viral skin infections': ['Herpes zoster (shingles)', 'Herpes simplex', 'Chickenpox', 'Warts', 'Molluscum contagiosum'],
        // Disorders Of The Renal System
        'GLOMERULAR DISORDERS': ['Acute glomerulonephritis', 'Nephrotic syndrome', 'Chronic kidney disease'],
        'Catheter-related bloodstream infections (CRBSI)': ['Prevention of CRBSI', 'Treatment of CRBSI'],
        'hypertension': ['Renovascular hypertension', 'Renal hypertension'],
        'Metabolic disorders': ['Renal calculi (kidney stones)', 'Gout', 'Hyperuricaemia'],
        'Renal and pancreatic transplant': ['Pre-transplant management', 'Post-transplant care', 'Rejection management'],
        // Eye Disease
        'Glaucoma': ['Primary open-angle glaucoma', 'Acute angle-closure glaucoma', 'Secondary glaucoma', 'Congenital glaucoma'],
        'ocular emergencies': ['Chemical eye injury', 'Penetrating eye injury', 'Retinal detachment', 'Central retinal artery occlusion'],
        'Optics & refraction (Refractive errors and low vision)': ['Myopia', 'Hyperopia', 'Astigmatism', 'Presbyopia', 'Low vision'],
        'strabismus (squint)': ['Esotropia', 'Exotropia', 'Mixed strabismus'],
        'systemic eye disease and the eye': ['Diabetic retinopathy', 'Hypertensive retinopathy', 'HIV-related eye disease', 'Thyroid eye disease'],
        'the red eye': ['Conjunctivitis', 'Keratitis', 'Anterior uveitis', 'Episcleritis'],
        'Trachoma': ['Active trachoma', 'Cicatricial trachoma', 'Trachomatous trichiasis'],
        // Gastro-intestinal conditions
        'Abdominal Pain': ['Acute appendicitis', 'Peptic ulcer disease', 'Irritable bowel syndrome', 'Intestinal obstruction', 'Cholecystitis', 'Pancreatitis'],
        'Cholera': ['Cholera (severe dehydration)', 'Cholera (some dehydration)', 'Cholera (no dehydration)'],
        'Diarrhoea': ['Acute watery diarrhoea', 'Persistent diarrhoea', 'Chronic diarrhoea'],
        'Dysentery': ['Amoebic dysentery', 'Bacillary dysentery (Shigellosis)'],
        'Epilepsy': ['Generalised tonic-clonic seizures', 'Absence seizures', 'Partial seizures', 'Status epilepticus'],
        'Febrile Convulsions': ['Simple febrile convulsions', 'Complex febrile convulsions'],
        'Giardiasis': ['Giardia intestinalis infection'],
        'Helminth Infestation': ['Ascariasis', 'Hookworm infection', 'Tapeworm (Taeniasis)', 'Trichuriasis', 'Strongyloides'],
        'Mental health and psychiatric illnesses': ['Major depressive disorder', 'Anxiety disorder', 'Schizophrenia', 'Bipolar disorder'],
        'Mood disorders': ['Major depressive disorder', 'Persistent depressive disorder (dysthymia)', 'Bipolar I disorder', 'Bipolar II disorder'],
        'Pyschotic Disorder': ['Schizophrenia', 'Schizophreniform disorder', 'Brief psychotic disorder', 'Schizoaffective disorder'],
        'Rabies': ['Post-exposure prophylaxis', 'Established rabies'],
        // Infections
        'Anthrax': ['Cutaneous anthrax', 'Inhalation anthrax', 'Gastrointestinal anthrax'],
        'Malaria': ['Uncomplicated malaria', 'Severe malaria', 'Malaria in pregnancy', 'Congenital malaria'],
        'Meningitis': ['Bacterial meningitis', 'Viral meningitis', 'Tuberculous meningitis', 'Cryptococcal meningitis'],
        'Sexually transmitted infections': ['Gonorrhoea', 'Syphilis', 'Chlamydia', 'Trichomoniasis', 'Genital herpes', 'Genital warts', 'Chancroid'],
        'The plague': ['Bubonic plague', 'Pneumonic plague', 'Septicaemic plague'],
        'Tuberculosis': ['Pulmonary tuberculosis', 'Extrapulmonary tuberculosis', 'TB/HIV co-infection', 'Drug-resistant TB (MDR-TB)'],
        // Malignancies
        'Anal cancer': ['Squamous cell carcinoma of the anus'],
        'Astrocytomas': ['Low-grade astrocytoma', 'High-grade astrocytoma (glioblastoma)'],
        'Carcinoma of the Breast': ['Early breast cancer', 'Locally advanced breast cancer', 'Metastatic breast cancer'],
        'Cervical cancer': ['Cervical intraepithelial neoplasia (CIN)', 'Stage I cervical cancer', 'Stage II cervical cancer', 'Stage III–IV cervical cancer'],
        'Colorectal carcinoma': ['Stage I colorectal cancer', 'Stage II colorectal cancer', 'Stage III colorectal cancer', 'Stage IV colorectal cancer'],
        'endometrial cancer': ['Endometrial adenocarcinoma'],
        'Gastric cancer': ['Early gastric cancer', 'Advanced gastric cancer'],
        'leukaemias': ['Acute lymphoblastic leukaemia (ALL)', 'Acute myeloid leukaemia (AML)', 'Chronic lymphocytic leukaemia (CLL)', 'Chronic myeloid leukaemia (CML)'],
        'Lung cancer': ['Small cell lung cancer', 'Non-small cell lung cancer'],
        'lymphomas': ['Hodgkin lymphoma', 'Non-Hodgkin lymphoma'],
        'Nasopharyngeal carcinoma': ['Nasopharyngeal carcinoma'],
        'non-melanoma skin cancer': ['Basal cell carcinoma', 'Squamous cell carcinoma of the skin'],
        'Oesophageal Cacinoma': ['Squamous cell carcinoma of the oesophagus', 'Adenocarcinoma of the oesophagus'],
        'Other head and neck cancer': ['Laryngeal cancer', 'Oral cavity cancer', 'Salivary gland cancer'],
        'Ovarian cancer': ['Epithelial ovarian cancer'],
        'Paediatric cancer': ['Wilms\' tumour (nephroblastoma)', 'Retinoblastoma', 'Neuroblastoma', 'Medulloblastoma'],
        'pancreatic cancer': ['Adenocarcinoma of the pancreas'],
        'penile cancer': ['Squamous cell carcinoma of the penis'],
        'prostate cancer': ['Localised prostate cancer', 'Locally advanced prostate cancer', 'Metastatic prostate cancer'],
        'testicular cancer': ['Seminoma', 'Non-seminomatous germ cell tumour'],
        'thyroid cancer': ['Papillary thyroid carcinoma', 'Follicular thyroid carcinoma', 'Medullary thyroid carcinoma'],
        'vulval cancer': ['Squamous cell carcinoma of the vulva'],
        // Obstetric & Gynaecological conditions
        'Abortion': ['Threatened abortion', 'Inevitable abortion', 'Incomplete abortion', 'Septic abortion', 'Missed abortion'],
        'Antenatal care': ['Routine antenatal care', 'High-risk pregnancy monitoring'],
        'Antepartum haemorrhage (APH)': ['Placenta praevia', 'Placental abruption'],
        'Medical disease in pregnancy': ['Hypertension in pregnancy', 'Diabetes in pregnancy', 'Anaemia in pregnancy', 'Malaria in pregnancy', 'HIV in pregnancy'],
        'Menstrual disorders': ['Dysmenorrhoea', 'Menorrhagia', 'Amenorrhoea', 'Polycystic ovary syndrome (PCOS)'],
        'Normal labour': ['First stage of labour', 'Second stage of labour', 'Third stage of labour'],
        'Postpartum haemorrhage (PPH)': ['Primary PPH', 'Secondary PPH'],
        'Pre-eclapsia and eclampsia': ['Pre-eclampsia', 'Severe pre-eclampsia', 'Eclampsia', 'HELLP syndrome'],
        // Poisoning
        'Management of a poisned patient': ['General approach to poisoning', 'Gastric decontamination', 'Antidote therapy'],
        'treatment of specific common poisoning': ['Organophosphate poisoning', 'Paracetamol overdose', 'Carbon monoxide poisoning', 'Snake bite', 'Alcohol intoxication', 'Rodenticide poisoning'],
        // Surgical conditions
        'Acute mumps orchitis': ['Orchitis management'],
        'Bites': ['Dog bite', 'Snake bite', 'Insect bite', 'Human bite'],
        'Hydrocele': ['Congenital hydrocele', 'Secondary hydrocele'],
        'injuries': ['Head injury', 'Chest injury', 'Abdominal injury', 'Fractures', 'Burns', 'Soft tissue injuries'],
        'Strangulated hernia': ['Inguinal hernia', 'Femoral hernia', 'Umbilical hernia'],
        'Testicular torsion': ['Testicular torsion'],
        'testicular tumours': ['Seminoma', 'Teratoma'],
        'varicocele': ['Varicocele'],
        // Dental conditions
        'Facial fractures': ['Mandibular fracture', 'Maxillary fracture', 'Zygomatic fracture'],
        'Hard tissue conditions': ['Dental caries', 'Dental abscess', 'Periodontitis', 'Tooth erosion'],
        'Soft tissue conditions': ['Aphthous ulcers', 'Ranula', 'Epulis', 'Sialadenitis', "Ludwig's angina"],
        'Tumors-benign': ['Ameloblastoma', 'Fibroma', 'Papilloma', 'Dentigerous cyst'],
        'Tumors-malignant': ['Oral squamous cell carcinoma', 'Mucoepidermoid carcinoma'],
    };

    return {
        showModal: false,
        error: '',
        showIcd11Dropdown: false,
        icd11Library,
        entries: [],
        initialRaw: '',
        form: {
            type: 'National Treatment Guideline',
            level1: '',
            level2: '',
            level3: '',
            icd11: '',
            condition: '',
            date_diagnosed: '',
            still_ongoing: false,
            date_resolved: '',
            certainty: '',
            comments: '',
        },
        get level2Options() {
            return level2OptionsByLevel1[this.form.level1] || [];
        },
        get level3Options() {
            return level3OptionsByLevel2[this.form.level2] || [];
        },
        onLevel1Change() {
            this.form.level2 = '';
            this.form.level3 = '';
        },
        onLevel2Change() {
            this.form.level3 = '';
        },
        initFromText(initialText) {
            this.initialRaw = typeof initialText === 'string' ? initialText : '';
            if (!this.initialRaw) return;
            try {
                const parsed = JSON.parse(this.initialRaw);
                if (Array.isArray(parsed)) {
                    this.entries = parsed;
                    this.initialRaw = '';
                }
            } catch (e) {
                // Legacy plain text — kept in initialRaw for display
            }
        },
        get filteredIcd11() {
            const query = this.form.icd11.trim().toLowerCase();
            if (!query) return [];
            return this.icd11Library.filter(item => item.toLowerCase().includes(query)).slice(0, 300);
        },
        selectIcd11(item) {
            this.form.icd11 = item;
            this.showIcd11Dropdown = false;
        },
        addEntry() {
            this.error = '';
            if (this.form.type === 'National Treatment Guideline' && (!this.form.level1 || !this.form.level2 || !this.form.level3)) {
                this.error = 'NTG Levels 1, 2 and 3 are required.';
                return;
            }
            if (this.form.type === 'ICD 11' && !this.form.icd11.trim()) {
                this.error = 'ICD 11 field is required.';
                return;
            }
            if (!this.form.condition) {
                this.error = 'Condition is required.';
                return;
            }
            if (!this.form.date_diagnosed) {
                this.error = 'Date Diagnosed is required.';
                return;
            }
            if (!this.form.certainty) {
                this.error = 'Certainty is required.';
                return;
            }
            const path = this.form.type === 'ICD 11'
                ? this.form.icd11.trim()
                : [this.form.level1, this.form.level2, this.form.level3].join(' > ');
            this.entries.push({
                type: this.form.type,
                path,
                condition: this.form.condition,
                date_diagnosed: this.form.date_diagnosed,
                still_ongoing: this.form.still_ongoing,
                date_resolved: this.form.still_ongoing ? '' : this.form.date_resolved,
                certainty: this.form.certainty,
                comments: this.form.comments.trim(),
            });
            this.form.level1 = '';
            this.form.level2 = '';
            this.form.level3 = '';
            this.form.icd11 = '';
            this.form.condition = '';
            this.form.date_diagnosed = '';
            this.form.still_ongoing = false;
            this.form.date_resolved = '';
            this.form.certainty = '';
            this.form.comments = '';
        },
        removeEntry(idx) {
            this.entries.splice(idx, 1);
        },
        get serialized() {
            if (this.entries.length > 0) return JSON.stringify(this.entries);
            return this.initialRaw;
        },
    };
}

// ── Development History (Alpine component) ──────────────────────────────────
function developmentHistory() {
    const milestonesDef = [
        { key: 'social_smile',       name: 'Social Smile',                                    limits: '4 – 6 Weeks',   unit: 'Weeks' },
        { key: 'head_holding',       name: 'Head Holding',                                    limits: '1 – 3 Months',  unit: 'Months' },
        { key: 'turn_sound',         name: 'Turn Towards Origin of Sound',                    limits: '2 – 3 Months',  unit: 'Months' },
        { key: 'grasp_toy',          name: 'Extends Hand to Grasp a Toy',                     limits: '2 – 3 Months',  unit: 'Months' },
        { key: 'follow_eyes',        name: 'Follow objects with eyes',                        limits: '2 – 4 Months',  unit: 'Months' },
        { key: 'rolls_over',         name: 'Rolls over',                                      limits: '4 – 6 Months',  unit: 'Months' },
        { key: 'babbles',            name: 'Babbles',                                         limits: '4 – 6 Months',  unit: 'Months' },
        { key: 'objects_mouth',      name: 'Takes objects to mouth',                          limits: '4 – 6 Months',  unit: 'Months' },
        { key: 'sitting',            name: 'Sitting',                                         limits: '5 – 9 Months',  unit: 'Months' },
        { key: 'repeats_syllables',  name: 'Repeats syllables',                               limits: '6 – 9 Months',  unit: 'Months' },
        { key: 'move_objects_hands', name: 'Move objects from one hand to another',            limits: '6 – 9 Months',  unit: 'Months' },
        { key: 'peek_a_boo',         name: 'Plays peek-a-boo',                                limits: '6 – 9 Months',  unit: 'Months' },
        { key: 'responds_name',      name: 'Responds to own name',                            limits: '6 – 9 Months',  unit: 'Months' },
        { key: 'steps_support',      name: 'Takes steps with support',                        limits: '9 – 12 Months', unit: 'Months' },
        { key: 'picks_small',        name: 'Picks up small objects or string with two fingers',limits: '9 – 12 Months', unit: 'Months' },
        { key: 'imitates_gestures',  name: 'Imitates simple gestures',                        limits: '9 – 12 Months', unit: 'Months' },
        { key: 'points_words',       name: 'Points to objects and says 2 – 3 words',          limits: '9 – 12 Months', unit: 'Months' },
        { key: 'standing',           name: 'Standing',                                        limits: '7 – 13 Months', unit: 'Months' },
        { key: 'walking',            name: 'Walking',                                         limits: '12 – 18 Months',unit: 'Months' },
        { key: 'drinks_cup',         name: 'Drinks from cup',                                 limits: '12 – 18 Months',unit: 'Months' },
        { key: 'says_words',         name: 'Says 7 – 10 words',                               limits: '12 – 18 Months',unit: 'Months' },
        { key: 'points_body',        name: 'Points to body parts',                            limits: '12 – 18 Months',unit: 'Months' },
        { key: 'talking',            name: 'Talking',                                         limits: '9 – 24 Months', unit: 'Months' },
        { key: 'kicks_ball',         name: 'Kicks ball and starts to run',                    limits: '18 – 24 Months',unit: 'Months' },
        { key: 'points_picture',     name: 'Points at picture on request',                    limits: '18 – 24 Months',unit: 'Months' },
        { key: 'short_sentences',    name: 'Sings and uses short sentences',                  limits: '18 – 24 Months',unit: 'Months' },
        { key: 'builds_tower',       name: 'Builds tower with 3 blocks or boxes',             limits: '18 – 24 Months',unit: 'Months' },
        { key: 'jumps_runs',         name: 'Jumps and runs',                                  limits: '> 24 Months',   unit: 'Months' },
        { key: 'dresses_self',       name: 'Begins to dress and undress by itself',           limits: '> 24 Months',   unit: 'Months' },
        { key: 'groups_objects',     name: 'Groups similar objects',                          limits: '> 24 Months',   unit: 'Months' },
        { key: 'plays_children',     name: 'Plays with other children',                       limits: '> 24 Months',   unit: 'Months' },
        { key: 'says_name_story',    name: 'Says first name and tells short story',           limits: '> 24 Months',   unit: 'Months' },
    ];

    // Parse existing saved data
    let saved = [];
    try {
        const raw = @json(old('development_history', $s?->development_history ?? ''));
        if (typeof raw === 'string' && raw.startsWith('[')) {
            saved = JSON.parse(raw);
        } else if (Array.isArray(raw)) {
            saved = raw;
        }
    } catch(e) {}

    // Merge saved values into milestones
    const milestones = milestonesDef.map(m => {
        const s = saved.find(s => s.key === m.key);
        return { ...m, achieved: s ? s.achieved : '' };
    });

    return {
        milestones,
        showModal: false,
        get filledCount() {
            return this.milestones.filter(m => m.achieved).length;
        }
    };
}

// ── Immunization Manager (Alpine component) ─────────────────────────────────
function immunizationManager() {
    const vaccineTypes = [
        {
            name: 'Oral Polio Vaccine (OPV)',
            vaccines: ['OPV (Oral Polio Vaccine)'],
            doses: ['Birth Dose', 'Dose 1', 'Dose 2', 'Dose 3'],
        },
        {
            name: 'COVAX',
            vaccines: ['AstraZeneca (COVID Shield)', 'Pfizer-BioNTech (Comirnaty)', 'Johnson & Johnson (Janssen)', 'Sinopharm (BBIBP-CorV)', 'Moderna (Spikevax)'],
            doses: ['Dose 1', 'Dose 2', 'Booster 1', 'Booster 2'],
        },
        {
            name: 'Human Papilloma Virus Vaccine (HPV)',
            vaccines: ['Cervarix (2vHPV)', 'Gardasil (4vHPV)', 'Gardasil 9 (9vHPV)'],
            doses: ['Dose 1', 'Dose 2', 'Dose 3'],
        },
        {
            name: 'Measles',
            vaccines: ['Measles Vaccine', 'MR (Measles-Rubella)', 'MMR (Measles-Mumps-Rubella)'],
            doses: ['Dose 1', 'Dose 2'],
        },
        {
            name: 'Rota',
            vaccines: ['Rotarix (RV1)', 'RotaTeq (RV5)'],
            doses: ['Dose 1', 'Dose 2', 'Dose 3'],
        },
        {
            name: 'Pneumococcal conjugate vaccine (PCV)',
            vaccines: ['PCV 10 (Synflorix)', 'PCV 13 (Prevnar 13)', 'PCV 15', 'PCV 20'],
            doses: ['Dose 1', 'Dose 2', 'Dose 3', 'Booster 1'],
        },
        {
            name: 'DPT-HepB-Hib',
            vaccines: ['Pentavalent (DPT-HepB-Hib)', 'Hexaxim', 'Infanrix Hexa'],
            doses: ['Dose 1', 'Dose 2', 'Dose 3', 'Booster 1'],
        },
        {
            name: 'Inactivated Polio Vaccine (IPV)',
            vaccines: ['IPV (IPOL)', 'Imovax Polio'],
            doses: ['Dose 1', 'Dose 2', 'Booster 1'],
        },
        {
            name: 'Bacillus Calmette-Guérin (BCG)',
            vaccines: ['BCG Vaccine'],
            doses: ['Birth Dose', 'Single Dose'],
        },
    ];

    // Parse existing value
    let existing = [];
    try {
        const raw = @json(old('immunization_history', $s?->immunization_history ?? ''));
        if (typeof raw === 'string' && raw.startsWith('[')) {
            existing = JSON.parse(raw);
        } else if (Array.isArray(raw)) {
            existing = raw;
        }
    } catch(e) {}

    return {
        entries: existing,
        showModal: false,
        showForm: false,
        vaccineTypes: vaccineTypes,
        form: { vaccine_type: '', vaccine: '', dose: '', batch_number: '', date_given: '' },
        get availableVaccines() {
            const vt = this.vaccineTypes.find(v => v.name === this.form.vaccine_type);
            return vt ? vt.vaccines : [];
        },
        get availableDoses() {
            const vt = this.vaccineTypes.find(v => v.name === this.form.vaccine_type);
            return vt ? vt.doses : ['Dose 1', 'Dose 2', 'Dose 3', 'Booster 1', 'Booster 2'];
        },
        resetForm() {
            this.form = { vaccine_type: '', vaccine: '', dose: '', batch_number: '', date_given: '' };
        },
        addEntry() {
            if (!this.form.vaccine_type || !this.form.vaccine || !this.form.dose || !this.form.date_given) return;
            this.entries.push({...this.form});
            this.resetForm();
        },
        removeEntry(idx) {
            this.entries.splice(idx, 1);
        }
    };
}

// ── Birth History modal helper ───────────────────────────────────────────────
function birthHistoryModal(
    initialWeight, initialLength, initialOutcome,
    initialHead, initialChest, initialGeneralCondition,
    initialBreastFeeding, initialOtherFeeding,
    initialDeliveryTime, initialVaccinationOutside,
    initialTetanus, initialNotes
) {
    return {
        showModal: false,
        birthWeight:          initialWeight          || '',
        birthLength:          initialLength          || '',
        birthOutcome:         initialOutcome         || '',
        headCircumference:    initialHead            || '',
        chestCircumference:   initialChest           || '',
        generalCondition:     initialGeneralCondition|| '',
        isBreastFeedingWell:  !!initialBreastFeeding,
        otherFeedingOption:   initialOtherFeeding    || '',
        deliveryTime:         initialDeliveryTime    || '',
        vaccinationOutside:   initialVaccinationOutside || '',
        tetanusAtBirth:       initialTetanus         || '',
        birthNotes:           initialNotes           || '',
        get hasData() {
            return !!this.birthWeight || !!this.birthLength || !!this.birthOutcome ||
                   !!this.headCircumference || !!this.chestCircumference ||
                   !!this.generalCondition || this.isBreastFeedingWell ||
                   !!this.otherFeedingOption || !!this.deliveryTime ||
                   !!this.vaccinationOutside || !!this.tetanusAtBirth || !!this.birthNotes;
        },
    };
}

// ── Feeding History modal helper ─────────────────────────────────────────────
function feedingHistoryModal(initialCode, initialComments) {
    return {
        showModal: false,
        feedingCode: initialCode || '',
        feedingComments: initialComments || '',
        get hasData() {
            return !!this.feedingCode || !!this.feedingComments;
        },
    };
}

// ── Prescription cart ────────────────────────────────────────────────────────
function prescriptionCart() {
    const emptyForm = () => ({
        drug_name: '',
        dose: '',
        item_per_dose: 0,
        frequency: '',
        time_per: '',
        frequency_unit: '',
        duration: '',
        duration_unit: '',
        route: '',
        start_date: new Date().toISOString().slice(0, 10),
        end_date: '',
        quantity_prescribed: '',
        is_passer_by: '0',
        instructions: '',
    });

    return {
        cart: [],
        showModal: false,
        form: emptyForm(),
        showError: false,
        errorMsg: '',

        addToCart() {
            if (!this.form.drug_name.trim()) {
                this.errorMsg = 'General Drug is required.';
                this.showError = true;
                return;
            }
            if (!this.form.dose.trim()) {
                this.errorMsg = 'Dosage is required.';
                this.showError = true;
                return;
            }
            this.showError = false;
            this.cart.push({ ...this.form });
            this.form = emptyForm();
            this.showModal = false;
        },

        removeFromCart(idx) {
            this.cart.splice(idx, 1);
        },
    };
}

// ── TB Constitutional Symptoms modal helper ──────────────────────────────────
function tbSymptomsModal(initialSymptoms, initialConstitutional, initialPresumptive) {
    const tbOptionsList = [
        { key: 'lethargy',            label: 'Lethargy' },
        { key: 'cough',               label: 'Cough' },
        { key: 'fever',               label: 'Fever' },
        { key: 'weight_loss',         label: 'Weight Loss' },
        { key: 'blood_stained_sputum',label: 'Blood-stained sputum' },
        { key: 'shortness_of_breath', label: 'Shortness of breath' },
        { key: 'chest_pain',          label: 'Chest Pain' },
        { key: 'night_sweats',        label: 'Night Sweats' },
        { key: 'fatigue',             label: 'Fatigue' },
    ];

    const symptomsState = {};
    tbOptionsList.forEach(o => {
        symptomsState[o.key] = Array.isArray(initialSymptoms) && initialSymptoms.includes(o.key);
    });

    return {
        showModal: false,
        tbOptions: tbOptionsList,
        symptoms: symptomsState,
        constitutionalSymptoms: initialConstitutional || '',
        presumptiveTbCaseNo: initialPresumptive || '',
        get checkedSymptoms() {
            return this.tbOptions
                .filter(o => this.symptoms[o.key])
                .map(o => o.key);
        },
    };
}

// ── Complaints & Histories modal helper ─────────────────────────────────────
function complaintsHistoryModal() {
    return {
        showModal: false,
        complaints: '',
        history: '',
        initFromText(initialComplaints, initialHistory) {
            this.complaints = typeof initialComplaints === 'string' ? initialComplaints : '';
            this.history    = typeof initialHistory    === 'string' ? initialHistory    : '';
        },
    };
}

// ── Family & Social History modal helper ────────────────────────────────────
function familySocialHistoryModal() {
    return {
        showModal: false,
        tab: 'family',
        familyHistory: '',
        ncdRiskFactors: null,
        smokes: null,
        drinksAlcohol: null,
        initialFamilyRaw: '',
        initialSocialRaw: '',
        initFromText(initialFamily, initialSocial) {
            // Family history
            const fRaw = typeof initialFamily === 'string' ? initialFamily : '';
            try {
                const parsed = JSON.parse(fRaw);
                if (parsed && typeof parsed === 'object') {
                    this.familyHistory   = parsed.family_history   ?? '';
                    this.ncdRiskFactors  = parsed.ncd_risk_factors  ?? null;
                } else {
                    this.initialFamilyRaw = fRaw;
                }
            } catch (e) {
                this.initialFamilyRaw = fRaw;
            }
            // Social history
            const sRaw = typeof initialSocial === 'string' ? initialSocial : '';
            try {
                const parsed = JSON.parse(sRaw);
                if (parsed && typeof parsed === 'object') {
                    this.smokes        = parsed.smokes         ?? null;
                    this.drinksAlcohol = parsed.drinks_alcohol ?? null;
                } else {
                    this.initialSocialRaw = sRaw;
                }
            } catch (e) {
                this.initialSocialRaw = sRaw;
            }
        },
        get serializedFamily() {
            if (this.familyHistory !== '' || this.ncdRiskFactors !== null) {
                return JSON.stringify({ family_history: this.familyHistory, ncd_risk_factors: this.ncdRiskFactors });
            }
            return this.initialFamilyRaw;
        },
        get serializedSocial() {
            if (this.smokes !== null || this.drinksAlcohol !== null) {
                return JSON.stringify({ smokes: this.smokes, drinks_alcohol: this.drinksAlcohol });
            }
            return this.initialSocialRaw;
        },
    };
}

// ── Allergies modal helper ──────────────────────────────────────────────────
function allergiesModal() {
    return {
        showModal: false,
        error: '',
        entries: [],
        initialRaw: '',
        form: {
            allergy_type: '',
            severity: '',
            drug_type: '',
        },
        initFromText(initialText) {
            this.initialRaw = typeof initialText === 'string' ? initialText : '';
            if (!this.initialRaw) return;
            try {
                const parsed = JSON.parse(this.initialRaw);
                if (Array.isArray(parsed)) {
                    this.entries = parsed;
                    this.initialRaw = '';
                }
            } catch (e) {
                // Legacy plain text — kept in initialRaw for display
            }
        },
        addEntry() {
            this.error = '';
            if (!this.form.allergy_type) {
                this.error = 'Allergy Type is required.';
                return;
            }
            if (!this.form.severity) {
                this.error = 'Severity is required.';
                return;
            }
            this.entries.push({
                allergy_type: this.form.allergy_type,
                severity: this.form.severity,
                drug_type: this.form.allergy_type === 'Drug' ? this.form.drug_type : '',
            });
            this.form.allergy_type = '';
            this.form.severity = '';
            this.form.drug_type = '';
        },
        removeEntry(idx) {
            this.entries.splice(idx, 1);
        },
        get serialized() {
            if (this.entries.length > 0) return JSON.stringify(this.entries);
            return this.initialRaw;
        },
    };
}
</script>
@endpush
