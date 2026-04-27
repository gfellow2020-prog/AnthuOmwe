@extends('layouts.dashboard')

@section('title', 'Registration Desk — Anthu Omwe Health Center')

@push('styles')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<style>
    .field-label  { display:block; font-size:13px; font-weight:600; color:#171717; margin-bottom:4px; }
    .field-label .req { color:#991b1b; margin-left:2px; }
    .search-row-label { min-height: 38px; display: flex; align-items: flex-end; }
    @media (max-width: 767px) {
        .search-row-label { min-height: 0; display: block; }
    }
    .field-input  { width:100%; padding:9px 13px; font-size:14px; border:1px solid #d4d4d4; border-radius:4px;
                    background:#fff; color:#171717; outline:none; transition:border-color .15s,box-shadow .15s; }
    .field-input:focus { border-color:#171717; box-shadow:0 0 0 3px rgba(23,23,23,.08); }
    .field-input::placeholder { color:#a3a3a3; }
    select.field-input { appearance:auto; }

    /* Uppercase the browser-rendered mm/dd/yyyy placeholder on date inputs */
    input[type="date"]::-webkit-datetime-edit-month-field,
    input[type="date"]::-webkit-datetime-edit-day-field,
    input[type="date"]::-webkit-datetime-edit-year-field { text-transform: uppercase; }

    /* Clean up html5-qrcode default styles */
    #barcode-reader-modal { background: #f5f5f5; }
    #barcode-reader-modal video { border-radius: 4px; }
    #barcode-reader-modal__dashboard { padding: 0 !important; }
    #barcode-reader-modal__dashboard_section_swaplink { color: #525252 !important; text-decoration: underline; font-size: 12px; }
</style>
@endpush

@section('breadcrumbs')
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Registration Desk</span>
@endsection

@section('content')

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 rounded text-sm flex items-center gap-2">
    <svg class="w-4 h-4 text-neutral-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<div x-data="registrationDesk()" class="space-y-6">

    {{-- ─────────────────────────────────────────────────────────────
         PANEL 1 — PATIENT SEARCH
         ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-neutral-200 dark:bg-neutral-700 rounded flex items-center justify-center">
                    <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-neutral-900 dark:text-white">Find Patient</h2>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                        @click="openNewPatientForm('household')"
                        class="btn-secondary text-xs px-3 py-1.5 whitespace-nowrap">
                    Add Household
                </button>
            </div>
        </div>

        <div class="p-6">
            {{-- Search Mode Tabs --}}
            <div class="flex items-center gap-1 mb-4 bg-neutral-900 dark:bg-neutral-800 rounded p-1">
                <template x-for="tab in tabs" :key="tab.key">
                    <button type="button" @click="switchTab(tab.key)"
                            :class="activeTab === tab.key
                                ? 'bg-neutral-700 text-white shadow-sm'
                                : 'text-neutral-400 hover:text-neutral-200'"
                            class="flex items-center gap-1.5 px-3.5 py-2 rounded text-xs font-semibold transition-all duration-200 flex-1 justify-center">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon"/>
                        </svg>
                        <span x-text="tab.label"></span>
                    </button>
                </template>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="field-label search-row-label" x-text="currentLabel()"></label>

                    {{-- Barcode Scanner --}}
                    <div x-show="activeTab === 'barcode'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <button type="button" @click="openScannerModal()"
                                class="btn-secondary text-xs px-3 py-2 mb-3">
                            Open Barcode Camera
                        </button>

                        {{-- Manual fallback input --}}
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            </span>
                            <input type="text" x-model="searchQuery"
                                   @input.debounce.300ms="searchPatients"
                                   x-ref="barcodeInput"
                                   class="field-input pl-10 font-mono tracking-wider"
                                   placeholder="Or type barcode manually…" />
                        </div>
                        <p class="text-[11px] text-neutral-400 mt-1" x-text="scannerStatus"></p>

                        {{-- Scanner modal --}}
                        <div x-show="showScannerModal"
                             x-transition.opacity
                             @keydown.escape.window="closeScannerModal()"
                             class="fixed inset-0 z-[9999] bg-black/50 flex items-center justify-center p-4"
                             style="display:none;"
                             @click.self="closeScannerModal()">
                            <div class="w-full max-w-3xl bg-white rounded-lg shadow-xl border border-neutral-200 overflow-hidden">
                                <div class="px-4 py-3 border-b border-neutral-200 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-neutral-900">Barcode Scanner</h3>
                                    <button type="button" @click="closeScannerModal()" class="text-neutral-500 hover:text-neutral-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="p-4">
                                    <div id="barcode-reader-modal" class="rounded overflow-hidden border border-neutral-200" style="min-height:300px;"></div>
                                    <p class="text-[11px] text-neutral-400 mt-2" x-text="scannerStatus"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Name Search --}}
                    <div x-show="activeTab === 'name'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <input type="text" x-model="searchQuery" @input.debounce.400ms="searchPatients"
                               class="field-input" placeholder="e.g. Jane Banda, John Mwale…" />
                    </div>

                    {{-- NRC Search --}}
                    <div x-show="activeTab === 'nrc'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="relative">
                            <input type="text"
                                   @input="formatNrc($event); searchPatients()"
                                   @keydown.backspace="handleNrcBackspace($event)"
                                   maxlength="11"
                                   class="field-input tracking-widest font-mono text-base"
                                   placeholder="000000/00/0" />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-neutral-400 uppercase tracking-wide pointer-events-none">NRC</span>
                        </div>
                        <p class="text-[11px] text-neutral-400 mt-1">Format: 123456/12/1 — slashes are added automatically</p>
                    </div>

                    {{-- Phone Search --}}
                    <div x-show="activeTab === 'phone'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <input type="tel" x-model="phoneLocal"
                               @input="formatPhone($event); searchQuery = phoneLocal.replace(/\s/g,''); $nextTick(() => searchPatients())"
                               maxlength="12"
                               class="field-input font-mono tracking-wider"
                               placeholder="97 1234567" />
                        <p class="text-[11px] text-neutral-400 mt-1">Enter phone digits only (no country code required)</p>
                    </div>

                    {{-- Patient Number Search --}}
                    <div x-show="activeTab === 'patient_no'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-neutral-400 pointer-events-none">PAT-</span>
                            <input type="text" x-model="searchQuery" @input.debounce.400ms="searchPatients"
                                   class="field-input pl-14 font-mono tracking-wider"
                                   placeholder="000001" />
                        </div>
                        <p class="text-[11px] text-neutral-400 mt-1">Enter the patient ID number</p>
                    </div>
                </div>
                <div x-show="activeTab === 'name'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <label class="field-label search-row-label">Sex <span class="text-neutral-500 font-normal">(optional)</span></label>
                    <select x-model="searchSex" @change="searchPatients" class="field-input">
                        <option value="">All</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div x-show="activeTab === 'name'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <label class="field-label search-row-label">Date of Birth <span class="text-neutral-500 font-normal">(optional — narrows name search)</span></label>
                    <input type="date" x-model="searchDob" @change="searchPatients" class="field-input" />
                </div>
            </div>

            {{-- Search Results --}}
            <div x-show="searching" class="mt-4 text-sm text-neutral-500 flex items-center gap-2">
                <svg class="w-4 h-4 animate-spin text-neutral-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"/>
                </svg>
                Searching…
            </div>

            <template x-if="visibleResults().length > 0">
                <div class="mt-4">
                    <p class="text-xs text-neutral-500 mb-2" x-text="resultCountLabel()"></p>
                    <div class="divide-y divide-neutral-200 dark:divide-neutral-700 border border-neutral-300 dark:border-neutral-700 rounded overflow-hidden">
                        <template x-for="p in visibleResults()" :key="p.id">
                            <div class="flex items-center justify-between px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                                <div class="flex items-center gap-4">
                                    <div class="w-9 h-9 bg-neutral-200 dark:bg-neutral-700 rounded-full flex items-center justify-center text-neutral-700 dark:text-neutral-300 font-bold text-sm"
                                         x-text="p.full_name.charAt(0)"></div>
                                    <div>
                                        <p class="font-semibold text-neutral-900 dark:text-neutral-100 text-sm" x-text="p.full_name"></p>
                                        <p class="text-xs text-neutral-500" x-text="`${p.patient_id} · ${p.gender ?? '—'} · DOB: ${p.date_of_birth ?? '—'}`"></p>
                                        <p class="text-xs text-neutral-500" x-text="`NRC: ${p.nrc_number ?? '—'} · Phone: ${p.phone_number ?? '—'}`"></p>
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
                <div class="mt-4 text-sm text-neutral-600 bg-neutral-100 dark:bg-neutral-800 rounded px-4 py-3 flex items-center justify-between">
                    <span>No patients found for "<span x-text="searchQuery" class="font-medium"></span>"</span>
                    <button type="button" @click="openNewPatientForm('patient')"
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
        <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-neutral-900 dark:bg-white rounded flex items-center justify-center">
                        <svg class="w-4 h-4 text-white dark:text-neutral-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-neutral-900 dark:text-white">
                        <span x-show="selectedPatient">Start Encounter — <span class="text-neutral-700 dark:text-neutral-300" x-text="selectedPatient?.full_name"></span></span>
                        <span x-show="showNewPatientForm && !selectedPatient && newPatientMode === 'patient'">Register New Patient &amp; Start Encounter</span>
                        <span x-show="showNewPatientForm && !selectedPatient && newPatientMode === 'household'">Register Household Leader &amp; Start Encounter</span>
                    </h2>
                </div>
                <button type="button" @click="resetForm" class="text-neutral-400 hover:text-neutral-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('encounters.start') }}" class="p-6 space-y-6" id="start-encounter-form" @submit.prevent="submitStartEncounter($event)">
                @csrf

                {{-- Hidden: existing patient id (if selected) --}}
                <input type="hidden" name="patient_id" x-bind:value="selectedPatient?.id ?? ''">
                <input type="hidden" name="search_reference" x-bind:value="searchQuery">
                <input type="hidden" name="create_household" x-bind:value="newPatientMode === 'household' ? '1' : '0'">
                <input type="hidden" name="payment_plan" x-bind:value="paymentPlan">
                <input type="hidden" name="payment_mode" x-bind:value="paymentMode">
                <input type="hidden" name="payment_amount" x-bind:value="paymentAmount">

                {{-- ── New patient fields (only when no existing patient selected) --}}
                <div x-show="showNewPatientForm && !selectedPatient">
                    <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-4 uppercase tracking-wide" x-text="newPatientMode === 'household' ? 'Household Leader Details' : 'New Patient Details'"></h3>
                    <p x-show="newPatientMode === 'household'" class="text-xs text-neutral-500 mb-4">
                        This patient will be saved as the household leader and a linked household record will be created.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2" x-show="newPatientMode === 'patient'">
                            <label class="field-label">Add to Household <span class="text-neutral-500 font-normal">(optional)</span></label>
                            <input type="hidden" name="household_id" x-model="selectedHouseholdId">
                            <div class="relative">
                                <input type="text"
                                       x-model="householdSearch"
                                       @input.debounce.250ms="searchHouseholds()"
                                       @focus="if (householdSearch.trim().length > 0) searchHouseholds()"
                                       @click.away="householdSuggestionOpen = false"
                                       class="field-input @error('household_id') border-neutral-700 @enderror"
                                       placeholder="Search households by head name or household ID" />
                                <div x-show="householdSuggestionOpen && householdSuggestions.length > 0"
                                     x-transition.opacity
                                     class="absolute z-20 mt-1 w-full overflow-hidden rounded border border-neutral-200 bg-white shadow-lg"
                                     style="display:none;">
                                    <template x-for="household in householdSuggestions" :key="household.id">
                                        <button type="button"
                                                @click="selectHousehold(household)"
                                                class="block w-full border-b border-neutral-100 px-3 py-2 text-left last:border-b-0 hover:bg-neutral-50">
                                            <span class="block text-sm font-medium text-neutral-900" x-text="household.name"></span>
                                            <span class="block text-xs text-neutral-500" x-text="household.id"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <p class="mt-1 text-[11px] text-neutral-400">Type to search. Up to three matches are shown.</p>
                            <p x-show="selectedHouseholdId" class="mt-1 text-xs text-neutral-600" style="display:none;">
                                Linked household: <span class="font-medium" x-text="selectedHouseholdLabel"></span>
                            </p>
                            @error('household_id')<p class="text-xs text-neutral-700 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Full Name <span class="req">*</span></label>
                            <input type="text" name="full_name" class="field-input @error('full_name') border-neutral-700 @enderror"
                                   value="{{ old('full_name') }}" placeholder="First Middle Last"
                                   @input="titleCaseName($event)" />
                            @error('full_name')<p class="text-xs text-neutral-700 mt-1">{{ $message }}</p>@enderror
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
                            <input type="text" name="nrc_number"
                                   class="field-input font-mono tracking-widest"
                                   value="{{ old('nrc_number') }}"
                                   placeholder="123456/10/1"
                                   maxlength="11"
                                   autocomplete="off"
                                   @input="applyNrcFormat($event)"
                                   @keydown.backspace="handleNrcFieldBackspace($event)" />
                            <p class="text-[11px] text-neutral-400 mt-1">Format: 6 digits / 2 digits / 1 digit — slashes are added automatically</p>
                        </div>
                        <div>
                            <label class="field-label">Phone Number</label>
                            <input type="text" name="phone_number" class="field-input" value="{{ old('phone_number') }}" placeholder="+260 97…" />
                        </div>
                        <div class="md:col-span-2" x-show="newPatientMode === 'household'">
                            <label class="field-label">Village <span class="req">*</span></label>
                            <div class="relative" @click.away="villageSuggestionOpen = false">
                                <input type="text"
                                       name="village"
                                       x-model="villageQuery"
                                       @focus="openVillageSuggestions()"
                                       @click="openVillageSuggestions()"
                                       @input="onVillageInput()"
                                       class="field-input @error('village') border-neutral-700 @enderror"
                                       placeholder="Search and select village"
                                       autocomplete="off" />

                                <div x-show="villageSuggestionOpen && filteredVillageSuggestions().length > 0"
                                     x-transition.opacity
                                     class="absolute z-20 mt-1 w-full overflow-hidden rounded border border-neutral-200 bg-white shadow-lg"
                                     style="display:none;">
                                    <template x-for="village in filteredVillageSuggestions()" :key="village">
                                        <button type="button"
                                                @click="selectVillage(village)"
                                                class="block w-full border-b border-neutral-100 px-3 py-2 text-left last:border-b-0 hover:bg-neutral-50">
                                            <span class="block text-sm text-neutral-900" x-text="village"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <p class="mt-1 text-[11px] text-neutral-400">Click to see 4 suggestions. Keep typing to refine matches.</p>
                            @error('village')<p class="text-xs text-neutral-700 mt-1">{{ $message }}</p>@enderror

                            {{-- Add Village --}}
                            <div class="mt-2">
                                <button type="button"
                                        x-show="!showAddVillage"
                                        @click="showAddVillage = true; $nextTick(() => $refs.newVillageInput.focus())"
                                        class="text-[11px] text-neutral-500 hover:text-neutral-800 underline underline-offset-2 transition-colors">
                                    + Add village
                                </button>

                                <div x-show="showAddVillage" x-transition style="display:none;">
                                    <div class="flex items-center gap-2 mt-1">
                                        <input type="text"
                                               x-ref="newVillageInput"
                                               x-model="newVillageName"
                                               @keydown.enter.prevent="saveNewVillage()"
                                               @keydown.escape="showAddVillage = false; newVillageName = ''; addVillageError = ''"
                                               class="field-input text-sm"
                                               placeholder="Type village name…"
                                               maxlength="100" />
                                        <button type="button"
                                                @click="saveNewVillage()"
                                                :disabled="addVillageLoading"
                                                class="btn-primary text-xs px-3 py-2 whitespace-nowrap"
                                                x-text="addVillageLoading ? 'Saving…' : 'Save'"></button>
                                        <button type="button"
                                                @click="showAddVillage = false; newVillageName = ''; addVillageError = ''"
                                                class="btn-secondary text-xs px-3 py-2">Cancel</button>
                                    </div>
                                    <p x-show="addVillageError" x-text="addVillageError" class="text-xs text-red-700 mt-1" style="display:none;"></p>
                                </div>
                            </div>

                            <div class="mt-3 rounded border border-neutral-200 bg-neutral-50 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-semibold text-neutral-700">Household Payment</p>
                                    <button type="button" @click="openPaymentModal(true)" class="btn-secondary text-xs px-3 py-1.5">Set Payment</button>
                                </div>
                                <p class="mt-1 text-[11px] text-neutral-500">Choose plan and payment mode after village selection.</p>
                                <p x-show="paymentPlan && paymentMode" class="mt-2 text-xs text-neutral-700" style="display:none;">
                                    Selected: <span class="font-medium" x-text="paymentPlanLabel()"></span>
                                    (<span x-text="'K' + paymentAmount"></span>) via
                                    <span class="font-medium" x-text="paymentModeLabel()"></span>
                                </p>
                                <p x-show="paymentError" x-text="paymentError" class="mt-2 text-xs text-red-700" style="display:none;"></p>
                            </div>

                            <div x-show="showPaymentModal"
                                 x-transition.opacity
                                 class="fixed inset-0 z-[9998] bg-black/50 flex items-center justify-center p-4"
                                 style="display:none;"
                                 @click.self="closePaymentModal()">
                                <div class="w-full max-w-md rounded border border-neutral-200 bg-white shadow-xl">
                                    <div class="px-4 py-3 border-b border-neutral-200 flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-neutral-900">Household Payment Details</h3>
                                        <button type="button" @click="closePaymentModal()" class="text-neutral-500 hover:text-neutral-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="p-4 space-y-4">
                                        <div>
                                            <label class="field-label">Choose Plan</label>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <button type="button" @click="setPaymentPlan('monthly')"
                                                        :class="paymentPlan === 'monthly' ? 'border-neutral-900 bg-neutral-100' : 'border-neutral-300 bg-white'"
                                                        class="rounded border px-3 py-2 text-left text-sm transition-colors">
                                                    Monthly Amount
                                                    <span class="block text-xs text-neutral-500 mt-0.5">K500</span>
                                                </button>
                                                <button type="button" @click="setPaymentPlan('annual')"
                                                        :class="paymentPlan === 'annual' ? 'border-neutral-900 bg-neutral-100' : 'border-neutral-300 bg-white'"
                                                        class="rounded border px-3 py-2 text-left text-sm transition-colors">
                                                    Annual Amount
                                                    <span class="block text-xs text-neutral-500 mt-0.5">K6000</span>
                                                </button>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="field-label">Payment Mode</label>
                                            <select x-model="paymentMode" class="field-input">
                                                <option value="">— Select payment mode —</option>
                                                <option value="cash">Cash</option>
                                                <option value="mobile_money">Mobile money</option>
                                            </select>
                                        </div>

                                        <p x-show="paymentError" x-text="paymentError" class="text-xs text-red-700" style="display:none;"></p>
                                    </div>
                                    <div class="px-4 py-3 border-t border-neutral-200 flex items-center justify-end gap-2">
                                        <button type="button" @click="closePaymentModal()" class="btn-secondary text-xs px-3 py-2">Cancel</button>
                                        <button type="button" @click="confirmPayment()" class="btn-primary text-xs px-3 py-2">Save Payment</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Encounter details ────────────────────────────────── --}}
                <div>
                    <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-4 uppercase tracking-wide">Encounter Details</h3>
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
                            <select name="priority_level" class="field-input @error('priority_level') border-neutral-700 @enderror">
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
    <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center gap-3">
            <div class="w-8 h-8 bg-neutral-200 dark:bg-neutral-700 rounded flex items-center justify-center">
                <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h2 class="text-base font-semibold text-neutral-900 dark:text-white">Active at Registration</h2>
            <span class="ml-auto text-xs font-semibold bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 px-2 py-0.5 rounded">
                {{ $activeEncounters->total() }}
            </span>
        </div>

        @if($activeEncounters->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-neutral-500">
            No active encounters at registration. Start one using the search above.
        </div>
        @else
        <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @foreach($activeEncounters as $enc)
            <div class="flex items-center justify-between px-6 py-4 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                <div class="flex items-center gap-4">
                    <div class="w-9 h-9 bg-neutral-200 dark:bg-neutral-700 rounded-full flex items-center justify-center text-neutral-700 dark:text-neutral-300 font-bold text-sm">
                        {{ strtoupper(substr($enc->patient->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-neutral-900 dark:text-neutral-100 text-sm">{{ $enc->patient->full_name ?? 'Unknown' }}</p>
                        <p class="text-xs text-neutral-500">
                            {{ $enc->encounter_number }}
                            @if($enc->visit_type) · {{ $enc->visit_type }} @endif
                            · Started {{ $enc->started_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($enc->priority_level && $enc->priority_level !== 'normal')
                    <span class="badge badge-{{ $enc->priority_level }}">
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
        <div class="px-6 py-3 border-t border-neutral-200 dark:border-neutral-700">{{ $activeEncounters->links() }}</div>
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
        searchSex:       '',
        searchResults:   [],
        searching:       false,
        searchPerformed: false,
        selectedPatient: null,
        showNewPatientForm: false,
        newPatientMode: 'patient',
        householdSearchUrl: @js(route('registration.households.search')),
        householdSuggestions: [],
        householdSearch: @js($selectedHouseholdOption
            ? (($selectedHouseholdOption->head_of_house ?: 'Unnamed Household') . ' (' . $selectedHouseholdOption->household_id . ')')
            : ''),
        selectedHouseholdId: @js(old('household_id', '')),
        selectedHouseholdLabel: @js($selectedHouseholdOption
            ? (($selectedHouseholdOption->head_of_house ?: 'Unnamed Household') . ' (' . $selectedHouseholdOption->household_id . ')')
            : ''),
        householdSuggestionOpen: false,
        villageOptions: @js($villages),
        villageQuery: @js(old('village', '')),
        villageSuggestionOpen: false,
        showAddVillage: false,
        newVillageName: '',
        addVillageError: '',
        addVillageLoading: false,
        addVillageUrl: @js(route('registration.villages.store')),
        showPaymentModal: false,
        paymentPlan: @js(old('payment_plan', '')),
        paymentMode: @js(old('payment_mode', '')),
        paymentAmount: @js(old('payment_amount', '')),
        paymentError: @js($errors->first('payment_plan') ?: $errors->first('payment_mode') ?: $errors->first('payment_amount')),

        // Search tabs
        activeTab: 'name',
        phoneLocal: '',
        showScannerModal: false,
        scannerStatus: 'Initialising camera…',
        _scanner: null,
        tabs: [
            { key: 'name',       label: 'Name',       icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
            { key: 'barcode',    label: 'Barcode',     icon: 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z' },
            { key: 'nrc',        label: 'NRC',         icon: 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0' },
            { key: 'phone',      label: 'Phone',       icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z' },
            { key: 'patient_no', label: 'Patient No.', icon: 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14' },
        ],

        switchTab(key) {
            // Stop scanner when leaving barcode tab
            if (this.activeTab === 'barcode' && key !== 'barcode') {
                this.closeScannerModal();
            }
            this.activeTab = key;
            this.phoneLocal = '';
            this.searchQuery = '';
            this.searchDob = '';
            this.searchSex = '';
            this.searchResults = [];
            this.searchPerformed = false;
            // Open scanner modal when entering barcode tab
            if (key === 'barcode') {
                this.$nextTick(() => this.openScannerModal());
            } else {
                this.$nextTick(() => {
                    const panel = this.$el.querySelector('[x-show*="activeTab"][x-show*="' + key + '"]');
                    if (panel) {
                        const input = panel.querySelector('input');
                        if (input) input.focus();
                    }
                });
            }
        },

        openScannerModal() {
            this.showScannerModal = true;
            this.$nextTick(() => this.startScanner());
        },

        async closeScannerModal() {
            await this.stopScanner();
            this.showScannerModal = false;
            if (this.$refs.barcodeInput) {
                this.$refs.barcodeInput.focus();
            }
        },

        async startScanner() {
            if (this._scanner) return;
            if (!this.showScannerModal) return;
            if (typeof Html5Qrcode === 'undefined') {
                this.scannerStatus = 'Scanner library loading…';
                return;
            }
            this.scannerStatus = 'Initialising camera…';
            try {
                this._scanner = new Html5Qrcode('barcode-reader-modal');
                await this._scanner.start(
                    { facingMode: 'environment' },
                    {
                        fps: 10,
                        qrbox: { width: 300, height: 150 },
                        aspectRatio: 2.0,
                        formatsToSupport: [
                            Html5QrcodeSupportedFormats.CODE_128,
                            Html5QrcodeSupportedFormats.CODE_39,
                            Html5QrcodeSupportedFormats.EAN_13,
                            Html5QrcodeSupportedFormats.EAN_8,
                            Html5QrcodeSupportedFormats.QR_CODE,
                            Html5QrcodeSupportedFormats.CODE_93,
                        ],
                    },
                    (decodedText) => {
                        // Success — populate and search
                        this.searchQuery = decodedText;
                        this.scannerStatus = '✓ Scanned: ' + decodedText;
                        this.searchPatients();
                        this.closeScannerModal();
                    },
                    () => {} // ignore scan failures
                );
                this.scannerStatus = 'Point camera at the barcode';
            } catch (err) {
                this.scannerStatus = 'Camera unavailable — type barcode manually below';
                this._scanner = null;
            }
        },

        async stopScanner() {
            if (this._scanner) {
                try {
                    await this._scanner.stop();
                    this._scanner.clear();
                } catch(e) {}
                this._scanner = null;
            }
        },

        init() {
            // Auto-open scanner modal on page load
            if (this.activeTab === 'barcode') {
                this.$nextTick(() => this.openScannerModal());
            }

            if (this.selectedHouseholdId && this.householdSearch.trim() !== '') {
                this.selectedHouseholdLabel = this.householdSearch;
            }
        },

        currentLabel() {
            const labels = {
                barcode: 'Scan Patient Barcode',
                name: 'Search by Patient Name',
                nrc: 'Search by NRC Number',
                phone: 'Search by Phone Number',
                patient_no: 'Search by Patient Number',
            };
            return labels[this.activeTab];
        },

        visibleResults() {
            if (['name', 'phone', 'nrc'].includes(this.activeTab)) {
                return this.searchResults.slice(0, 5);
            }
            return this.searchResults;
        },

        resultCountLabel() {
            if (['name', 'phone', 'nrc'].includes(this.activeTab) && this.searchResults.length > 5) {
                return `Showing 5 of ${this.searchResults.length} result(s)`;
            }
            return `${this.searchResults.length} result(s) found`;
        },

        formatNrc(e) {
            let raw = e.target.value.replace(/[^0-9]/g, '');
            let formatted = '';
            for (let i = 0; i < raw.length && i < 9; i++) {
                if (i === 6 || i === 8) formatted += '/';
                formatted += raw[i];
            }
            e.target.value = formatted;
            this.searchQuery = formatted;
        },

        handleNrcBackspace(e) {
            const v = e.target.value;
            if (v.endsWith('/')) {
                e.preventDefault();
                const trimmed = v.slice(0, -2);
                e.target.value = trimmed;
                this.searchQuery = trimmed;
            }
        },

        // Generic NRC formatter — for the new-patient form field (no state side-effects)
        applyNrcFormat(e) {
            const raw = e.target.value.replace(/[^0-9]/g, '');
            let formatted = '';
            for (let i = 0; i < raw.length && i < 9; i++) {
                if (i === 6 || i === 8) formatted += '/';
                formatted += raw[i];
            }
            e.target.value = formatted;
        },

        handleNrcFieldBackspace(e) {
            const v = e.target.value;
            if (v.endsWith('/')) {
                e.preventDefault();
                e.target.value = v.slice(0, -2);
            }
        },

        titleCaseName(e) {
            const el = e.target;
            const start = el.selectionStart;
            const end   = el.selectionEnd;
            el.value = el.value.replace(/\b\w/g, c => c.toUpperCase());
            el.setSelectionRange(start, end);
        },

        formatPhone(e) {
            let raw = e.target.value.replace(/[^0-9]/g, '');
            if (raw.length > 9) raw = raw.substring(0, 9);
            let formatted = '';
            if (raw.length > 2) {
                formatted = raw.substring(0, 2) + ' ' + raw.substring(2);
            } else {
                formatted = raw;
            }
            this.phoneLocal = formatted;
            e.target.value = formatted;
        },

        async searchHouseholds() {
            const query = this.householdSearch.trim();

            if (query === '') {
                this.selectedHouseholdId = '';
                this.selectedHouseholdLabel = '';
                this.householdSuggestions = [];
                this.householdSuggestionOpen = false;
                return;
            }

            if (query !== this.selectedHouseholdLabel) {
                this.selectedHouseholdId = '';
            }

            try {
                const params = new URLSearchParams({ q: query });
                const res = await fetch(`${this.householdSearchUrl}?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!res.ok) {
                    throw new Error('Household search failed');
                }

                const data = await res.json();
                this.householdSuggestions = data.households ?? [];
                this.householdSuggestionOpen = this.householdSuggestions.length > 0;
            } catch (e) {
                this.householdSuggestions = [];
                this.householdSuggestionOpen = false;
            }
        },

        selectHousehold(household) {
            this.selectedHouseholdId = household.id;
            this.selectedHouseholdLabel = household.label;
            this.householdSearch = household.label;
            this.householdSuggestions = [];
            this.householdSuggestionOpen = false;
        },

        filteredVillageSuggestions() {
            const q = this.villageQuery.trim().toLowerCase();
            const pool = q === ''
                ? this.villageOptions
                : this.villageOptions.filter(v => v.toLowerCase().includes(q));

            return pool.slice(0, 4);
        },

        openVillageSuggestions() {
            if (this.newPatientMode !== 'household') {
                this.villageSuggestionOpen = false;
                return;
            }

            this.villageSuggestionOpen = this.filteredVillageSuggestions().length > 0;
        },

        onVillageInput() {
            this.paymentError = '';
            this.villageSuggestionOpen = this.filteredVillageSuggestions().length > 0;
        },

        selectVillage(village) {
            this.villageQuery = village;
            this.villageSuggestionOpen = false;
        },

        openPaymentModal(force = false) {
            if (this.newPatientMode !== 'household') return;
            if (!force && this.villageQuery.trim() === '') {
                this.paymentError = 'Please select village first.';
                return;
            }
            this.paymentError = '';
            this.showPaymentModal = true;
        },

        closePaymentModal() {
            this.showPaymentModal = false;
        },

        setPaymentPlan(plan) {
            this.paymentPlan = plan;
            this.paymentAmount = plan === 'annual' ? '6000' : '500';
        },

        paymentPlanLabel() {
            return this.paymentPlan === 'annual' ? 'Annual' : (this.paymentPlan === 'monthly' ? 'Monthly' : '');
        },

        paymentModeLabel() {
            return this.paymentMode === 'mobile_money' ? 'Mobile money' : (this.paymentMode === 'cash' ? 'Cash' : '');
        },

        confirmPayment() {
            if (!this.paymentPlan) {
                this.paymentError = 'Please choose monthly or annual amount.';
                return;
            }
            if (!this.paymentMode) {
                this.paymentError = 'Please choose payment mode.';
                return;
            }
            this.paymentAmount = this.paymentPlan === 'annual' ? '6000' : '500';
            this.paymentError = '';
            this.closePaymentModal();
        },

        async saveNewVillage() {
            const name = this.newVillageName.trim();
            if (!name) {
                this.addVillageError = 'Please type a village name.';
                return;
            }
            this.addVillageLoading = true;
            this.addVillageError = '';
            try {
                const res = await fetch(this.addVillageUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @js(csrf_token()),
                    },
                    body: JSON.stringify({ name }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.addVillageError = data.errors?.name?.[0] ?? data.message ?? 'Could not save village.';
                    return;
                }
                // Insert alphabetically and select
                this.villageOptions = [...this.villageOptions, data.name].sort((a, b) => a.localeCompare(b));
                this.villageQuery = data.name;
                this.showAddVillage = false;
                this.newVillageName = '';
                this.villageSuggestionOpen = false;
                if (!this.paymentPlan || !this.paymentMode) {
                    this.openPaymentModal();
                }
            } catch (e) {
                this.addVillageError = 'Network error — please try again.';
            } finally {
                this.addVillageLoading = false;
            }
        },

        submitStartEncounter(e) {
            e.target.submit();
        },

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
                if (this.activeTab === 'name' && this.searchSex) params.append('sex', this.searchSex);

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
            this.newPatientMode = 'patient';
            this.$nextTick(() => {
                document.getElementById('start-encounter-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },

        openNewPatientForm(mode = 'patient') {
            this.selectedPatient = null;
            this.newPatientMode = mode;
            this.showNewPatientForm = true;
            if (mode === 'household') {
                this.selectedHouseholdId = '';
                this.selectedHouseholdLabel = '';
                this.householdSearch = '';
                this.householdSuggestions = [];
                this.householdSuggestionOpen = false;
                this.villageSuggestionOpen = false;
                this.villageQuery = '';
                this.showAddVillage = false;
                this.newVillageName = '';
                this.addVillageError = '';
                this.showPaymentModal = false;
                this.paymentPlan = '';
                this.paymentMode = '';
                this.paymentAmount = '';
                this.paymentError = '';
            }
            this.$nextTick(() => {
                document.getElementById('start-encounter-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },

        resetForm() {
            this.selectedPatient    = null;
            this.showNewPatientForm = false;
            this.newPatientMode = 'patient';
            this.selectedHouseholdId = '';
            this.selectedHouseholdLabel = '';
            this.householdSearch = '';
            this.householdSuggestions = [];
            this.householdSuggestionOpen = false;
            this.villageQuery = '';
            this.villageSuggestionOpen = false;
            this.showAddVillage = false;
            this.newVillageName = '';
            this.addVillageError = '';
            this.showPaymentModal = false;
            this.paymentPlan = '';
            this.paymentMode = '';
            this.paymentAmount = '';
            this.paymentError = '';
        },
    };
}
</script>
@endpush

@endsection
