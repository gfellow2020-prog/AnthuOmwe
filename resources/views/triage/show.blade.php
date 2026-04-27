@extends('layouts.dashboard')

@section('title', 'Triage — ' . $encounter->encounter_number)

@push('styles')
<style>
    .field-label { display:block; font-size:13px; font-weight:600; color:#171717; margin-bottom:4px; }
    .field-label .unit { font-weight:400; color:#a3a3a3; font-size:11px; margin-left:4px; }
    .field-input { width:100%; padding:9px 13px; font-size:14px; border:1px solid #d4d4d4; border-radius:4px;
                   background:#fff; color:#171717; outline:none; transition:border-color .15s; }
    .field-input:focus { border-color:#171717; box-shadow:0 0 0 3px rgba(23,23,23,.08); }
    .field-input::placeholder { color:#a3a3a3; }
    textarea.field-input { resize:vertical; min-height:70px; }
    .detail-row { display:flex; gap:12px; padding:9px 0; border-bottom:1px solid #e5e5e5; font-size:14px; }
    .detail-row:last-child { border-bottom:none; }
    .detail-label { flex-shrink:0; width:160px; font-size:12px; font-weight:600; color:#525252; text-transform:uppercase; letter-spacing:.04em; }
    .vital-card { background:#f5f5f5; border:1px solid #e5e5e5; border-radius:4px; padding:12px 14px; }
    .vital-value { font-size:22px; font-weight:700; color:#171717; line-height:1; }
    .vital-label { font-size:11px; color:#525252; font-weight:500; margin-top:2px; }
    .vital-error { border-color:#dc2626 !important; background:#fef2f2 !important; color:#dc2626 !important; }
    .vital-error:focus { box-shadow:0 0 0 3px rgba(220,38,38,.15) !important; }
    main.blurred { filter: blur(3px); cursor: pointer; transition: filter .3s; }
</style>
@endpush

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('triage.queue') }}" class="hover:text-neutral-700 transition">Triage</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">{{ $encounter->encounter_number }}</span>
@endsection

@section('navbar-extras')
<div class="relative flex justify-center">
    <button type="button" onclick="event.stopPropagation(); toggleReviewPanel()"
            class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-b-lg bg-neutral-900 hover:bg-neutral-800 text-white shadow-lg transition -mt-[1px] relative z-[999]">
        <svg id="reviewChevronIcon" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
        Passed Review
    </button>

    {{-- ── Passed Review Slide-down Panel ──── --}}
    <div id="reviewPanel" class="absolute top-full left-0 right-0 bg-white dark:bg-neutral-900 shadow-2xl z-[998] overflow-hidden transition-[max-height] duration-300 ease-in-out" style="max-height:0;">
        <div class="overflow-y-auto" style="max-height:50vh;">
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-wide">Passed Review — Previous Encounters</h2>
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
                @if($past->triageRecord)
                @php $pt = $past->triageRecord; @endphp
                {{-- Vital Signs --}}
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
                            ['Sugar',      $pt->blood_sugar ? $pt->blood_sugar.' mmol/L' : '—'],
                            ['MUAC',       $pt->muac ? $pt->muac.' cm' : '—'],
                            ['MUAC Score', $pt->muac_score ?? '—'],
                            ['Abd. Circ',  $pt->abdominal_circumference ? $pt->abdominal_circumference.' cm' : '—'],
                        ] as [$label, $val])
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded px-2 py-1.5">
                            <div class="text-sm font-bold text-neutral-900 dark:text-white">{{ $val }}</div>
                            <div class="text-[10px] text-neutral-500">{{ $label }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Clinical Notes --}}
                @if($pt->chief_complaint_brief || $pt->startup_interventions_notes)
                <div>
                    <h4 class="text-[11px] font-semibold text-neutral-500 uppercase tracking-wide mb-2">Clinical Notes</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
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
                </div>
                @endif
                @else
                <p class="text-xs text-neutral-400 italic">No triage data recorded</p>
                @endif

                {{-- Startup Medications --}}
                @if($past->startupMedications->isNotEmpty())
                <div>
                    <h4 class="text-[11px] font-semibold text-neutral-500 uppercase tracking-wide mb-2">Startup Medications ({{ $past->startupMedications->count() }})</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-neutral-50 dark:bg-neutral-800 text-left">
                                    <th class="px-2 py-1.5 font-semibold text-neutral-500 uppercase">Medication</th>
                                    <th class="px-2 py-1.5 font-semibold text-neutral-500 uppercase">Dosage</th>
                                    <th class="px-2 py-1.5 font-semibold text-neutral-500 uppercase">Route</th>
                                    <th class="px-2 py-1.5 font-semibold text-neutral-500 uppercase">Frequency</th>
                                    <th class="px-2 py-1.5 font-semibold text-neutral-500 uppercase">Given At</th>
                                    <th class="px-2 py-1.5 font-semibold text-neutral-500 uppercase">By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                                @foreach($past->startupMedications as $med)
                                <tr>
                                    <td class="px-2 py-1.5 font-medium text-neutral-900 dark:text-white">{{ $med->medication_name }}</td>
                                    <td class="px-2 py-1.5 text-neutral-600">{{ $med->dosage ?? '—' }}</td>
                                    <td class="px-2 py-1.5 text-neutral-600">{{ $med->route ?? '—' }}</td>
                                    <td class="px-2 py-1.5 text-neutral-600">{{ $med->frequency ?? '—' }}</td>
                                    <td class="px-2 py-1.5 text-neutral-600">{{ $med->administered_at ? $med->administered_at->format('d M Y H:i') : '—' }}</td>
                                    <td class="px-2 py-1.5 text-neutral-600">{{ $med->recordedBy->name ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
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

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 rounded text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-neutral-100 dark:bg-neutral-800 border border-neutral-700 text-neutral-800 dark:text-neutral-200 rounded text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Left: Vitals form ───────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Existing vitals summary (if already saved) --}}
        @if($encounter->triageRecord)
        <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700 p-6">
            <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wide mb-4">Current Vitals</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @php $t = $encounter->triageRecord; @endphp
                @foreach([
                    ['Weight',   $t->weight    ? $t->weight.' kg'   : '—'],
                    ['Height',   $t->height    ? $t->height.' cm'   : '—'],
                    ['BMI',      $t->bmi       ?? '—'],
                    ['Temp',     $t->temperature ? $t->temperature.' °C' : '—'],
                    ['Pulse',    $t->pulse     ? $t->pulse.' bpm'   : '—'],
                    ['RR',       $t->respiratory_rate ? $t->respiratory_rate.'/min' : '—'],
                    ['BP',       $t->bloodPressure()],
                    ['O₂ Sat',   $t->oxygen_saturation ? $t->oxygen_saturation.'%' : '—'],
                    ['Sugar',    $t->blood_sugar ? $t->blood_sugar.' mmol/L' : '—'],
                    ['MUAC',     $t->muac ? $t->muac.' cm' : '—'],
                    ['MUAC Score', $t->muac_score ?? '—'],
                    ['Abd. Circ', $t->abdominal_circumference ? $t->abdominal_circumference.' cm' : '—'],
                ] as [$label, $val])
                <div class="vital-card">
                    <div class="vital-value text-lg">{{ $val }}</div>
                    <div class="vital-label">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @php
            $isAtTriage = $encounter->current_stage->value === 'triage';
        @endphp

        @if($isAtTriage)
        {{-- Triage form — editable only when encounter is at triage stage --}}
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-neutral-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-neutral-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-neutral-900">Record Vitals &amp; Notes</h2>
            </div>

            <form method="POST" action="{{ route('triage.complete', $encounter) }}" class="p-6 space-y-6"
                  id="triage-form">
                @csrf

                {{-- Vitals grid --}}
                <div>
                    <h3 class="text-sm font-semibold text-neutral-600 uppercase tracking-wide mb-3">Vital Signs</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="field-label">Weight <span class="unit">kg</span></label>
                            <input type="number" step="0.1" min="0.5" max="300" name="weight" class="field-input @error('weight') border-neutral-400 @enderror"
                                   value="{{ old('weight', $encounter->triageRecord?->weight) }}" placeholder="e.g. 65.5" />
                            @error('weight')<p class="text-xs text-neutral-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label">Height <span class="unit">cm</span></label>
                            <input type="number" step="0.1" min="20" max="250" name="height" class="field-input @error('height') border-neutral-400 @enderror"
                                   value="{{ old('height', $encounter->triageRecord?->height) }}" placeholder="e.g. 165" />
                        </div>
                        <div>
                            <label class="field-label">Temperature <span class="unit">°C</span></label>
                            <input type="number" step="0.1" min="25" max="45" name="temperature" class="field-input @error('temperature') border-neutral-400 @enderror"
                                   value="{{ old('temperature', $encounter->triageRecord?->temperature) }}" placeholder="e.g. 36.8" />
                        </div>
                        <div>
                            <label class="field-label">Pulse <span class="unit">bpm</span></label>
                            <input type="number" min="20" max="300" name="pulse" class="field-input"
                                   value="{{ old('pulse', $encounter->triageRecord?->pulse) }}" placeholder="e.g. 72" />
                        </div>
                        <div>
                            <label class="field-label">Resp. Rate <span class="unit">breaths/min</span></label>
                            <input type="number" min="4" max="80" name="respiratory_rate" class="field-input"
                                   value="{{ old('respiratory_rate', $encounter->triageRecord?->respiratory_rate) }}" placeholder="e.g. 18" />
                        </div>
                        <div>
                            <label class="field-label">Oxygen Saturation <span class="unit">%</span></label>
                            <input type="number" step="0.1" min="0" max="100" name="oxygen_saturation" class="field-input"
                                   value="{{ old('oxygen_saturation', $encounter->triageRecord?->oxygen_saturation) }}" placeholder="e.g. 98" />
                        </div>
                        <div>
                            <label class="field-label">Systolic BP <span class="unit">mmHg</span></label>
                            <input type="number" min="40" max="300" name="systolic_bp" class="field-input"
                                   value="{{ old('systolic_bp', $encounter->triageRecord?->systolic_bp) }}" placeholder="e.g. 120" />
                        </div>
                        <div>
                            <label class="field-label">Diastolic BP <span class="unit">mmHg</span></label>
                            <input type="number" min="20" max="200" name="diastolic_bp" class="field-input"
                                   value="{{ old('diastolic_bp', $encounter->triageRecord?->diastolic_bp) }}" placeholder="e.g. 80" />
                        </div>
                        <div>
                            <label class="field-label">Blood Sugar <span class="unit">mmol/L</span></label>
                            <input type="number" step="0.1" min="0.5" max="50" name="blood_sugar" class="field-input"
                                   value="{{ old('blood_sugar', $encounter->triageRecord?->blood_sugar) }}" placeholder="e.g. 5.5" />
                        </div>
                        <div>
                            <label class="field-label">MUAC <span class="unit">cm</span></label>
                            <input type="number" step="0.1" min="5" max="40" name="muac" id="muac_input" class="field-input"
                                   value="{{ old('muac', $encounter->triageRecord?->muac) }}" placeholder="e.g. 12.5" oninput="calculateMuacScore()" />
                        </div>
                        <div>
                            <label class="field-label">MUAC Score</label>
                            <input type="text" name="muac_score" id="muac_score" class="field-input font-semibold" readonly
                                   value="{{ old('muac_score', $encounter->triageRecord?->muac_score) }}" placeholder="Auto-calculated" />
                        </div>
                        <div>
                            <label class="field-label">Abd. Circumference <span class="unit">cm</span></label>
                            <input type="number" step="0.1" min="20" max="200" name="abdominal_circumference" class="field-input"
                                   value="{{ old('abdominal_circumference', $encounter->triageRecord?->abdominal_circumference) }}" placeholder="e.g. 85" />
                        </div>
                    </div>
                </div>

                {{-- Clinical notes --}}
                <div>
                    <h3 class="text-sm font-semibold text-neutral-600 uppercase tracking-wide mb-3">Clinical Notes</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="field-label">Chief Complaint (brief)</label>
                            <textarea name="chief_complaint_brief" rows="2" class="field-input"
                                      placeholder="Patient's main presenting complaint…">{{ old('chief_complaint_brief', $encounter->triageRecord?->chief_complaint_brief) }}</textarea>
                        </div>
                        <div>
                            <label class="field-label">Startup Interventions</label>
                            <textarea name="startup_interventions_notes" rows="2" class="field-input"
                                      placeholder="Any immediate interventions performed…">{{ old('startup_interventions_notes', $encounter->triageRecord?->startup_interventions_notes) }}</textarea>
                        </div>

                        <div>
                            <label class="field-label">Handover Note to Screening <span class="text-neutral-400 font-normal text-xs">(optional)</span></label>
                            <textarea name="notes" rows="2" class="field-input"
                                      placeholder="Anything the screening clinician should know…">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-neutral-100">
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                        Save &amp; Queue to Screening
                    </button>
                    <a href="{{ route('triage.queue') }}" class="btn-secondary">Back to Queue</a>
                </div>
            </form>
        </div>

        @else
        {{-- Read-only view when encounter has moved past triage --}}
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-neutral-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-neutral-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-neutral-900">Triage Vitals (Completed)</h2>
                </div>
                <span class="text-xs font-semibold bg-neutral-100 text-neutral-600 px-2 py-0.5 rounded-full uppercase">
                    Now at {{ str_replace('_', ' ', ucfirst($encounter->current_stage->value)) }}
                </span>
            </div>
            <div class="p-6">
                <p class="text-sm text-neutral-500 mb-4">This encounter has moved past triage. Vitals are shown as read-only.</p>

                @if($encounter->triageRecord)
                @php $t = $encounter->triageRecord; @endphp
                <h3 class="text-sm font-semibold text-neutral-600 uppercase tracking-wide mb-3">Vital Signs</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                    @foreach([
                        ['Weight',   $t->weight    ? $t->weight.' kg'   : '—'],
                        ['Height',   $t->height    ? $t->height.' cm'   : '—'],
                        ['BMI',      $t->bmi       ?? '—'],
                        ['Temp',     $t->temperature ? $t->temperature.' °C' : '—'],
                        ['Pulse',    $t->pulse     ? $t->pulse.' bpm'   : '—'],
                        ['RR',       $t->respiratory_rate ? $t->respiratory_rate.'/min' : '—'],
                        ['BP',       $t->bloodPressure()],
                        ['O₂ Sat',   $t->oxygen_saturation ? $t->oxygen_saturation.'%' : '—'],
                        ['Sugar',    $t->blood_sugar ? $t->blood_sugar.' mmol/L' : '—'],
                        ['MUAC',     $t->muac ? $t->muac.' cm' : '—'],
                        ['MUAC Score', $t->muac_score ?? '—'],
                        ['Abd. Circ', $t->abdominal_circumference ? $t->abdominal_circumference.' cm' : '—'],
                    ] as [$label, $val])
                    <div class="vital-card">
                        <div class="vital-value text-lg">{{ $val }}</div>
                        <div class="vital-label">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>

                @if($t->chief_complaint_brief)
                <h3 class="text-sm font-semibold text-neutral-600 uppercase tracking-wide mb-2">Chief Complaint</h3>
                <p class="text-sm text-neutral-700 dark:text-neutral-300 mb-4">{{ $t->chief_complaint_brief }}</p>
                @endif

                @if($t->startup_interventions_notes)
                <h3 class="text-sm font-semibold text-neutral-600 uppercase tracking-wide mb-2">Startup Interventions</h3>
                <p class="text-sm text-neutral-700 dark:text-neutral-300 mb-4">{{ $t->startup_interventions_notes }}</p>
                @endif

                {{-- Structured startup medications --}}
                @if($encounter->startupMedications->isNotEmpty())
                <h3 class="text-sm font-semibold text-neutral-600 uppercase tracking-wide mb-2">Startup Medications (Recorded)</h3>
                <div class="overflow-x-auto mb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-700 text-left">
                                <th class="px-4 py-2 text-xs font-semibold text-neutral-600 uppercase">Medication</th>
                                <th class="px-4 py-2 text-xs font-semibold text-neutral-600 uppercase">Dosage</th>
                                <th class="px-4 py-2 text-xs font-semibold text-neutral-600 uppercase">Route</th>
                                <th class="px-4 py-2 text-xs font-semibold text-neutral-600 uppercase">Frequency</th>
                                <th class="px-4 py-2 text-xs font-semibold text-neutral-600 uppercase">Given At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach($encounter->startupMedications as $med)
                            <tr>
                                <td class="px-4 py-2 font-medium text-neutral-900 dark:text-white">{{ $med->medication_name }}</td>
                                <td class="px-4 py-2 text-neutral-600">{{ $med->dosage ?? '—' }}</td>
                                <td class="px-4 py-2 text-neutral-600">{{ $med->route ?? '—' }}</td>
                                <td class="px-4 py-2 text-neutral-600">{{ $med->frequency ?? '—' }}</td>
                                <td class="px-4 py-2 text-neutral-600">{{ $med->administered_at ? $med->administered_at->format('d M Y H:i') : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                @else
                <p class="text-sm text-neutral-400">No vitals were recorded during triage.</p>
                @endif

                <div class="pt-4 border-t border-neutral-100 mt-4">
                    <a href="{{ route('triage.vitals') }}" class="btn-secondary">← Back to Vitals</a>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Right: Patient summary + audit ─────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Patient card --}}
        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-neutral-100">
                <h2 class="text-sm font-semibold text-neutral-700">Patient</h2>
            </div>
            <div class="px-6 py-4 space-y-1 text-sm">
                <p class="font-semibold text-neutral-900">{{ $encounter->patient->full_name }}</p>
                <p class="text-neutral-500">{{ $encounter->patient->patient_id }}</p>
                <p class="text-neutral-500">
                    {{ ucfirst($encounter->patient->gender ?? '—') }}
                    @if($encounter->patient->date_of_birth)
                     · DOB: {{ $encounter->patient->date_of_birth->format('d M Y') }}
                    @endif
                </p>
                <p class="text-neutral-500">Phone: {{ $encounter->patient->phone_number ?? '—' }}</p>
                @if($encounter->patient->allergies)
                <p class="text-neutral-600 font-medium mt-2">⚠ Allergies: {{ $encounter->patient->allergies }}</p>
                @endif
            </div>
        </div>

        {{-- ── Startup Medications Card ──── --}}
        @if($isAtTriage)
        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700">
            {{-- Header with view-list button --}}
            <div class="px-6 py-4 border-b border-neutral-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-neutral-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.155-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-neutral-900">Startup Medications</h2>
                </div>
                <button type="button" onclick="event.stopPropagation(); openMedsDrawer()"
                        class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded bg-neutral-900 hover:bg-neutral-800 text-white transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    View List
                    <span id="medsBadge" class="min-w-[18px] h-[18px] flex items-center justify-center px-1 text-[10px] font-bold rounded-full bg-white text-neutral-900 {{ $encounter->startupMedications->count() === 0 ? 'hidden' : '' }}">{{ $encounter->startupMedications->count() }}</span>
                </button>
            </div>

            {{-- Add medication form (inline) --}}
            <div class="px-6 py-4">
                <h4 class="text-xs font-semibold text-neutral-600 uppercase tracking-wide mb-3">Add Medication</h4>
                <form id="addMedForm" data-ajax="true">
                    <input type="hidden" name="medication_id" id="medication_id">
                    <div class="space-y-3">
                        <div class="relative">
                            <label class="field-label">Medication Name <span class="text-red-500">*</span></label>
                            <input type="text" name="medication_name" id="medication_search" class="field-input" placeholder="Start typing to search..." required autocomplete="off">
                            <div id="med-dropdown" class="hidden absolute z-50 w-full mt-1 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="field-label">Dosage</label>
                                <input type="text" name="dosage" class="field-input" placeholder="e.g. 500mg">
                            </div>
                            <div>
                                <label class="field-label">Route</label>
                                <select name="route" class="field-input">
                                    <option value="">--Select--</option>
                                    <option value="Oral">Oral</option>
                                    <option value="IV">IV</option>
                                    <option value="IM">IM</option>
                                    <option value="SC">SC</option>
                                    <option value="Topical">Topical</option>
                                    <option value="Rectal">Rectal</option>
                                    <option value="Inhaled">Inhaled</option>
                                    <option value="Sublingual">Sublingual</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="field-label">Frequency</label>
                                <select name="frequency" class="field-input">
                                    <option value="">--Select--</option>
                                    <option value="Stat">Stat</option>
                                    <option value="OD">OD (once daily)</option>
                                    <option value="BD">BD (twice daily)</option>
                                    <option value="TDS">TDS (3x daily)</option>
                                    <option value="QDS">QDS (4x daily)</option>
                                    <option value="PRN">PRN (as needed)</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Administered At</label>
                                <input type="datetime-local" name="administered_at" class="field-input" value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Notes</label>
                            <input type="text" name="notes" class="field-input" placeholder="Optional notes…">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" id="addMedBtn" class="w-full px-4 py-2 text-xs font-semibold rounded bg-neutral-900 hover:bg-neutral-800 text-white transition">
                            + Add Medication
                        </button>
                    </div>
                </form>
                {{-- AJAX success flash --}}
                <div id="medFlash" class="hidden mt-3 px-3 py-2 bg-neutral-100 border border-neutral-300 text-neutral-800 rounded text-xs font-medium"></div>
            </div>
        </div>

        @endif

    </div>
</div>

@endsection

@section('modals')
@if(!$encounter->is_locked)
{{-- ── Startup Medications Slide-in Drawer (list only) ──── --}}
<div id="medsDrawer" class="fixed top-0 right-0 h-full w-[28rem] bg-white dark:bg-neutral-900 shadow-2xl z-[999] transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    {{-- Edge handle to toggle drawer --}}
    <button type="button" onclick="toggleMedsDrawer()"
            class="absolute left-0 top-1/2 -translate-x-full -translate-y-1/2 w-6 h-16 bg-neutral-900 dark:bg-neutral-700 hover:bg-neutral-700 dark:hover:bg-neutral-600 text-white rounded-l-md flex items-center justify-center shadow-lg transition">
        <svg id="drawerHandleIcon" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
    {{-- Drawer header --}}
    <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between flex-shrink-0">
        <div>
            <h2 class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-wide">Startup Medications</h2>
            <p class="text-[11px] text-neutral-500 mt-0.5"><span id="drawerCount">{{ $encounter->startupMedications->count() }}</span> recorded</p>
        </div>
        <button type="button" onclick="closeMedsDrawer()" class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Drawer body (scrollable list) --}}
    <div id="medsListContainer" class="flex-1 overflow-y-auto">
        @if($encounter->startupMedications->isNotEmpty())
        <div class="divide-y divide-neutral-100 dark:divide-neutral-700">
            @foreach($encounter->startupMedications as $med)
            <div class="px-5 py-3 med-item" data-med-id="{{ $med->id }}">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $med->medication_name }}</p>
                        <p class="text-xs text-neutral-500 mt-0.5">
                            {{ $med->dosage ?? '' }}{{ $med->route ? ' · '.$med->route : '' }}{{ $med->frequency ? ' · '.$med->frequency : '' }}
                        </p>
                        <p class="text-[11px] text-neutral-400 mt-0.5">
                            {{ $med->administered_at ? $med->administered_at->format('d M Y H:i') : '' }}
                            {{ $med->recordedBy ? ' · '.$med->recordedBy->name : '' }}
                        </p>
                        @if($med->notes)
                        <p class="text-[11px] text-neutral-400 italic mt-0.5">{{ $med->notes }}</p>
                        @endif
                    </div>
                    <button type="button" onclick="removeMed(this, '{{ route('triage.startup-medications.destroy', $med) }}')"
                            class="flex-shrink-0 mt-0.5 p-1 rounded hover:bg-red-50 transition" title="Remove">
                        <svg class="w-3.5 h-3.5 text-red-400 hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div id="medsEmpty" class="px-5 py-8 text-center">
            <svg class="w-8 h-8 text-neutral-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.155-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            <p class="text-xs text-neutral-400">No medications added yet</p>
        </div>
        @endif
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
const STORE_URL  = '{{ route("triage.startup-medications.store", $encounter) }}';

// ── Review panel slide-down ─────────────────────────────────────────
function toggleReviewPanel() {
    const panel = document.getElementById('reviewPanel');
    const icon = document.getElementById('reviewChevronIcon');
    const main = document.querySelector('#mainPanel > main');
    const isOpen = panel.style.maxHeight !== '0px' && panel.style.maxHeight !== '';
    if (isOpen) {
        panel.style.maxHeight = '0px';
        icon.classList.remove('rotate-180');
        main.classList.remove('blurred');
        main.removeEventListener('click', closeReviewPanel);
    } else {
        panel.style.maxHeight = '50vh';
        icon.classList.add('rotate-180');
        main.classList.add('blurred');
        setTimeout(() => {
            main.addEventListener('click', closeReviewPanel, { once: true });
        }, 50);
    }
}
function closeReviewPanel() {
    const panel = document.getElementById('reviewPanel');
    if (panel.style.maxHeight !== '0px') toggleReviewPanel();
}

// ── Real-time vitals validation ────────────────────────────────────
document.querySelectorAll('.field-input[type="number"][min][max]').forEach(input => {
    input.addEventListener('input', function() {
        const val = parseFloat(this.value);
        const min = parseFloat(this.min);
        const max = parseFloat(this.max);
        if (this.value === '' || isNaN(val)) {
            this.classList.remove('vital-error');
            return;
        }
        if (val < min || val > max) {
            this.classList.add('vital-error');
        } else {
            this.classList.remove('vital-error');
        }
    });
});

document.querySelector('form[action*="triage"]')?.addEventListener('submit', function(e) {
    const invalid = this.querySelectorAll('.vital-error');
    if (invalid.length) {
        e.preventDefault();
        invalid[0].focus();
        invalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

// ── Drawer open/close ──────────────────────────────────────────────
function openMedsDrawer() {
    const main = document.querySelector('#mainPanel > main');
    document.getElementById('medsDrawer').classList.remove('translate-x-full');
    document.getElementById('drawerHandleIcon').classList.add('rotate-180');
    main.classList.add('blurred');
    setTimeout(() => {
        main.addEventListener('click', closeMedsDrawer, { once: true });
    }, 50);
}
function closeMedsDrawer() {
    const main = document.querySelector('#mainPanel > main');
    document.getElementById('medsDrawer').classList.add('translate-x-full');
    document.getElementById('drawerHandleIcon').classList.remove('rotate-180');
    main.classList.remove('blurred');
    main.removeEventListener('click', closeMedsDrawer);
}
function toggleMedsDrawer() {
    document.getElementById('medsDrawer').classList.contains('translate-x-full') ? openMedsDrawer() : closeMedsDrawer();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeMedsDrawer(); closeReviewPanel(); } });

// ── MUAC score calculation ─────────────────────────────────────────
function calculateMuacScore() {
    const val = parseFloat(document.getElementById('muac_input').value);
    const scoreField = document.getElementById('muac_score');
    if (isNaN(val) || val <= 0) { scoreField.value = ''; scoreField.style.color = ''; return; }
    let score, color;
    if (val < 11.5) { score = 'SAM (Red)'; color = '#dc2626'; }
    else if (val < 12.5) { score = 'MAM (Yellow)'; color = '#d97706'; }
    else { score = 'Normal (Green)'; color = '#16a34a'; }
    scoreField.value = score;
    scoreField.style.color = color;
}
document.addEventListener('DOMContentLoaded', calculateMuacScore);

// ── Update badge + drawer count ────────────────────────────────────
function updateMedCount(count) {
    const badge = document.getElementById('medsBadge');
    const drawerCount = document.getElementById('drawerCount');
    badge.textContent = count;
    drawerCount.textContent = count;
    badge.classList.toggle('hidden', count === 0);
}

// ── Build a single med item HTML ───────────────────────────────────
function buildMedItem(med) {
    const details = [med.dosage, med.route, med.frequency].filter(Boolean).join(' · ');
    const time = [med.administered_at, med.recorded_by].filter(Boolean).join(' · ');
    const notes = med.notes ? `<p class="text-[11px] text-neutral-400 italic mt-0.5">${escHtml(med.notes)}</p>` : '';

    return `<div class="px-5 py-3 med-item border-b border-neutral-100 dark:border-neutral-700" data-med-id="${med.id}">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-neutral-900 dark:text-white">${escHtml(med.medication_name)}</p>
                ${details ? `<p class="text-xs text-neutral-500 mt-0.5">${escHtml(details)}</p>` : ''}
                ${time ? `<p class="text-[11px] text-neutral-400 mt-0.5">${escHtml(time)}</p>` : ''}
                ${notes}
            </div>
            <button type="button" onclick="removeMed(this, '${med.destroy_url}')"
                    class="flex-shrink-0 mt-0.5 p-1 rounded hover:bg-red-50 transition" title="Remove">
                <svg class="w-3.5 h-3.5 text-red-400 hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
    </div>`;
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// ── Remove medication via AJAX ─────────────────────────────────────
function removeMed(btn, url) {
    if (!confirm('Remove this medication?')) return;
    btn.disabled = true;

    fetch(url, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const item = btn.closest('.med-item');
            item.remove();
            updateMedCount(data.count);
            // Show empty state if no items left
            if (data.count === 0) {
                document.getElementById('medsListContainer').innerHTML = `
                    <div id="medsEmpty" class="px-5 py-8 text-center">
                        <svg class="w-8 h-8 text-neutral-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.155-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <p class="text-xs text-neutral-400">No medications added yet</p>
                    </div>`;
            }
        }
    })
    .catch(() => { btn.disabled = false; alert('Failed to remove medication.'); });
}

// ── AJAX form submit ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const form        = document.getElementById('addMedForm');
    const flash       = document.getElementById('medFlash');
    const searchInput = document.getElementById('medication_search');
    const dropdown    = document.getElementById('med-dropdown');
    const hiddenId    = document.getElementById('medication_id');
    const routeSelect = form.querySelector('[name="route"]');
    const freqSelect  = form.querySelector('[name="frequency"]');
    const dosageInput = form.querySelector('[name="dosage"]');
    const submitBtn   = document.getElementById('addMedBtn');

    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Adding…';

        const formData = new FormData(form);

        fetch(STORE_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: formData,
        })
        .then(r => {
            if (!r.ok) return r.json().then(d => Promise.reject(d));
            return r.json();
        })
        .then(data => {
            if (data.success) {
                // Append to drawer list
                const container = document.getElementById('medsListContainer');
                const empty = document.getElementById('medsEmpty');
                if (empty) empty.remove();

                // Ensure there's a wrapper div for the list
                let listWrap = container.querySelector('.divide-y');
                if (!listWrap) {
                    listWrap = document.createElement('div');
                    listWrap.className = 'divide-y divide-neutral-100 dark:divide-neutral-700';
                    container.appendChild(listWrap);
                }
                listWrap.insertAdjacentHTML('beforeend', buildMedItem(data.med));

                updateMedCount(data.count);

                // Flash
                flash.textContent = '✓ ' + data.med.medication_name + ' added';
                flash.classList.remove('hidden');
                setTimeout(() => flash.classList.add('hidden'), 3000);

                // Reset form
                form.reset();
                hiddenId.value = '';
                form.querySelector('[name="administered_at"]').value = new Date().toISOString().slice(0, 16);
            }
        })
        .catch(err => {
            let msg = 'Failed to add medication.';
            if (err && err.errors) {
                msg = Object.values(err.errors).flat().join(', ');
            }
            flash.textContent = '✗ ' + msg;
            flash.classList.remove('hidden');
            setTimeout(() => flash.classList.add('hidden'), 4000);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = '+ Add Medication';
        });
    });

    // ── Medication autocomplete search ─────────────────────────────
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        const q = this.value.trim();
        hiddenId.value = '';

        clearTimeout(debounceTimer);
        if (q.length < 2) { dropdown.classList.add('hidden'); return; }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('medications.search') }}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(meds => {
                    if (!meds.length) { dropdown.classList.add('hidden'); return; }

                    dropdown.innerHTML = meds.map(m => `
                        <div class="med-option px-4 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700 border-b border-neutral-50 dark:border-neutral-700 last:border-0"
                             data-id="${m.id}"
                             data-name="${m.name}${m.strength ? ' ' + m.strength : ''} (${m.form})"
                             data-strength="${m.strength || ''}"
                             data-route="${m.default_route || ''}"
                             data-frequency="${m.default_frequency || ''}">
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">${m.name} ${m.strength || ''}</p>
                            <p class="text-xs text-neutral-500">${m.generic_name || ''} · ${m.form} · ${m.category}</p>
                        </div>
                    `).join('');

                    dropdown.classList.remove('hidden');

                    dropdown.querySelectorAll('.med-option').forEach(opt => {
                        opt.addEventListener('click', function() {
                            hiddenId.value       = this.dataset.id;
                            searchInput.value    = this.dataset.name;
                            if (this.dataset.strength) dosageInput.value = this.dataset.strength;
                            if (this.dataset.route)    setSelect(routeSelect, this.dataset.route);
                            if (this.dataset.frequency) setSelect(freqSelect, this.dataset.frequency);
                            dropdown.classList.add('hidden');
                        });
                    });
                });
        }, 250);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    function setSelect(select, value) {
        for (let opt of select.options) {
            if (opt.value === value) { select.value = value; return; }
        }
    }
});
</script>
@endpush
