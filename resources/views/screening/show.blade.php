@extends('layouts.dashboard')

@section('title', 'Screening — ' . $encounter->encounter_number)

@push('styles')
<style>
    .field-label  { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px; }
    .field-input  { width:100%; padding:9px 13px; font-size:14px; border:1px solid #d1d5db; border-radius:8px;
                    background:#fff; color:#111827; outline:none; transition:border-color .15s; }
    .field-input:focus  { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
    textarea.field-input { resize:vertical; min-height:80px; }
    .btn-primary  { display:inline-flex; align-items:center; gap:6px; padding:9px 22px; font-size:14px;
                    font-weight:600; background:#2563eb; color:#fff; border-radius:8px; border:none; cursor:pointer; }
    .btn-primary:hover  { background:#1d4ed8; }
    .btn-green    { display:inline-flex; align-items:center; gap:6px; padding:9px 22px; font-size:14px;
                    font-weight:600; background:#16a34a; color:#fff; border-radius:8px; border:none; cursor:pointer; }
    .btn-green:hover    { background:#15803d; }
    .btn-secondary{ display:inline-flex; align-items:center; gap:6px; padding:9px 20px; font-size:14px;
                    font-weight:600; background:#f3f4f6; color:#374151; border-radius:8px; border:1px solid #d1d5db; cursor:pointer; }
    .section-title{ font-size:12px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:12px; }
    .vital-pill   { display:inline-flex; flex-direction:column; background:#f0f9ff; border:1px solid #bae6fd;
                    border-radius:10px; padding:8px 14px; min-width:80px; }
    .vital-val    { font-size:18px; font-weight:700; color:#0369a1; line-height:1; }
    .vital-lbl    { font-size:10px; color:#64748b; font-weight:500; margin-top:2px; }
    .detail-row   { display:flex; gap:12px; padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13px; }
    .detail-row:last-child { border-bottom:none; }
    .detail-label { flex-shrink:0; width:140px; font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; }
</style>
@endpush

@section('page-header')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('screening.queue') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Screening — {{ $encounter->encounter_number }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $encounter->patient->full_name }}</p>
        </div>
    </div>
    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
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
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
    <ul class="list-disc pl-4 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Left: Assessment form ────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Triage vitals summary ---------------------------------------- --}}
        @if($encounter->triageRecord)
        @php $t = $encounter->triageRecord; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-blue-100 p-5">
            <p class="section-title">Triage Vitals</p>
            <div class="flex flex-wrap gap-2">
                @foreach([
                    ['T', $t->temperature ? $t->temperature.'°C' : '—'],
                    ['BP', $t->bloodPressure()],
                    ['Pulse', $t->pulse ? $t->pulse.' bpm' : '—'],
                    ['RR', $t->respiratory_rate ? $t->respiratory_rate.'/min' : '—'],
                    ['SpO₂', $t->oxygen_saturation ? $t->oxygen_saturation.'%' : '—'],
                    ['BMI', $t->bmi ?? '—'],
                    ['Pain', $t->pain_scale !== null ? $t->pain_scale.'/10' : '—'],
                ] as [$lbl, $val])
                <div class="vital-pill">
                    <div class="vital-val text-sm">{{ $val }}</div>
                    <div class="vital-lbl">{{ $lbl }}</div>
                </div>
                @endforeach
            </div>
            @if($t->chief_complaint_brief)
            <p class="mt-3 text-sm text-gray-600"><span class="font-semibold">Chief Complaint:</span> {{ $t->chief_complaint_brief }}</p>
            @endif
        </div>
        @endif

        {{-- Clinical assessment form ------------------------------------- --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-gray-900">Clinical Assessment</h2>
            </div>

            <form method="POST" action="{{ route('screening.complete', $encounter) }}" class="p-6 space-y-5"
                  x-data="{ labRequested: {{ $encounter->screeningRecord?->lab_requested ? 'true' : 'false' }} }">
                @csrf

                {{-- History --}}
                <div>
                    <p class="section-title">History</p>
                    <div class="space-y-4">
                        <div>
                            <label class="field-label">Complaints</label>
                            <textarea name="complaints" rows="2" class="field-input">{{ old('complaints', $encounter->screeningRecord?->complaints) }}</textarea>
                        </div>
                        <div>
                            <label class="field-label">History of Presenting Illness</label>
                            <textarea name="history_of_presenting_illness" rows="3" class="field-input">{{ old('history_of_presenting_illness', $encounter->screeningRecord?->history_of_presenting_illness) }}</textarea>
                        </div>
                        <div>
                            <label class="field-label">Past Medical History</label>
                            <textarea name="past_medical_history" rows="2" class="field-input">{{ old('past_medical_history', $encounter->screeningRecord?->past_medical_history) }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Medication History</label>
                                <textarea name="medication_history" rows="2" class="field-input">{{ old('medication_history', $encounter->screeningRecord?->medication_history) }}</textarea>
                            </div>
                            <div>
                                <label class="field-label">Allergy History</label>
                                <textarea name="allergy_history" rows="2" class="field-input">{{ old('allergy_history', $encounter->screeningRecord?->allergy_history) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Examination --}}
                <div>
                    <p class="section-title">Examination & Assessment</p>
                    <div class="space-y-4">
                        <div>
                            <label class="field-label">Physical Examination</label>
                            <textarea name="physical_examination" rows="3" class="field-input">{{ old('physical_examination', $encounter->screeningRecord?->physical_examination) }}</textarea>
                        </div>
                        <div>
                            <label class="field-label">Clinical Findings</label>
                            <textarea name="clinical_findings" rows="3" class="field-input">{{ old('clinical_findings', $encounter->screeningRecord?->clinical_findings) }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Provisional Diagnosis</label>
                                <textarea name="provisional_diagnosis" rows="2" class="field-input">{{ old('provisional_diagnosis', $encounter->screeningRecord?->provisional_diagnosis) }}</textarea>
                            </div>
                            <div>
                                <label class="field-label">Final Diagnosis</label>
                                <textarea name="final_diagnosis" rows="2" class="field-input">{{ old('final_diagnosis', $encounter->screeningRecord?->final_diagnosis) }}</textarea>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Management Plan</label>
                            <textarea name="plan" rows="3" class="field-input">{{ old('plan', $encounter->screeningRecord?->plan) }}</textarea>
                        </div>
                        <div>
                            <label class="field-label">Assessment Notes</label>
                            <textarea name="assessment_notes" rows="2" class="field-input">{{ old('assessment_notes', $encounter->screeningRecord?->assessment_notes) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Lab / routing decision --}}
                <div class="bg-gray-50 rounded-xl p-4 space-y-4 border border-gray-200">
                    <p class="section-title">Next Step</p>

                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="lab_requested" value="1"
                               x-model="labRequested"
                               {{ old('lab_requested', $encounter->screeningRecord?->lab_requested) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-semibold text-gray-800">Request Lab Tests</span>
                    </label>

                    <div x-show="labRequested" x-cloak class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 text-sm text-blue-800">
                        Patient will be queued to <strong>Lab</strong> after submission.
                    </div>
                    <div x-show="!labRequested" x-cloak class="bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm text-green-800">
                        No lab required — patient will be queued directly to <strong>Pharmacy</strong>.
                    </div>

                    <div>
                        <label class="field-label">Handover Note <span class="font-normal text-gray-400 text-xs">(optional)</span></label>
                        <textarea name="notes" rows="2" class="field-input"
                                  placeholder="Anything the next department should know…">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
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
            </form>
        </div>
    </div>

    {{-- ── Right: Patient info + audit ─────────────────────────────────── --}}
    <div class="space-y-6">

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Patient</h2>
            </div>
            <div class="px-6 py-4 text-sm space-y-1">
                <p class="font-semibold text-gray-900">{{ $encounter->patient->full_name }}</p>
                <p class="text-gray-500">{{ $encounter->patient->patient_id }}</p>
                <p class="text-gray-500">
                    {{ ucfirst($encounter->patient->gender ?? '—') }}
                    @if($encounter->patient->date_of_birth)
                     · {{ $encounter->patient->date_of_birth->format('d M Y') }}
                    @endif
                </p>
                <p class="text-gray-500">Phone: {{ $encounter->patient->phone_number ?? '—' }}</p>
                @if($encounter->patient->allergies)
                <p class="text-red-600 font-medium mt-2">⚠ Allergies: {{ $encounter->patient->allergies }}</p>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Encounter</h2>
            </div>
            <div class="px-6 py-4 text-sm space-y-2">
                <div class="detail-row"><span class="detail-label">Number</span><span class="font-mono font-semibold text-blue-700">{{ $encounter->encounter_number }}</span></div>
                <div class="detail-row"><span class="detail-label">Visit Type</span><span>{{ $encounter->visit_type ?? '—' }}</span></div>
                <div class="detail-row"><span class="detail-label">Priority</span><span>{{ ucfirst($encounter->priority_level ?? 'Normal') }}</span></div>
                <div class="detail-row"><span class="detail-label">Started</span><span>{{ $encounter->started_at->format('d M Y H:i') }}</span></div>
            </div>
        </div>

        @if($encounter->audits->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Activity</h2>
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
