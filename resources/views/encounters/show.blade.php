@extends('layouts.dashboard')

@section('title', 'Encounter Profile — ' . $encounter->encounter_number)

@push('styles')
<style>
    .section      { background:#fff;border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #e5e7eb;margin-bottom:1.5rem;overflow:hidden; }
    .section-hd   { padding:.85rem 1.5rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:.75rem; }
    .section-title{ font-weight:700;font-size:.875rem;color:#111827; }
    .section-bd   { padding:1.25rem 1.5rem; }
    .kv           { display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem 1.25rem; }
    .kv-item label{ display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em; }
    .kv-item span { display:block;font-size:13px;color:#111827;margin-top:2px; }
    .badge        { display:inline-flex;align-items:center;padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600; }
    .badge-green  { background:#f5f5f5;color:#404040;border:1px solid #d4d4d4; }
    .badge-blue   { background:#f5f5f5;color:#404040;border:1px solid #d4d4d4; }
    .badge-yellow { background:#f5f5f5;color:#404040;border:1px solid #d4d4d4; }
    .badge-red    { background:#fef2f2;color:#991b1b;border:1px solid #fecaca; }
    .badge-gray   { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    table         { width:100%;border-collapse:collapse; }
    th,td         { padding:7px 12px;text-align:left;font-size:12px; }
    th            { background:#f9fafb;font-weight:600;color:#374151;border-bottom:1px solid #e5e7eb; }
    td            { color:#374151;border-bottom:1px solid #f9fafb; }
    .pill         { display:inline-block;padding:1px 8px;border-radius:9999px;font-size:11px; }
    .stage-icon   { width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0; }
</style>
@endpush

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('encounters.index') }}" class="hover:text-neutral-700 transition">Encounters</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">{{ $encounter->encounter_number }}</span>
@endsection

@section('content')

{{-- ── 1. ENCOUNTER HEADER ───────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-gray-100">&#128203;</div>
        <span class="section-title">Encounter Header</span>
    </div>
    <div class="section-bd">
        <div class="kv">
            <div class="kv-item"><label>Encounter #</label><span class="font-mono">{{ $encounter->encounter_number }}</span></div>
            <div class="kv-item"><label>Current Stage</label><span>{{ ucfirst(str_replace('_',' ',$encounter->current_stage?->value ?? '—')) }}</span></div>
            <div class="kv-item"><label>Current Status</label><span>{{ ucfirst(str_replace('_',' ',$encounter->current_status?->value ?? '—')) }}</span></div>
            <div class="kv-item"><label>Visit Type</label><span>{{ $encounter->visit_type ?? '—' }}</span></div>
            <div class="kv-item"><label>Priority</label><span>{{ $encounter->priority_level ?? '—' }}</span></div>
            <div class="kv-item"><label>Started By</label><span>{{ $encounter->startedBy?->name ?? '—' }}</span></div>
            <div class="kv-item"><label>Started At</label><span>{{ $encounter->started_at?->format('d M Y H:i') ?? '—' }}</span></div>
            <div class="kv-item"><label>Closed At</label><span>{{ $encounter->closed_at?->format('d M Y H:i') ?? '—' }}</span></div>
            <div class="kv-item"><label>Closed By</label><span>{{ $encounter->closedBy?->name ?? '—' }}</span></div>
            @if($encounter->closure_notes)
            <div class="kv-item" style="grid-column:1/-1"><label>Closure Notes</label><span>{{ $encounter->closure_notes }}</span></div>
            @endif
        </div>
    </div>
</div>

{{-- ── 2. PATIENT DEMOGRAPHICS ──────────────────────────────────────────── --}}
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-neutral-100">&#128100;</div>
        <span class="section-title">Patient Demographics</span>
    </div>
    <div class="section-bd">
        <div class="kv">
            <div class="kv-item"><label>Full Name</label><span>{{ $encounter->patient->full_name }}</span></div>
            <div class="kv-item"><label>Patient ID</label><span class="font-mono">{{ $encounter->patient->patient_id }}</span></div>
            <div class="kv-item"><label>Gender</label><span>{{ ucfirst($encounter->patient->gender ?? '—') }}</span></div>
            <div class="kv-item"><label>Date of Birth</label><span>{{ $encounter->patient->date_of_birth ? \Carbon\Carbon::parse($encounter->patient->date_of_birth)->format('d M Y') : '—' }}</span></div>
            <div class="kv-item"><label>Phone</label><span>{{ $encounter->patient->phone ?? '—' }}</span></div>
            <div class="kv-item"><label>NRC</label><span>{{ $encounter->patient->nrc ?? '—' }}</span></div>
        </div>
    </div>
</div>

{{-- ── 3. REGISTRATION ──────────────────────────────────────────────────── --}}
@if($encounter->registrationRecord)
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-neutral-100">&#128221;</div>
        <span class="section-title">Registration</span>
    </div>
    <div class="section-bd">
        <div class="kv">
            <div class="kv-item"><label>Registrar</label><span>{{ $encounter->registrationRecord->registrar?->name ?? '—' }}</span></div>
            <div class="kv-item"><label>Existing Patient</label><span>{{ $encounter->registrationRecord->was_existing_patient ? 'Yes' : 'No' }}</span></div>
            <div class="kv-item"><label>Registered At</label><span>{{ \Carbon\Carbon::parse($encounter->registrationRecord->registered_at)->format('d M Y H:i') }}</span></div>
            @if($encounter->registrationRecord->registration_notes)
            <div class="kv-item" style="grid-column:1/-1"><label>Notes</label><span>{{ $encounter->registrationRecord->registration_notes }}</span></div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── 4. TRIAGE ────────────────────────────────────────────────────────── --}}
@if($encounter->triageRecord)
@php $t = $encounter->triageRecord; @endphp
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-neutral-100">&#129760;</div>
        <span class="section-title">Triage</span>
    </div>
    <div class="section-bd">
        <div class="kv">
            <div class="kv-item"><label>Nurse</label><span>{{ $t->nurse?->name ?? '—' }}</span></div>
            <div class="kv-item"><label>Triaged At</label><span>{{ \Carbon\Carbon::parse($t->triage_at)->format('d M Y H:i') }}</span></div>
            <div class="kv-item"><label>Weight</label><span>{{ $t->weight ? $t->weight.' kg' : '—' }}</span></div>
            <div class="kv-item"><label>Height</label><span>{{ $t->height ? $t->height.' cm' : '—' }}</span></div>
            <div class="kv-item"><label>BMI</label><span>{{ $t->bmi ?? '—' }}</span></div>
            <div class="kv-item"><label>Temperature</label><span>{{ $t->temperature ? $t->temperature.' °C' : '—' }}</span></div>
            <div class="kv-item"><label>Pulse</label><span>{{ $t->pulse ? $t->pulse.' bpm' : '—' }}</span></div>
            <div class="kv-item"><label>Blood Pressure</label><span>{{ ($t->systolic_bp && $t->diastolic_bp) ? $t->systolic_bp.'/'.$t->diastolic_bp.' mmHg' : '—' }}</span></div>
            <div class="kv-item"><label>O₂ Saturation</label><span>{{ $t->oxygen_saturation ? $t->oxygen_saturation.'%' : '—' }}</span></div>
            <div class="kv-item"><label>Blood Sugar</label><span>{{ $t->blood_sugar ? $t->blood_sugar.' mmol/L' : '—' }}</span></div>
            <div class="kv-item"><label>Pain Scale</label><span>{{ $t->pain_scale ?? '—' }}</span></div>
            @if($t->chief_complaint_brief)
            <div class="kv-item" style="grid-column:1/-1"><label>Chief Complaint</label><span>{{ $t->chief_complaint_brief }}</span></div>
            @endif
            @if($t->triage_notes)
            <div class="kv-item" style="grid-column:1/-1"><label>Triage Notes</label><span>{{ $t->triage_notes }}</span></div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── 5. INITIAL SCREENING ─────────────────────────────────────────────── --}}
@if($encounter->screeningRecord)
@php $sr = $encounter->screeningRecord; @endphp
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-neutral-100">&#128203;</div>
        <span class="section-title">Initial Screening</span>
        <span class="ml-2 badge badge-blue">{{ $sr->screening_type }}</span>
    </div>
    <div class="section-bd">
        <div class="kv">
            <div class="kv-item"><label>Clinician</label><span>{{ $sr->clinician?->name ?? '—' }}</span></div>
            <div class="kv-item"><label>Lab Requested</label><span>{{ $sr->lab_requested ? 'Yes' : 'No' }}</span></div>
            <div class="kv-item"><label>Prescribed</label><span>{{ $sr->prescribed ? 'Yes' : 'No' }}</span></div>
            @if($sr->complaints)
            <div class="kv-item" style="grid-column:1/-1"><label>Complaints</label><span>{{ $sr->complaints }}</span></div>
            @endif
            @if($sr->provisional_diagnosis)
            <div class="kv-item" style="grid-column:1/-1"><label>Provisional Diagnosis</label><span>{{ $sr->provisional_diagnosis }}</span></div>
            @endif
            @if($sr->clinical_findings)
            <div class="kv-item" style="grid-column:1/-1"><label>Clinical Findings</label><span>{{ $sr->clinical_findings }}</span></div>
            @endif
            @if($sr->plan)
            <div class="kv-item" style="grid-column:1/-1"><label>Plan</label><span>{{ $sr->plan }}</span></div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── 6. LAB ───────────────────────────────────────────────────────────── --}}
@if($encounter->labRequest)
@php $lab = $encounter->labRequest; @endphp
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-pink-100">&#129514;</div>
        <span class="section-title">Lab Request — {{ $lab->request_number }}</span>
        <span class="ml-2 badge badge-{{ $lab->isCompleted() ? 'green' : 'yellow' }}">{{ $lab->status }}</span>
    </div>
    <div class="section-bd">
        <div class="kv mb-4">
            <div class="kv-item"><label>Requested By</label><span>{{ $lab->requestedBy?->name ?? '—' }}</span></div>
            <div class="kv-item"><label>Priority</label><span>{{ $lab->priority_level ?? 'Normal' }}</span></div>
            <div class="kv-item"><label>Requested At</label><span>{{ \Carbon\Carbon::parse($lab->requested_at)->format('d M Y H:i') }}</span></div>
            <div class="kv-item"><label>Completed At</label><span>{{ $lab->completed_at ? \Carbon\Carbon::parse($lab->completed_at)->format('d M Y H:i') : '—' }}</span></div>
        </div>

        @if($lab->samples->isNotEmpty())
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Samples</p>
        <table class="mb-4">
            <thead><tr><th>Sample Type</th><th>Label</th><th>Collected By</th><th>Collected At</th></tr></thead>
            <tbody>
                @foreach($lab->samples as $s)
                <tr>
                    <td>{{ $s->sample_type }}</td>
                    <td>{{ $s->sample_label ?? '—' }}</td>
                    <td>{{ $s->collectedBy?->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($s->collected_at)->format('d M Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($lab->results->isNotEmpty())
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Results</p>
        <table>
            <thead><tr><th>Result Value</th><th>Interpretation</th><th>Status</th><th>Recorded By</th></tr></thead>
            <tbody>
                @foreach($lab->results as $r)
                <tr>
                    <td class="font-medium">{{ $r->result_value ?? $r->result_text ?? '—' }}</td>
                    <td>
                        @if($r->interpretation)
                        <span class="pill {{ $r->isAbnormal() ? 'bg-neutral-900 text-white' : 'bg-neutral-100 text-neutral-700' }}">{{ $r->interpretation }}</span>
                        @else —@endif
                    </td>
                    <td>{{ $r->result_status }}</td>
                    <td>{{ $r->recordedBy?->name ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endif

{{-- ── 7. SCREENING REVIEW ──────────────────────────────────────────────── --}}
@if($encounter->screeningReviewRecord)
@php $rv = $encounter->screeningReviewRecord; @endphp
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-neutral-200">&#128203;</div>
        <span class="section-title">Screening Review (Post-Lab)</span>
        <span class="ml-2 badge badge-green">{{ $rv->screening_type }}</span>
    </div>
    <div class="section-bd">
        <div class="kv">
            <div class="kv-item"><label>Clinician</label><span>{{ $rv->clinician?->name ?? '—' }}</span></div>
            <div class="kv-item"><label>Prescribed</label><span>{{ $rv->prescribed ? 'Yes' : 'No' }}</span></div>
            @if($rv->final_diagnosis)
            <div class="kv-item" style="grid-column:1/-1"><label>Final Diagnosis</label><span class="font-semibold">{{ $rv->final_diagnosis }}</span></div>
            @endif
            @if($rv->review_notes)
            <div class="kv-item" style="grid-column:1/-1"><label>Review Notes</label><span>{{ $rv->review_notes }}</span></div>
            @endif
            @if($rv->plan)
            <div class="kv-item" style="grid-column:1/-1"><label>Plan</label><span>{{ $rv->plan }}</span></div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── 8. PRESCRIPTION ──────────────────────────────────────────────────── --}}
@if($encounter->prescription)
@php $rx = $encounter->prescription; @endphp
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-neutral-100">&#128138;</div>
        <span class="section-title">Prescription — {{ $rx->prescription_number }}</span>
        <span class="ml-2 badge badge-{{ $rx->isDispensed() ? 'green' : ($rx->isActive() ? 'blue' : 'gray') }}">{{ $rx->status }}</span>
    </div>
    <div class="section-bd">
        <div class="kv mb-4">
            <div class="kv-item"><label>Prescribed By</label><span>{{ $rx->prescribedBy?->name ?? '—' }}</span></div>
            <div class="kv-item"><label>Prescribed At</label><span>{{ \Carbon\Carbon::parse($rx->prescribed_at)->format('d M Y H:i') }}</span></div>
        </div>
        @if($rx->items->isNotEmpty())
        <table>
            <thead><tr><th>Drug</th><th>Dose</th><th>Frequency</th><th>Duration</th><th>Qty</th><th>Route</th></tr></thead>
            <tbody>
                @foreach($rx->items as $item)
                <tr>
                    <td class="font-medium">{{ $item->drug_name }}</td>
                    <td>{{ $item->dose }}</td>
                    <td>{{ $item->frequency }}</td>
                    <td>{{ $item->duration }}</td>
                    <td>{{ $item->quantity_prescribed }}</td>
                    <td>{{ $item->route ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endif

{{-- ── 9. DISPENSE ──────────────────────────────────────────────────────── --}}
@if($encounter->dispense)
@php $d = $encounter->dispense; @endphp
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-neutral-100">&#128138;</div>
        <span class="section-title">Dispensing Record</span>
        <span class="ml-2 badge badge-green">dispensed</span>
    </div>
    <div class="section-bd">
        <div class="kv mb-4">
            <div class="kv-item"><label>Dispensed By</label><span>{{ $d->dispensedBy?->name ?? '—' }}</span></div>
            <div class="kv-item"><label>Dispensed At</label><span>{{ $d->dispensed_at?->format('d M Y H:i') ?? '—' }}</span></div>
            @if($d->counseling_notes)
            <div class="kv-item" style="grid-column:1/-1"><label>Counseling Notes</label><span>{{ $d->counseling_notes }}</span></div>
            @endif
        </div>
        @if($d->items->isNotEmpty())
        <table>
            <thead><tr><th>Drug</th><th>Qty Dispensed</th><th>Batch No</th><th>Instructions</th></tr></thead>
            <tbody>
                @foreach($d->items as $item)
                <tr>
                    <td class="font-medium">{{ $item->drug_name }}</td>
                    <td>{{ $item->quantity_dispensed }}</td>
                    <td>{{ $item->batch_no ?? '—' }}</td>
                    <td>{{ $item->instructions ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endif

{{-- ── 10. QUEUE TRANSITION TIMELINE ───────────────────────────────────── --}}
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-neutral-100">&#8594;</div>
        <span class="section-title">Queue Transitions</span>
        <span class="ml-auto badge badge-gray">{{ $encounter->queueTransitions->count() }}</span>
    </div>
    <div class="section-bd p-0">
        @if($encounter->queueTransitions->isNotEmpty())
        <table>
            <thead><tr><th>From</th><th>To</th><th>Status</th><th>Queued By</th><th>Queued At</th><th>Received By</th><th>Received At</th></tr></thead>
            <tbody>
                @foreach($encounter->queueTransitions->sortBy('queued_at') as $tr)
                <tr>
                    <td>{{ $tr->from_stage ? ucfirst(str_replace('_',' ',$tr->from_stage)) : '—' }}</td>
                    <td class="font-medium">{{ ucfirst(str_replace('_',' ',$tr->to_stage)) }}</td>
                    <td><span class="badge badge-{{ $tr->status === 'completed' ? 'green' : ($tr->status === 'received' ? 'blue' : 'yellow') }}">{{ $tr->status }}</span></td>
                    <td>{{ $tr->queuedBy?->name ?? '—' }}</td>
                    <td class="text-xs">{{ $tr->queued_at ? \Carbon\Carbon::parse($tr->queued_at)->format('d M H:i') : '—' }}</td>
                    <td>{{ $tr->receivedBy?->name ?? '—' }}</td>
                    <td class="text-xs">{{ $tr->received_at ? \Carbon\Carbon::parse($tr->received_at)->format('d M H:i') : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="px-6 py-4 text-sm text-gray-400">No queue transitions recorded.</p>
        @endif
    </div>
</div>

{{-- ── 11. STAGE LOGS ───────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-gray-100">&#128337;</div>
        <span class="section-title">Stage Logs</span>
        <span class="ml-auto badge badge-gray">{{ $encounter->stageLogs->count() }}</span>
    </div>
    <div class="section-bd p-0">
        @if($encounter->stageLogs->isNotEmpty())
        <table>
            <thead><tr><th>Stage</th><th>Status</th><th>Started By</th><th>Started At</th><th>Completed By</th><th>Completed At</th><th>Notes</th></tr></thead>
            <tbody>
                @foreach($encounter->stageLogs->sortBy('started_at') as $log)
                <tr>
                    <td class="font-medium">{{ ucfirst(str_replace('_',' ',$log->stage_name)) }}</td>
                    <td><span class="badge badge-{{ $log->completed_at ? 'green' : 'blue' }}">{{ $log->completed_at ? 'completed' : 'open' }}</span></td>
                    <td>{{ $log->startedBy?->name ?? '—' }}</td>
                    <td class="text-xs">{{ $log->started_at ? \Carbon\Carbon::parse($log->started_at)->format('d M H:i') : '—' }}</td>
                    <td>{{ $log->completedBy?->name ?? '—' }}</td>
                    <td class="text-xs">{{ $log->completed_at ? \Carbon\Carbon::parse($log->completed_at)->format('d M H:i') : '—' }}</td>
                    <td class="text-xs text-gray-500">{{ $log->notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="px-6 py-4 text-sm text-gray-400">No stage logs recorded.</p>
        @endif
    </div>
</div>

{{-- ── 12. AUDIT LOG ────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-hd">
        <div class="stage-icon bg-neutral-200">&#128269;</div>
        <span class="section-title">Audit Trail</span>
        <span class="ml-auto badge badge-gray">{{ $encounter->audits->count() }}</span>
    </div>
    <div class="section-bd p-0">
        @if($encounter->audits->isNotEmpty())
        <table>
            <thead><tr><th>Action</th><th>Stage</th><th>By</th><th>At</th><th>Notes</th></tr></thead>
            <tbody>
                @foreach($encounter->audits->sortBy('action_at') as $audit)
                <tr>
                    <td class="font-mono text-xs">{{ $audit->action_name }}</td>
                    <td>{{ ucfirst(str_replace('_',' ',$audit->action_stage ?? '—')) }}</td>
                    <td>{{ $audit->actionBy?->name ?? '—' }}</td>
                    <td class="text-xs">{{ $audit->action_at ? \Carbon\Carbon::parse($audit->action_at)->format('d M H:i') : '—' }}</td>
                    <td class="text-xs text-gray-500">{{ $audit->notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="px-6 py-4 text-sm text-gray-400">No audit entries found.</p>
        @endif
    </div>
</div>

@endsection
