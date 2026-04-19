@extends('layouts.dashboard')

@section('title', 'Registration Desk — Anthu Omwe Health Center')

@push('styles')
<style>
    .field-label  { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px; }
    .field-label .req { color:#dc2626; margin-left:2px; }
    .field-input  { width:100%; padding:9px 13px; font-size:14px; border:1px solid #d1d5db; border-radius:8px;
                    background:#fff; color:#111827; outline:none; transition:border-color .15s,box-shadow .15s; }
    .field-input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
    .field-input::placeholder { color:#9ca3af; }
    select.field-input { appearance:auto; }
    .btn-primary   { display:inline-flex; align-items:center; gap:6px; padding:9px 20px; font-size:14px;
                     font-weight:600; background:#2563eb; color:#fff; border-radius:8px; border:none;
                     cursor:pointer; transition:background .15s; }
    .btn-primary:hover   { background:#1d4ed8; }
    .btn-secondary { display:inline-flex; align-items:center; gap:6px; padding:9px 20px; font-size:14px;
                     font-weight:600; background:#f3f4f6; color:#374151; border-radius:8px; border:1px solid #d1d5db;
                     cursor:pointer; transition:background .15s; }
    .btn-secondary:hover { background:#e5e7eb; }
    .btn-danger    { display:inline-flex; align-items:center; gap:6px; padding:9px 20px; font-size:14px;
                     font-weight:600; background:#ef4444; color:#fff; border-radius:8px; border:none;
                     cursor:pointer; transition:background .15s; }
    .badge-queued   { background:#dbeafe; color:#1e40af; }
    .badge-started  { background:#dcfce7; color:#166534; }
    .badge-urgent   { background:#fef3c7; color:#92400e; }
    .badge-emergency{ background:#fee2e2; color:#991b1b; }
</style>
@endpush

@section('page-header')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Registration Desk</h1>
        <p class="text-sm text-gray-500 mt-0.5">Search patients, start encounters, and queue to Triage</p>
    </div>
    <div class="text-sm text-gray-500">{{ now()->format('D, d M Y') }}</div>
</div>
@endsection

@section('content')

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center gap-2">
    <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<div x-data="registrationDesk()" class="space-y-6">

    {{-- ─────────────────────────────────────────────────────────────
         PANEL 1 — PATIENT SEARCH
         ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                </svg>
            </div>
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Find Patient</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="field-label">Search by Name, NRC, Phone, or Patient Number</label>
                    <input type="text" x-model="searchQuery" @input.debounce.400ms="searchPatients"
                           class="field-input" placeholder="e.g. Jane Banda, 123456/10/1, +26097…" />
                </div>
                <div>
                    <label class="field-label">Date of Birth <span class="text-gray-400 font-normal">(optional — narrows name search)</span></label>
                    <input type="date" x-model="searchDob" @change="searchPatients" class="field-input" />
                </div>
            </div>

            {{-- Search Results --}}
            <div x-show="searching" class="mt-4 text-sm text-gray-500 flex items-center gap-2">
                <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"/>
                </svg>
                Searching…
            </div>

            <template x-if="searchResults.length > 0">
                <div class="mt-4">
                    <p class="text-xs text-gray-500 mb-2" x-text="`${searchResults.length} result(s) found`"></p>
                    <div class="divide-y divide-gray-100 border border-gray-200 rounded-xl overflow-hidden">
                        <template x-for="p in searchResults" :key="p.id">
                            <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition">
                                <div class="flex items-center gap-4">
                                    <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold text-sm"
                                         x-text="p.full_name.charAt(0)"></div>
                                    <div>
                                        <p class="font-semibold text-gray-900 text-sm" x-text="p.full_name"></p>
                                        <p class="text-xs text-gray-500" x-text="`${p.patient_id} · ${p.gender ?? '—'} · DOB: ${p.date_of_birth ?? '—'}`"></p>
                                        <p class="text-xs text-gray-400" x-text="`NRC: ${p.nrc_number ?? '—'} · Phone: ${p.phone_number ?? '—'}`"></p>
                                    </div>
                                </div>
                                <button type="button" @click="selectPatient(p)"
                                        class="btn-primary text-xs px-3 py-1.5">
                                    Start Encounter
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="searchPerformed && searchResults.length === 0 && !searching">
                <div class="mt-4 text-sm text-gray-500 bg-gray-50 rounded-lg px-4 py-3 flex items-center justify-between">
                    <span>No patients found for "<span x-text="searchQuery" class="font-medium"></span>"</span>
                    <button type="button" @click="showNewPatientForm = true; selectedPatient = null"
                            class="btn-secondary text-xs px-3 py-1.5">
                        Register New Patient
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────────────────────
         PANEL 2 — START ENCOUNTER FORM
         (shown after selecting existing patient OR choosing new)
         ─────────────────────────────────────────────────────────── --}}
    <template x-if="selectedPatient !== null || showNewPatientForm">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                        <span x-show="selectedPatient">Start Encounter — <span class="text-blue-600" x-text="selectedPatient?.full_name"></span></span>
                        <span x-show="showNewPatientForm && !selectedPatient">Register New Patient &amp; Start Encounter</span>
                    </h2>
                </div>
                <button type="button" @click="resetForm" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('encounters.start') }}" class="p-6 space-y-6" id="start-encounter-form">
                @csrf

                {{-- Hidden: existing patient id (if selected) --}}
                <input type="hidden" name="patient_id" x-bind:value="selectedPatient?.id ?? ''">
                <input type="hidden" name="search_reference" x-bind:value="searchQuery">

                {{-- ── New patient fields (only when no existing patient selected) --}}
                <div x-show="showNewPatientForm && !selectedPatient">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">New Patient Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="field-label">Full Name <span class="req">*</span></label>
                            <input type="text" name="full_name" class="field-input @error('full_name') border-red-400 @enderror"
                                   value="{{ old('full_name') }}" placeholder="First Middle Last" />
                            @error('full_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label">Gender</label>
                            <select name="gender" class="field-input">
                                <option value="">— Select —</option>
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="field-input" value="{{ old('date_of_birth') }}" />
                        </div>
                        <div>
                            <label class="field-label">NRC Number</label>
                            <input type="text" name="nrc_number" class="field-input" value="{{ old('nrc_number') }}" placeholder="123456/10/1" />
                        </div>
                        <div>
                            <label class="field-label">Phone Number</label>
                            <input type="text" name="phone_number" class="field-input" value="{{ old('phone_number') }}" placeholder="+260 97…" />
                        </div>
                    </div>
                </div>

                {{-- ── Encounter details ────────────────────────────────── --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Encounter Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Visit Type</label>
                            <select name="visit_type" class="field-input">
                                <option value="">— Select —</option>
                                <option value="OPD" {{ old('visit_type') === 'OPD' ? 'selected' : '' }}>OPD</option>
                                <option value="ANC" {{ old('visit_type') === 'ANC' ? 'selected' : '' }}>ANC</option>
                                <option value="Immunisation" {{ old('visit_type') === 'Immunisation' ? 'selected' : '' }}>Immunisation</option>
                                <option value="HIV Testing" {{ old('visit_type') === 'HIV Testing' ? 'selected' : '' }}>HIV Testing</option>
                                <option value="ART" {{ old('visit_type') === 'ART' ? 'selected' : '' }}>ART</option>
                                <option value="Other" {{ old('visit_type') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Priority Level</label>
                            <select name="priority_level" class="field-input @error('priority_level') border-red-400 @enderror">
                                <option value="normal" {{ old('priority_level', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="urgent" {{ old('priority_level') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                <option value="emergency" {{ old('priority_level') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Registration Notes</label>
                            <textarea name="registration_notes" rows="2" class="field-input"
                                      placeholder="Any relevant notes for this visit…">{{ old('registration_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Start Encounter
                    </button>
                    <button type="button" @click="resetForm" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </template>

    {{-- ─────────────────────────────────────────────────────────────
         PANEL 3 — TODAY'S ACTIVE ENCOUNTERS AT REGISTRATION
         ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Active at Registration</h2>
            <span class="ml-auto text-xs font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                {{ $activeEncounters->total() }}
            </span>
        </div>

        @if($activeEncounters->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400">
            No active encounters at registration. Start one using the search above.
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($activeEncounters as $enc)
            <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition">
                <div class="flex items-center gap-4">
                    <div class="w-9 h-9 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 font-bold text-sm">
                        {{ strtoupper(substr($enc->patient->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">{{ $enc->patient->full_name ?? 'Unknown' }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $enc->encounter_number }}
                            @if($enc->visit_type) · {{ $enc->visit_type }} @endif
                            · Started {{ $enc->started_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($enc->priority_level && $enc->priority_level !== 'normal')
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full badge-{{ $enc->priority_level }}">
                        {{ ucfirst($enc->priority_level) }}
                    </span>
                    @endif
                    <a href="{{ route('registration.encounter', $enc) }}"
                       class="btn-secondary text-xs px-3 py-1.5">
                        View / Queue to Triage
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @if($activeEncounters->hasPages())
        <div class="px-6 py-3 border-t border-gray-100">{{ $activeEncounters->links() }}</div>
        @endif
        @endif
    </div>

</div>

@push('scripts')
<script>
function registrationDesk() {
    return {
        searchQuery:     '',
        searchDob:       '',
        searchResults:   [],
        searching:       false,
        searchPerformed: false,
        selectedPatient: null,
        showNewPatientForm: false,

        async searchPatients() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                this.searchPerformed = false;
                return;
            }
            this.searching = true;
            this.searchPerformed = false;
            try {
                const params = new URLSearchParams({ q: this.searchQuery });
                if (this.searchDob) params.append('date_of_birth', this.searchDob);

                const res = await fetch(`{{ route('registration.search') }}?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.searchResults = data.patients ?? [];
            } catch (e) {
                this.searchResults = [];
            } finally {
                this.searching = false;
                this.searchPerformed = true;
            }
        },

        selectPatient(patient) {
            this.selectedPatient    = patient;
            this.showNewPatientForm = false;
            this.$nextTick(() => {
                document.getElementById('start-encounter-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },

        resetForm() {
            this.selectedPatient    = null;
            this.showNewPatientForm = false;
        },
    };
}
</script>
@endpush

@endsection
