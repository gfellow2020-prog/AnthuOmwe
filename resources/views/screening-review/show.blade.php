@extends('layouts.dashboard')

@section('title', 'Screening Review — ' . $encounter->encounter_number)

@push('styles')
<style>
    .field-label  { display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px; }
    .field-input  { width:100%;padding:9px 13px;font-size:14px;border:1px solid #d1d5db;border-radius:8px;
                    background:#fff;color:#111827;outline:none;transition:border-color .15s; }
    .field-input:focus  { border-color:#525252;box-shadow:0 0 0 3px rgba(82,82,82,.12); }
    textarea.field-input{ resize:vertical;min-height:72px; }
    select.field-input  { appearance:none;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-repeat:no-repeat;background-position:right 10px center;background-size:20px; }
    .btn-submit   { display:inline-flex;align-items:center;gap:6px;padding:11px 28px;font-size:15px;font-weight:700;background:#171717;color:#fff;border-radius:10px;border:none;cursor:pointer; }
    .btn-submit:hover   { background:#262626; }
    .btn-secondary{ display:inline-flex;align-items:center;gap:6px;padding:10px 22px;font-size:14px;font-weight:600;background:#f5f5f5;color:#374151;border-radius:8px;border:1px solid #d4d4d4;cursor:pointer; }
    .card         { background:#fff;border:1px solid #e5e7eb;border-radius:16px; }
    .card-header  { padding:16px 24px;border-bottom:1px solid #f3f4f6;font-size:14px;font-weight:600;color:#374151;display:flex;align-items:center;gap:10px; }
    .card-body    { padding:20px 24px; }
    .section-label{ font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px; }
    .drug-row     { background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px;space-y:10px; }
    .result-pill  { display:inline-flex;align-items:center;padding:3px 12px;border-radius:9999px;font-size:12px;font-weight:600; }
    .rp-normal    { background:#f5f5f5;color:#404040; }
    .rp-abnormal  { background:#e5e5e5;color:#404040; }
    .rp-critical  { background:#fee2e2;color:#991b1b; }
</style>
@endpush

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('screening-review.queue') }}" class="hover:text-neutral-700 transition">Screening Review</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">{{ $encounter->encounter_number }}</span>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-neutral-100 border border-neutral-300 text-neutral-800 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-neutral-900 border border-neutral-700 text-white rounded-lg text-sm">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-neutral-900 border border-neutral-700 text-white rounded-lg text-sm">
    <ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Left: Review + Rx form ───────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        <form method="POST" action="{{ route('screening-review.complete', $encounter) }}"
              x-data="rxForm()" id="review-form">
            @csrf

            {{-- Final clinical review ---------------------------------- --}}
            <div class="card mb-6">
                <div class="card-header">
                    <div class="w-7 h-7 bg-neutral-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                        </svg>
                    </div>
                    Post-Lab Clinical Review
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <label class="field-label">Final Diagnosis <span class="text-neutral-600">*</span></label>
                        <textarea name="final_diagnosis" rows="2" class="field-input" required
                                  placeholder="Confirmed diagnosis after reviewing lab results…">{{ old('final_diagnosis') }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Clinical Findings</label>
                            <textarea name="clinical_findings" rows="3" class="field-input"
                                      placeholder="Findings from physical review…">{{ old('clinical_findings') }}</textarea>
                        </div>
                        <div>
                            <label class="field-label">Physical Examination</label>
                            <textarea name="physical_examination" rows="3" class="field-input"
                                      placeholder="Repeat or updated exam findings…">{{ old('physical_examination') }}</textarea>
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Assessment / Interpretation of Results</label>
                        <textarea name="assessment_notes" rows="3" class="field-input"
                                  placeholder="How do the lab results inform the diagnosis?">{{ old('assessment_notes') }}</textarea>
                    </div>
                    <div>
                        <label class="field-label">Treatment Plan</label>
                        <textarea name="plan" rows="2" class="field-input"
                                  placeholder="Planned interventions and follow-up…">{{ old('plan') }}</textarea>
                    </div>
                    <div>
                        <label class="field-label">Review Notes</label>
                        <textarea name="review_notes" rows="2" class="field-input"
                                  placeholder="Additional notes for pharmacy or records…">{{ old('review_notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Prescription ------------------------------------------- --}}
            <div class="card mb-6">
                <div class="card-header">
                    <div class="w-7 h-7 bg-neutral-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.155-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    Prescription
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <label class="field-label">Prescription Notes</label>
                        <input type="text" name="prescription_notes" class="field-input"
                               placeholder="General notes for pharmacy…" value="{{ old('prescription_notes') }}"/>
                    </div>

                    {{-- Drug rows --}}
                    <template x-for="(drug, index) in drugs" :key="index">
                        <div class="drug-row space-y-3">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-xs font-bold text-neutral-500 uppercase">Drug <span x-text="index+1"></span></p>
                                <button type="button" @click="drugs.splice(index,1)"
                                        class="text-xs text-neutral-500 hover:text-neutral-600" x-show="drugs.length > 1">Remove</button>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="field-label">Drug Name <span class="text-neutral-600">*</span></label>
                                    <input type="text" :name="`items[${index}][drug_name]`" x-model="drug.name"
                                           class="field-input" placeholder="e.g. Amoxicillin" required/>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="field-label">Strength</label>
                                        <input type="text" :name="`items[${index}][strength]`" x-model="drug.strength"
                                               class="field-input" placeholder="e.g. 500mg"/>
                                    </div>
                                    <div>
                                        <label class="field-label">Formulation</label>
                                        <select :name="`items[${index}][formulation]`" x-model="drug.form" class="field-input">
                                            <option value="">—</option>
                                            <option>Tablet</option><option>Capsule</option><option>Syrup</option>
                                            <option>Injection</option><option>Suspension</option><option>Drops</option><option>Cream</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-4 gap-3">
                                <div>
                                    <label class="field-label">Dose <span class="text-neutral-600">*</span></label>
                                    <input type="text" :name="`items[${index}][dose]`" x-model="drug.dose"
                                           class="field-input" placeholder="e.g. 1 tablet" required/>
                                </div>
                                <div>
                                    <label class="field-label">Frequency <span class="text-neutral-600">*</span></label>
                                    <input type="text" :name="`items[${index}][frequency]`" x-model="drug.freq"
                                           class="field-input" placeholder="TDS / BD / OD" required/>
                                </div>
                                <div>
                                    <label class="field-label">Duration <span class="text-neutral-600">*</span></label>
                                    <input type="text" :name="`items[${index}][duration]`" x-model="drug.dur"
                                           class="field-input" placeholder="5 days" required/>
                                </div>
                                <div>
                                    <label class="field-label">Quantity <span class="text-neutral-600">*</span></label>
                                    <input type="number" :name="`items[${index}][quantity_prescribed]`" x-model="drug.qty"
                                           class="field-input" placeholder="15" min="1" required/>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="field-label">Route</label>
                                    <select :name="`items[${index}][route]`" x-model="drug.route" class="field-input">
                                        <option value="">—</option>
                                        <option>Oral</option><option>IV</option><option>IM</option>
                                        <option>Topical</option><option>Sublingual</option><option>Inhaled</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="field-label">Special Instructions</label>
                                    <input type="text" :name="`items[${index}][instructions]`" x-model="drug.note"
                                           class="field-input" placeholder="e.g. Take after food"/>
                                </div>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="drugs.push({name:'',strength:'',form:'',dose:'',freq:'',dur:'',qty:'',route:'',note:''})"
                            class="text-sm text-neutral-600 font-semibold hover:underline">+ Add Drug</button>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-4">
                <button type="submit" class="btn-submit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                    Save Review &amp; Queue to Pharmacy
                </button>
                <a href="{{ route('screening-review.queue') }}" class="btn-secondary">Back</a>
            </div>
        </form>

    </div>

    {{-- ── Right sidebar ───────────────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Patient --}}
        <div class="card">
            <div class="card-header">Patient</div>
            <div class="card-body text-sm space-y-1">
                <p class="font-semibold text-neutral-900">{{ $encounter->patient->full_name }}</p>
                <p class="text-neutral-500">{{ $encounter->patient->patient_id }}</p>
                <p class="text-neutral-500">{{ ucfirst($encounter->patient->gender ?? '—') }}</p>
                @if($encounter->patient->allergies)
                <p class="text-neutral-600 font-semibold mt-2">⚠ {{ $encounter->patient->allergies }}</p>
                @endif
            </div>
        </div>

        {{-- Triage Vitals --}}
        @if($encounter->triageRecord)
        <div class="card">
            <div class="card-header">Triage Vitals</div>
            <div class="card-body text-sm space-y-1">
                @if($encounter->triageRecord->blood_pressure_systolic)
                <p><span class="font-semibold text-neutral-600">BP: </span>{{ $encounter->triageRecord->blood_pressure_systolic }}/{{ $encounter->triageRecord->blood_pressure_diastolic }} mmHg</p>
                @endif
                @if($encounter->triageRecord->temperature)<p><span class="font-semibold text-neutral-600">Temp: </span>{{ $encounter->triageRecord->temperature }}°C</p>@endif
                @if($encounter->triageRecord->pulse_rate)<p><span class="font-semibold text-neutral-600">Pulse: </span>{{ $encounter->triageRecord->pulse_rate }} bpm</p>@endif
                @if($encounter->triageRecord->weight)<p><span class="font-semibold text-neutral-600">Weight: </span>{{ $encounter->triageRecord->weight }} kg</p>@endif
            </div>
        </div>
        @endif

        {{-- Initial Screening --}}
        @if($encounter->screeningRecord)
        <div class="card">
            <div class="card-header">Initial Screening</div>
            <div class="card-body text-sm space-y-2">
                @if($encounter->screeningRecord->complaints)
                <p><span class="font-semibold text-neutral-600">Complaints: </span>{{ $encounter->screeningRecord->complaints }}</p>
                @endif
                @if($encounter->screeningRecord->provisional_diagnosis)
                <p><span class="font-semibold text-neutral-600">Provisional Dx: </span>{{ $encounter->screeningRecord->provisional_diagnosis }}</p>
                @endif
                @if($encounter->screeningRecord->plan)
                <p><span class="font-semibold text-neutral-600">Plan: </span>{{ $encounter->screeningRecord->plan }}</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Lab Results --}}
        @if($encounter->labRequest && $encounter->labRequest->results->isNotEmpty())
        <div class="card">
            <div class="card-header">Lab Results ({{ $encounter->labRequest->request_number }})</div>
            <div class="card-body space-y-2">
                @foreach($encounter->labRequest->results as $res)
                <div class="border border-neutral-100 rounded-lg px-3 py-2 text-sm">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($res->labRequestItem)<span class="font-semibold text-neutral-700">{{ $res->labRequestItem->test_name }}</span>@endif
                        @if($res->result_value)<span class="font-semibold text-neutral-700">{{ $res->result_value }}</span>@endif
                        @if($res->reference_range)<span class="text-neutral-400 text-xs">Ref: {{ $res->reference_range }}</span>@endif
                        @if($res->interpretation)
                        <span class="result-pill rp-{{ $res->interpretation }}">{{ ucfirst($res->interpretation) }}</span>
                        @endif
                    </div>
                    @if($res->result_text)<p class="text-neutral-500 mt-1 text-xs">{{ $res->result_text }}</p>@endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Activity --}}
        @if($encounter->audits->isNotEmpty())
        <div class="card">
            <div class="card-header">Activity</div>
            <div class="divide-y divide-neutral-50">
                @foreach($encounter->audits->sortByDesc('action_at')->take(8) as $audit)
                <div class="px-5 py-2">
                    <p class="text-xs font-semibold text-neutral-700">{{ str_replace('_',' ',ucfirst($audit->action_name)) }}</p>
                    <p class="text-xs text-neutral-400">{{ $audit->action_at->format('d M H:i') }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

</div>

@push('scripts')
<script>
function rxForm() {
    return {
        drugs: [{ name:'', strength:'', form:'', dose:'', freq:'', dur:'', qty:'', route:'', note:'' }]
    };
}
</script>
@endpush

@endsection
