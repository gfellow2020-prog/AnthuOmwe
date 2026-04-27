@extends('layouts.dashboard')

@section('title', 'Encounter — ' . $encounter->encounter_number)

@push('styles')
<style>
    .detail-row { display:flex; gap:12px; padding:10px 0; border-bottom:1px solid #e5e5e5; }
    .detail-row:last-child { border-bottom:none; }
    .detail-label { flex-shrink:0; width:180px; font-size:12px; font-weight:600; color:#525252; text-transform:uppercase; letter-spacing:.04em; }
    .detail-value { font-size:14px; color:#171717; }
    .field-input { width:100%; padding:9px 13px; font-size:14px; border:1px solid #d4d4d4; border-radius:4px;
                   background:#fff; color:#171717; outline:none; transition:border-color .15s; }
    .field-input:focus { border-color:#171717; box-shadow:0 0 0 3px rgba(23,23,23,.08); }
    textarea.field-input { resize:vertical; min-height:70px; }
</style>
@endpush

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('registration.index') }}" class="hover:text-neutral-700 transition">Registration</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">{{ $encounter->encounter_number }}</span>
@endsection

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 rounded text-sm">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-neutral-100 dark:bg-neutral-800 border border-neutral-700 text-neutral-800 dark:text-neutral-200 rounded text-sm">
    {{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Left column: Patient + Encounter info ───────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Patient Card --}}
        <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center gap-3">
                <div class="w-8 h-8 bg-neutral-200 dark:bg-neutral-700 rounded flex items-center justify-center">
                    <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Patient</h2>
                    @if($encounter->registrationRecord)
                    <p class="text-xs text-neutral-500">
                        {{ $encounter->registrationRecord->was_existing_patient ? 'Existing patient' : 'Newly registered' }}
                    </p>
                    @endif
                </div>
            </div>
            <div class="px-6 py-4">
                <div class="detail-row">
                    <span class="detail-label">Full Name</span>
                    <span class="detail-value font-semibold">{{ $encounter->patient->full_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Patient Number</span>
                    <span class="detail-value font-mono text-neutral-700 dark:text-neutral-300">{{ $encounter->patient->patient_id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Gender</span>
                    <span class="detail-value">{{ ucfirst($encounter->patient->gender ?? '—') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date of Birth</span>
                    <span class="detail-value">{{ $encounter->patient->date_of_birth?->format('d M Y') ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">NRC</span>
                    <span class="detail-value">{{ $encounter->patient->nrc_number ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">{{ $encounter->patient->phone_number ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Encounter Details --}}
        <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center gap-3">
                <div class="w-8 h-8 bg-neutral-900 dark:bg-white rounded flex items-center justify-center">
                    <svg class="w-4 h-4 text-white dark:text-neutral-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Encounter</h2>
            </div>
            <div class="px-6 py-4">
                <div class="detail-row">
                    <span class="detail-label">Encounter Number</span>
                    <span class="detail-value font-mono font-semibold">{{ $encounter->encounter_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Current Stage</span>
                    <span class="detail-value">{{ $encounter->current_stage->label() }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="badge badge-{{ $encounter->current_status->value }}">{{ $encounter->current_status->label() }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Visit Type</span>
                    <span class="detail-value">{{ $encounter->visit_type ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Priority</span>
                    @if($encounter->priority_level)
                    <span class="badge badge-{{ $encounter->priority_level }}">{{ ucfirst($encounter->priority_level) }}</span>
                    @else
                    <span class="detail-value">Normal</span>
                    @endif
                </div>
                <div class="detail-row">
                    <span class="detail-label">Started At</span>
                    <span class="detail-value">{{ $encounter->started_at->format('d M Y H:i') }}</span>
                </div>
                @if($encounter->registrationRecord?->registration_notes)
                <div class="detail-row">
                    <span class="detail-label">Notes</span>
                    <span class="detail-value text-neutral-600">{{ $encounter->registrationRecord->registration_notes }}</span>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── Right column: Queue action ───────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Queue to Triage --}}
        @if($encounter->current_stage->value === 'registration' && in_array($encounter->current_status->value, ['started', 'in_progress']))
        <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center gap-3">
                <div class="w-8 h-8 bg-neutral-900 dark:bg-white rounded flex items-center justify-center">
                    <svg class="w-4 h-4 text-white dark:text-neutral-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Queue to Triage</h2>
            </div>
            <div class="p-6">
                <p class="text-sm text-neutral-500 mb-4">
                    Registration is complete. Send this patient to the Triage nurse.
                </p>
                <form method="POST" action="{{ route('encounters.queue.triage', $encounter) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Handover Notes <span class="text-neutral-500 font-normal">(optional)</span></label>
                        <textarea name="notes" rows="3" class="field-input"
                                  placeholder="Any information the triage nurse should know…">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                        Send to Triage Queue
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if($encounter->current_stage->value === 'triage')
        <div class="bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded px-6 py-5 text-sm text-neutral-800 dark:text-neutral-200">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="font-semibold">Queued to Triage</span>
            </div>
            <p class="text-neutral-600 dark:text-neutral-400">This encounter has been sent to the Triage queue and is awaiting a nurse.</p>
        </div>
        @endif

        {{-- Audit trail --}}
        @if($encounter->audits->isNotEmpty())
        <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
                <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">Activity Log</h2>
            </div>
            <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @foreach($encounter->audits->sortByDesc('action_at') as $audit)
                <div class="px-6 py-3">
                    <p class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">{{ str_replace('_', ' ', ucfirst($audit->action_name)) }}</p>
                    <p class="text-xs text-neutral-500">{{ $audit->action_at->format('d M Y H:i') }} · {{ $audit->actionBy->name ?? '—' }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
