@extends('layouts.reception')

@section('title', 'Reception Dashboard — Anthu Omwe Health Center')

@push('styles')
<style>
    .reception-tab {
        border: 1px solid #60a5fa;
        color: #0f172a;
        background: #ffffff;
    }
    .reception-tab:hover {
        background: #eff6ff;
    }
    .reception-tab-active {
        color: #ffffff;
        border-color: #2563eb;
        background: linear-gradient(120deg, #2563eb, #1d4ed8);
    }
</style>
@endpush

@section('content')
@php
    $searchModes = [
        'barcode' => 'Barcode Scanner',
        'nrc' => 'NRC',
        'art' => 'ART Number',
        'nupn' => 'NUPN',
        'cellphone' => 'Cellphone',
        'full_name' => 'Full Name',
    ];

    $placeholders = [
        'barcode' => 'Scan or enter barcode value',
        'nrc' => '000000/00/0',
        'art' => 'Enter ART Number',
        'nupn' => 'Enter NUPN',
        'cellphone' => 'Enter cellphone number',
    ];

    $isFullNameMode = $searchBy === 'full_name';
    $isBarcodeMode = $searchBy === 'barcode';
@endphp

<div class="mb-8 rounded-2xl border border-blue-100 dark:border-blue-900/30 bg-gradient-to-br from-blue-50 to-sky-100/70 dark:from-blue-950/40 dark:to-gray-900 dark:bg-gray-800 shadow-sm overflow-hidden relative">
    <div class="pointer-events-none absolute -left-5 top-10 text-blue-200/70 dark:text-blue-800/40">
        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.78 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
    </div>
    <div class="pointer-events-none absolute right-2 top-8 text-blue-200/70 dark:text-blue-800/40">
        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M9.75 3v3m4.5-3v3m-7.5 3h10.5m-12 11.25h13.5A2.25 2.25 0 0021 18V6.75A2.25 2.25 0 0018.75 4.5H5.25A2.25 2.25 0 003 6.75V18A2.25 2.25 0 005.25 20.25z"/></svg>
    </div>

    <div class="relative p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-blue-950 dark:text-blue-100">Welcome Anthu Omwe</h2>
            <p class="text-sm text-slate-500 dark:text-slate-300">{{ now()->format('l, F d, Y') }}</p>
        </div>

        <h3 class="text-center text-3xl font-extrabold tracking-tight text-blue-950 dark:text-blue-100 mb-5">Search or Add New Patient</h3>

        <form method="GET" action="{{ route('reception.dashboard') }}">
            <input type="hidden" name="search_by" id="search_by" value="{{ $searchBy }}">

            <div class="flex flex-wrap items-center justify-center gap-2.5 mb-5">
                @foreach($searchModes as $mode => $label)
                    <button
                        type="button"
                        onclick="selectSearchMode('{{ $mode }}')"
                        class="reception-tab {{ $searchBy === $mode ? 'reception-tab-active' : '' }} px-5 py-2 rounded-full text-sm font-semibold min-w-[118px] transition">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div id="barcode-mode-fields" class="{{ $isBarcodeMode ? '' : 'hidden' }}">
                <div class="max-w-3xl mx-auto mb-4 rounded-2xl border border-blue-200 dark:border-blue-900/40 bg-white/80 dark:bg-gray-900/60 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div id="barcode-reader" class="w-full min-h-[220px] rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden"></div>
                            <p id="barcode-scan-status" class="text-xs text-slate-500 dark:text-slate-400 mt-2">Scanner idle. Click Start Scanner to use camera.</p>
                        </div>
                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Scan a patient or household barcode, or enter the barcode manually.</p>
                            <input
                                type="text"
                                id="barcode_query_input"
                                name="q"
                                value="{{ $queryValue }}"
                                placeholder="Scan or enter barcode value"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="startBarcodeScanner()" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Start Scanner</button>
                                <button type="button" onclick="stopBarcodeScanner()" class="px-4 py-2 rounded-lg bg-slate-600 hover:bg-slate-700 text-white text-sm font-semibold">Stop Scanner</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="single-mode-fields" class="{{ ($isFullNameMode || $isBarcodeMode) ? 'hidden' : '' }}">
                <div class="max-w-xl mx-auto mb-4">
                    <input
                        type="text"
                        name="q"
                        id="single_query_input"
                        value="{{ $queryValue }}"
                        placeholder="{{ $placeholders[$searchBy] ?? 'Search' }}"
                        class="w-full px-5 py-3.5 rounded-full border border-slate-200 dark:border-slate-700 bg-white/95 dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>

            <div id="full-name-fields" class="{{ $isFullNameMode ? '' : 'hidden' }}">
                <p class="text-center text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Please enter at least a first name or surname and select the gender to search</p>
                <div class="max-w-3xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-4">
                    <input type="text" name="first_name" value="{{ $firstName }}" placeholder="First name" class="px-4 py-3 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="last_name" value="{{ $lastName }}" placeholder="Surname" class="px-4 py-3 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="date" name="dob" value="{{ $dob }}" class="px-4 py-3 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <select name="gender" class="px-4 py-3 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Gender</option>
                        <option value="female" {{ strtolower((string) $gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="male" {{ strtolower((string) $gender) === 'male' ? 'selected' : '' }}>Male</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-center">
                <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 rounded-full bg-green-600 hover:bg-green-700 text-white text-lg font-semibold transition shadow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Search
                </button>
            </div>
        </form>

        <div class="mt-5 flex flex-wrap justify-center gap-2 text-xs font-medium">
            <span class="px-3 py-1 rounded-full bg-white/80 dark:bg-gray-800 border border-blue-200 dark:border-blue-900 text-blue-700 dark:text-blue-300">Today's Registrations: {{ number_format($todayRegistrations) }}</span>
            <span class="px-3 py-1 rounded-full bg-white/80 dark:bg-gray-800 border border-blue-200 dark:border-blue-900 text-blue-700 dark:text-blue-300">Today's Households: {{ number_format($todayHouseholds) }}</span>
            <span class="px-3 py-1 rounded-full bg-white/80 dark:bg-gray-800 border border-blue-200 dark:border-blue-900 text-blue-700 dark:text-blue-300">Today's Shift Intake: {{ number_format($todayShiftPatients) }}</span>
        </div>
    </div>
</div>

@if($hasSearch)
<div class="mb-3 flex justify-end">
    <p class="text-sm text-slate-500 dark:text-slate-400">Total Records : {{ number_format($totalRecords) }}</p>
</div>
@endif

<div class="space-y-4 mb-8">
    @if(!$hasSearch)
        <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-gray-800 p-10 text-center text-slate-500 dark:text-slate-400">
            Patient suggestions are hidden. Search to view matching patients.
        </div>
    @elseif($searchBy === 'barcode' || $searchBy === 'nrc')
        <div class="rounded-xl border border-blue-200 dark:border-blue-900/40 bg-blue-50/60 dark:bg-blue-900/10 px-4 py-3">
            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200">Patient Matches</h4>
        </div>
        @forelse($results as $row)
            @php
                $dobValue = $row->date_of_birth ? \Carbon\Carbon::parse($row->date_of_birth) : null;
                $age = $dobValue ? $dobValue->age : null;
                $patientRef = $row->patient_id ?: (string) $row->id;
            @endphp
            <div class="rounded-2xl border border-blue-300 dark:border-blue-900/40 bg-white dark:bg-gray-800 shadow-sm p-4 md:p-5">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-5">
                    <div class="md:col-span-3">
                        <a href="{{ route('patients.show', ['ref' => $patientRef]) }}" class="text-4xl leading-tight font-bold text-blue-950 dark:text-blue-100 hover:underline">{{ $row->full_name ?: 'Unknown Patient' }}</a>
                    </div>

                    <div class="md:col-span-9 grid grid-cols-2 lg:grid-cols-6 gap-3 text-sm">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 font-semibold">Date of Birth</p>
                            <p class="text-slate-700 dark:text-slate-200">{{ $dobValue ? $dobValue->format('d-M-Y') : '—' }} {{ $age ? '(' . $age . 'Y)' : '' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 font-semibold">Sex</p>
                            <p class="text-slate-700 dark:text-slate-200">{{ ucfirst((string) $row->gender ?: '—') }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 font-semibold">Cellphone</p>
                            <p class="text-slate-700 dark:text-slate-200">{{ $row->phone_number ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 font-semibold">NUPN</p>
                            <p class="text-slate-700 dark:text-slate-200">{{ $row->nupn ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 font-semibold">NRC</p>
                            <p class="text-slate-700 dark:text-slate-200">{{ $row->nrc_number ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 font-semibold">Barcode</p>
                            <p class="text-slate-700 dark:text-slate-200">{{ $row->barcode ?: '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('patients.show', ['ref' => $patientRef]) }}" class="px-4 py-1.5 rounded-full bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold">Edit Profile</a>
                    <a href="{{ route('patients.show', ['ref' => $patientRef]) }}" class="px-4 py-1.5 rounded-full bg-green-600 hover:bg-green-700 text-white text-xs font-semibold">Attend to Patient</a>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-gray-800 p-6 text-center text-slate-500 dark:text-slate-400">
                No patient matched this barcode.
            </div>
        @endforelse

        <div class="rounded-xl border border-emerald-200 dark:border-emerald-900/40 bg-emerald-50/60 dark:bg-emerald-900/10 px-4 py-3 mt-2">
            <h4 class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">Household Matches</h4>
        </div>
        @forelse($householdMatches as $household)
            <div class="rounded-2xl border border-emerald-300 dark:border-emerald-900/40 bg-white dark:bg-gray-800 shadow-sm p-4 md:p-5">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Household Head</p>
                        <p class="text-2xl font-bold text-emerald-900 dark:text-emerald-200">{{ $household->head_of_house ?: '—' }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $household->barcode ?: 'No barcode' }}</p>
                    </div>
                    <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 font-semibold">Household ID</p>
                            <p class="text-slate-700 dark:text-slate-200 font-mono">{{ $household->household_id }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 font-semibold">Cellphone</p>
                            <p class="text-slate-700 dark:text-slate-200">{{ $household->phone_number ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 font-semibold">Location</p>
                            <p class="text-slate-700 dark:text-slate-200">{{ implode(', ', array_filter([$household->village, $household->town])) ?: '—' }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('households.show', ['ref' => $household->household_id]) }}" class="inline-flex px-4 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">Open Household</a>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-gray-800 p-6 text-center text-slate-500 dark:text-slate-400">
                No household matched this barcode.
            </div>
        @endforelse
    @else
    @forelse($results as $row)
        @php
            $dobValue = $row->date_of_birth ? \Carbon\Carbon::parse($row->date_of_birth) : null;
            $age = $dobValue ? $dobValue->age : null;
            $patientRef = $row->patient_id ?: (string) $row->id;
        @endphp
        <div class="rounded-2xl border border-blue-300 dark:border-blue-900/40 bg-white dark:bg-gray-800 shadow-sm p-4 md:p-5">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-5">
                <div class="md:col-span-3">
                    <a href="{{ route('patients.show', ['ref' => $patientRef]) }}" class="text-4xl leading-tight font-bold text-blue-950 dark:text-blue-100 hover:underline">{{ $row->full_name ?: 'Unknown Patient' }}</a>
                </div>

                <div class="md:col-span-9 grid grid-cols-2 lg:grid-cols-6 gap-3 text-sm">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 font-semibold">Date of Birth</p>
                        <p class="text-slate-700 dark:text-slate-200">{{ $dobValue ? $dobValue->format('d-M-Y') : '—' }} {{ $age ? '(' . $age . 'Y)' : '' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 font-semibold">Sex</p>
                        <p class="text-slate-700 dark:text-slate-200">{{ ucfirst((string) $row->gender ?: '—') }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 font-semibold">Cellphone</p>
                        <p class="text-slate-700 dark:text-slate-200">{{ $row->phone_number ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 font-semibold">NUPN</p>
                        <p class="text-slate-700 dark:text-slate-200">{{ $row->nupn ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 font-semibold">NRC</p>
                        <p class="text-slate-700 dark:text-slate-200">{{ $row->nrc_number ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 font-semibold">Household Head</p>
                        <p class="text-slate-700 dark:text-slate-200">{{ $row->household_head_of_house ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('patients.show', ['ref' => $patientRef]) }}" class="px-4 py-1.5 rounded-full bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold">Edit Profile</a>
                <button type="button" class="px-4 py-1.5 rounded-full bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold">Admission</button>
                <button type="button" class="px-4 py-1.5 rounded-full bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold">Appointment</button>
                <button type="button" class="px-4 py-1.5 rounded-full bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold">Assign Queue</button>
                <button type="button" class="px-4 py-1.5 rounded-full bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold">Historical Visit</button>
                <button type="button" class="px-4 py-1.5 rounded-full bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold">Binding</button>
                <a href="{{ route('patients.show', ['ref' => $patientRef]) }}" class="px-4 py-1.5 rounded-full bg-green-600 hover:bg-green-700 text-white text-xs font-semibold">Attend to Patient</a>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-blue-200 dark:border-blue-800 bg-white dark:bg-gray-800 p-10 text-center">
            <p class="text-2xl font-semibold text-slate-400 dark:text-slate-500 italic mb-5">No Patient Found</p>
            <a href="{{ route('patients.create') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add New Patient
            </a>
        </div>
    @endforelse
    @endif
</div>

<div class="h-2"></div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
    let barcodeScanner = null;
    let scannerRunning = false;

    function setScannerStatus(text) {
        const status = document.getElementById('barcode-scan-status');
        if (status) {
            status.textContent = text;
        }
    }

    function applyScannedCode(code) {
        const trimmed = (code || '').trim();
        if (!trimmed) return;

        const barcodeInput = document.getElementById('barcode_query_input');
        if (barcodeInput) {
            barcodeInput.value = trimmed;
        }
        setScannerStatus('Barcode detected. Searching...');

        const form = document.getElementById('search_by')?.closest('form');
        if (form) {
            form.submit();
        }
    }

    async function startBarcodeScanner() {
        if (scannerRunning) return;

        if (typeof Html5Qrcode === 'undefined') {
            setScannerStatus('Scanner library is still loading. Try again.');
            return;
        }

        barcodeScanner = barcodeScanner || new Html5Qrcode('barcode-reader');
        try {
            await barcodeScanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 230, height: 120 } },
                (decodedText) => {
                    if (scannerRunning) {
                        applyScannedCode(decodedText);
                        stopBarcodeScanner();
                    }
                },
                () => {}
            );
            scannerRunning = true;
            setScannerStatus('Scanner started. Point camera at barcode.');
        } catch (error) {
            setScannerStatus('Unable to start scanner. Check camera permissions.');
        }
    }

    async function stopBarcodeScanner() {
        if (!barcodeScanner || !scannerRunning) {
            return;
        }

        try {
            await barcodeScanner.stop();
            await barcodeScanner.clear();
        } catch (error) {
            // Ignore stop errors from rapidly changing scanner state.
        }

        scannerRunning = false;
        setScannerStatus('Scanner stopped.');
    }

    function selectSearchMode(mode) {
        const field = document.getElementById('search_by');
        if (!field) return;
        field.value = mode;

        const fullNameFields = document.getElementById('full-name-fields');
        const singleModeFields = document.getElementById('single-mode-fields');
        const barcodeModeFields = document.getElementById('barcode-mode-fields');

        if (mode === 'full_name') {
            fullNameFields.classList.remove('hidden');
            singleModeFields.classList.add('hidden');
            barcodeModeFields.classList.add('hidden');
            stopBarcodeScanner();
        } else if (mode === 'barcode') {
            fullNameFields.classList.add('hidden');
            singleModeFields.classList.add('hidden');
            barcodeModeFields.classList.remove('hidden');
        } else {
            fullNameFields.classList.add('hidden');
            singleModeFields.classList.remove('hidden');
            barcodeModeFields.classList.add('hidden');
            stopBarcodeScanner();
        }

        const form = field.closest('form');
        if (form) {
            form.submit();
        }
    }

    window.addEventListener('beforeunload', function () {
        stopBarcodeScanner();
    });
</script>
@endpush
@endsection
