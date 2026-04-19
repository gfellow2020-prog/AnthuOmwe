@extends('layouts.dashboard')

@section('title', 'Triage — ' . $encounter->encounter_number)

@push('styles')
<style>
    .field-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px; }
    .field-label .unit { font-weight:400; color:#9ca3af; font-size:11px; margin-left:4px; }
    .field-input { width:100%; padding:9px 13px; font-size:14px; border:1px solid #d1d5db; border-radius:8px;
                   background:#fff; color:#111827; outline:none; transition:border-color .15s; }
    .field-input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
    .field-input::placeholder { color:#9ca3af; }
    textarea.field-input { resize:vertical; min-height:70px; }
    .btn-primary  { display:inline-flex; align-items:center; gap:6px; padding:9px 22px; font-size:14px;
                    font-weight:600; background:#2563eb; color:#fff; border-radius:8px; border:none; cursor:pointer; transition:background .15s; }
    .btn-primary:hover  { background:#1d4ed8; }
    .btn-success  { display:inline-flex; align-items:center; gap:6px; padding:9px 22px; font-size:14px;
                    font-weight:600; background:#16a34a; color:#fff; border-radius:8px; border:none; cursor:pointer; transition:background .15s; }
    .btn-success:hover  { background:#15803d; }
    .btn-secondary { display:inline-flex; align-items:center; gap:6px; padding:9px 20px; font-size:14px;
                     font-weight:600; background:#f3f4f6; color:#374151; border-radius:8px; border:1px solid #d1d5db; cursor:pointer; transition:background .15s; }
    .detail-row { display:flex; gap:12px; padding:9px 0; border-bottom:1px solid #f3f4f6; font-size:14px; }
    .detail-row:last-child { border-bottom:none; }
    .detail-label { flex-shrink:0; width:160px; font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
    .vital-card { background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:12px 14px; }
    .vital-value { font-size:22px; font-weight:700; color:#1e40af; line-height:1; }
    .vital-label { font-size:11px; color:#6b7280; font-weight:500; margin-top:2px; }
</style>
@endpush

@section('page-header')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('triage.queue') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Triage — {{ $encounter->encounter_number }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $encounter->patient->full_name }}</p>
        </div>
    </div>
    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                 {{ $encounter->current_status->value === 'in_progress' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
        {{ $encounter->current_status->label() }}
    </span>
</div>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Left: Vitals form ───────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Existing vitals summary (if already saved) --}}
        @if($encounter->triageRecord)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Current Vitals</h3>
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
                    ['SpO₂',    $t->oxygen_saturation ? $t->oxygen_saturation.'%' : '—'],
                    ['Sugar',    $t->blood_sugar ? $t->blood_sugar.' mmol/L' : '—'],
                    ['Pain',     $t->pain_scale !== null ? $t->pain_scale.'/10' : '—'],
                ] as [$label, $val])
                <div class="vital-card">
                    <div class="vital-value text-lg">{{ $val }}</div>
                    <div class="vital-label">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Triage form --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-gray-900">Record Vitals &amp; Notes</h2>
            </div>

            <form method="POST" action="{{ route('triage.complete', $encounter) }}" class="p-6 space-y-6"
                  id="triage-form">
                @csrf

                {{-- Vitals grid --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Vital Signs</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="field-label">Weight <span class="unit">kg</span></label>
                            <input type="number" step="0.1" name="weight" class="field-input @error('weight') border-red-400 @enderror"
                                   value="{{ old('weight', $encounter->triageRecord?->weight) }}" placeholder="e.g. 65.5" />
                            @error('weight')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label">Height <span class="unit">cm</span></label>
                            <input type="number" step="0.1" name="height" class="field-input @error('height') border-red-400 @enderror"
                                   value="{{ old('height', $encounter->triageRecord?->height) }}" placeholder="e.g. 165" />
                        </div>
                        <div>
                            <label class="field-label">Temperature <span class="unit">°C</span></label>
                            <input type="number" step="0.1" name="temperature" class="field-input @error('temperature') border-red-400 @enderror"
                                   value="{{ old('temperature', $encounter->triageRecord?->temperature) }}" placeholder="e.g. 36.8" />
                        </div>
                        <div>
                            <label class="field-label">Pulse <span class="unit">bpm</span></label>
                            <input type="number" name="pulse" class="field-input"
                                   value="{{ old('pulse', $encounter->triageRecord?->pulse) }}" placeholder="e.g. 72" />
                        </div>
                        <div>
                            <label class="field-label">Resp. Rate <span class="unit">breaths/min</span></label>
                            <input type="number" name="respiratory_rate" class="field-input"
                                   value="{{ old('respiratory_rate', $encounter->triageRecord?->respiratory_rate) }}" placeholder="e.g. 18" />
                        </div>
                        <div>
                            <label class="field-label">SpO₂ <span class="unit">%</span></label>
                            <input type="number" step="0.1" name="oxygen_saturation" class="field-input"
                                   value="{{ old('oxygen_saturation', $encounter->triageRecord?->oxygen_saturation) }}" placeholder="e.g. 98" />
                        </div>
                        <div>
                            <label class="field-label">Systolic BP <span class="unit">mmHg</span></label>
                            <input type="number" name="systolic_bp" class="field-input"
                                   value="{{ old('systolic_bp', $encounter->triageRecord?->systolic_bp) }}" placeholder="e.g. 120" />
                        </div>
                        <div>
                            <label class="field-label">Diastolic BP <span class="unit">mmHg</span></label>
                            <input type="number" name="diastolic_bp" class="field-input"
                                   value="{{ old('diastolic_bp', $encounter->triageRecord?->diastolic_bp) }}" placeholder="e.g. 80" />
                        </div>
                        <div>
                            <label class="field-label">Blood Sugar <span class="unit">mmol/L</span></label>
                            <input type="number" step="0.1" name="blood_sugar" class="field-input"
                                   value="{{ old('blood_sugar', $encounter->triageRecord?->blood_sugar) }}" placeholder="e.g. 5.5" />
                        </div>
                        <div>
                            <label class="field-label">Pain Scale <span class="unit">0–10</span></label>
                            <input type="number" min="0" max="10" name="pain_scale" class="field-input"
                                   value="{{ old('pain_scale', $encounter->triageRecord?->pain_scale) }}" placeholder="0" />
                        </div>
                    </div>
                </div>

                {{-- Clinical notes --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Clinical Notes</h3>
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
                            <label class="field-label">Startup Medications</label>
                            <textarea name="startup_medications_notes" rows="2" class="field-input"
                                      placeholder="Any medications given before screening…">{{ old('startup_medications_notes', $encounter->triageRecord?->startup_medications_notes) }}</textarea>
                        </div>
                        <div>
                            <label class="field-label">Triage Notes</label>
                            <textarea name="triage_notes" rows="3" class="field-input"
                                      placeholder="Additional triage observations…">{{ old('triage_notes', $encounter->triageRecord?->triage_notes) }}</textarea>
                        </div>
                        <div>
                            <label class="field-label">Handover Note to Screening <span class="text-gray-400 font-normal text-xs">(optional)</span></label>
                            <textarea name="notes" rows="2" class="field-input"
                                      placeholder="Anything the screening clinician should know…">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <button type="submit" class="btn-success">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                        Save &amp; Queue to Screening
                    </button>
                    <a href="{{ route('triage.queue') }}" class="btn-secondary">Back to Queue</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Right: Patient summary + audit ─────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Patient card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Patient</h2>
            </div>
            <div class="px-6 py-4 space-y-1 text-sm">
                <p class="font-semibold text-gray-900">{{ $encounter->patient->full_name }}</p>
                <p class="text-gray-500">{{ $encounter->patient->patient_id }}</p>
                <p class="text-gray-500">
                    {{ ucfirst($encounter->patient->gender ?? '—') }}
                    @if($encounter->patient->date_of_birth)
                     · DOB: {{ $encounter->patient->date_of_birth->format('d M Y') }}
                    @endif
                </p>
                <p class="text-gray-500">Phone: {{ $encounter->patient->phone_number ?? '—' }}</p>
                @if($encounter->patient->allergies)
                <p class="text-red-600 font-medium mt-2">⚠ Allergies: {{ $encounter->patient->allergies }}</p>
                @endif
            </div>
        </div>

        {{-- Encounter info --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Encounter</h2>
            </div>
            <div class="px-6 py-4 space-y-2 text-sm">
                <div class="detail-row">
                    <span class="detail-label">Number</span>
                    <span class="font-mono font-semibold text-blue-700">{{ $encounter->encounter_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Visit Type</span>
                    <span>{{ $encounter->visit_type ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Priority</span>
                    <span>{{ ucfirst($encounter->priority_level ?? 'Normal') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Started</span>
                    <span>{{ $encounter->started_at->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>

        {{-- Activity log --}}
        @if($encounter->audits->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Activity Log</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($encounter->audits->sortByDesc('action_at') as $audit)
                <div class="px-6 py-3">
                    <p class="text-xs font-semibold text-gray-700">{{ str_replace('_', ' ', ucfirst($audit->action_name)) }}</p>
                    <p class="text-xs text-gray-400">{{ $audit->action_at->format('d M Y H:i') }} · {{ $audit->actionBy->name ?? '—' }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
