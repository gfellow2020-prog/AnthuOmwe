@extends('layouts.dashboard')

@section('title', 'Patient Details — Anthu Omwe Health Center')

@section('breadcrumbs')
@php
    $firstName = $patient['firstName'] ?? $patient['first_name'] ?? $patient['firstname'] ?? '';
    $lastName  = $patient['lastName'] ?? $patient['last_name'] ?? $patient['lastname'] ?? '';
    $fullName  = trim($firstName . ' ' . $lastName) ?: ($patient['name'] ?? $patient['fullName'] ?? 'Patient');
@endphp
<span class="mx-2">/</span>
<a href="{{ route('patients.index') }}" class="hover:text-neutral-700 transition">Patients</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">{{ $fullName }}</span>
@endsection

@section('content')

@if($error)
    <div class="mb-5 flex items-start gap-3 p-4 rounded bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span>{{ $error }}</span>
    </div>
@endif

@php
    $pid        = $patient['patientId'] ?? $patient['patient_id'] ?? $patient['id'] ?? $patient['_id'] ?? '—';
    $barcode    = $patient['barcode'] ?? $patient['barcodeId'] ?? $pid;
    $gender     = $patient['gender'] ?? $patient['sex'] ?? '—';
    $dob        = $patient['dateOfBirth'] ?? $patient['dob'] ?? $patient['date_of_birth'] ?? null;
    $age        = $patient['age'] ?? null;
    $phone      = $patient['phone'] ?? $patient['phoneNumber'] ?? $patient['mobile'] ?? '—';
    $email      = $patient['email'] ?? '—';
    $status     = $patient['status'] ?? 'Active';
    $nrc        = $patient['nrcNumber'] ?? $patient['nrc_number'] ?? '—';
    $country    = $patient['country'] ?? '—';
    $otherCell  = $patient['otherCellphone'] ?? '—';
    $landline   = $patient['landline'] ?? '—';
    $houseNo    = $patient['houseNumber'] ?? '—';
    $roadStreet = $patient['roadStreet'] ?? '—';
    $area       = $patient['area'] ?? '—';
    $cityTown   = $patient['cityTownVillage'] ?? '—';
    $landmarks  = $patient['landmarks'] ?? '—';
    $marital    = $patient['maritalStatus'] ?? '—';
    $spouseFirst = $patient['spouseFirstName'] ?? '—';
    $spouseSur  = $patient['spouseSurname'] ?? '—';
    $homeLang   = $patient['homeLanguage'] ?? '—';
    $bornZambia = $patient['bornInZambia'] ?? '—';
    $provBirth  = $patient['provinceOfBirth'] ?? '—';
    $distBirth  = $patient['districtOfBirth'] ?? '—';
    $placeBirth = $patient['placeOfBirth'] ?? '—';
    $occupation = $patient['occupation'] ?? '—';
    $artNumber  = $patient['artNumber'] ?? '—';
    $nupn       = $patient['nupn'] ?? '—';
    $bloodGroup = $patient['bloodGroup'] ?? '—';
    $allergies  = $patient['allergies'] ?? '—';

    $householdId        = $patient['householdId'] ?? $patient['household_id'] ?? $patient['houseHoldId'] ?? '—';
    $relationshipToHead = $patient['relationshipToHead'] ?? $patient['relationship_to_head'] ?? '—';
    $createdAt  = $patient['createdAt'] ?? $patient['created_at'] ?? null;

    if (is_array($dob) || is_object($dob)) { $dob = null; }

    if (!$age && $dob) {
        try { $age = \Carbon\Carbon::parse($dob)->age; } catch (\Exception $e) { $age = null; }
    }

    $householdName = is_array($household)
        ? ($household['householdName'] ?? $household['household_name'] ?? $household['headOfHouseName'] ?? $household['name'] ?? '—')
        : '—';

    $householdBarcode = is_array($household) ? ($household['barcode'] ?? $household['barcodeId'] ?? null) : null;
    $householdRef     = is_array($household) ? ($household['id'] ?? $household['_id'] ?? null) : null;

    $dobFormatted = '—';
    if ($dob) { try { $dobFormatted = \Carbon\Carbon::parse($dob)->format('d M Y'); } catch (\Exception $e) {} }

    $regFormatted = '—';
    if ($createdAt && is_string($createdAt)) { try { $regFormatted = \Carbon\Carbon::parse($createdAt)->format('d M Y'); } catch (\Exception $e) {} }
@endphp

{{-- ================================================================
     TOP SUMMARY BAR
     ================================================================ --}}
<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
    <div class="h-1 bg-neutral-900 dark:bg-white"></div>
    <div class="px-5 py-4">
        <div class="flex flex-wrap items-center gap-x-8 gap-y-2 text-sm">
            <div>
                <span class="text-neutral-500">Name:</span>
                <span class="font-semibold text-neutral-900 dark:text-white ml-1">{{ $fullName }}</span>
            </div>
            <div>
                <span class="text-neutral-500">DOB:</span>
                <span class="font-semibold text-neutral-900 dark:text-white ml-1">{{ $dobFormatted }}{{ $age ? ' (' . $age . ' yrs)' : '' }}</span>
            </div>
            <div>
                <span class="text-neutral-500">Sex:</span>
                <span class="font-semibold text-neutral-900 dark:text-white ml-1">{{ ucfirst((string)$gender) }}</span>
            </div>
            <div>
                <span class="text-neutral-500">Cellphone:</span>
                <span class="font-semibold text-neutral-900 dark:text-white ml-1">{{ $phone }}</span>
            </div>
            <div>
                <span class="text-neutral-500">NUPN:</span>
                <span class="font-semibold text-neutral-900 dark:text-white ml-1">{{ $nupn }}</span>
            </div>
            <div>
                <span class="text-neutral-500">NRC:</span>
                <span class="font-semibold text-neutral-900 dark:text-white ml-1 font-mono">{{ $nrc }}</span>
            </div>
            @if($householdName !== '—')
            <div>
                <span class="text-neutral-500">Household:</span>
                @if($householdRef)
                    <a href="{{ route('households.show', ['ref' => $householdRef]) }}" class="font-semibold text-neutral-700 dark:text-neutral-300 hover:underline ml-1">{{ $householdName }}</a>
                @else
                    <span class="font-semibold text-neutral-900 dark:text-white ml-1">{{ $householdName }}</span>
                @endif
            </div>
            @endif
        </div>
        {{-- Action buttons --}}
        <div class="mt-3 flex flex-wrap gap-2">
            <a href="{{ route('patients.edit', ['ref' => $pid]) }}" class="px-3 py-1.5 text-xs font-medium rounded bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-900 transition">Edit Profile</a>

            <form method="POST" action="{{ route('encounters.start') }}" class="inline">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $patientDbId }}">
                <input type="hidden" name="visit_type" value="admission">
                <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-200 transition">Admission</button>
            </form>

            <form method="POST" action="{{ route('encounters.start') }}" class="inline">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $patientDbId }}">
                <input type="hidden" name="visit_type" value="appointment">
                <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-200 transition">Appointment</button>
            </form>

            <form method="POST" action="{{ route('encounters.start') }}" class="inline">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $patientDbId }}">
                <input type="hidden" name="visit_type" value="queue">
                <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-200 transition">Assign Queue</button>
            </form>

            <a href="{{ route('patients.encounters', ['ref' => $pid]) }}" class="px-3 py-1.5 text-xs font-medium rounded bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-200 transition">Historical Visit</a>

            <form method="POST" action="{{ route('encounters.start') }}" class="inline">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $patientDbId }}">
                <input type="hidden" name="visit_type" value="binding">
                <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-200 transition">Binding</button>
            </form>

            <form method="POST" action="{{ route('encounters.start') }}" class="inline">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $patientDbId }}">
                <input type="hidden" name="visit_type" value="walk-in">
                <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded bg-neutral-700 hover:bg-neutral-600 dark:bg-neutral-300 dark:hover:bg-neutral-200 text-white dark:text-neutral-900 transition">Attend to Patient</button>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     BASIC INFO
     ================================================================ --}}
<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Basic Info</h3>
        <button class="text-xs text-neutral-600 dark:text-neutral-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-4 gap-x-6">
        <div><p class="text-xs text-neutral-500">Full Name</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $fullName }}</p></div>
        <div><p class="text-xs text-neutral-500">Sex</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ ucfirst((string)$gender) }}</p></div>
        <div><p class="text-xs text-neutral-500">Date of Birth</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $dobFormatted }}{{ $age ? ' (' . $age . ' yrs)' : '' }}</p></div>
        <div><p class="text-xs text-neutral-500">Country</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $country }}</p></div>
        <div><p class="text-xs text-neutral-500">NRC Number</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5 font-mono">{{ $nrc }}</p></div>
        <div><p class="text-xs text-neutral-500">NUPN</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5 font-mono">{{ $nupn }}</p></div>
        <div><p class="text-xs text-neutral-500">Registered on</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $regFormatted }}</p></div>
    </div>
</div>

{{-- ================================================================
     CONTACT INFORMATION
     ================================================================ --}}
<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Contact Information</h3>
        <button class="text-xs text-neutral-600 dark:text-neutral-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-4 gap-x-6">
        <div><p class="text-xs text-neutral-500">Cellphone Number</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $phone }}</p></div>
        <div><p class="text-xs text-neutral-500">Other Cellphone Number</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $otherCell }}</p></div>
        <div><p class="text-xs text-neutral-500">Landline Number</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $landline }}</p></div>
        <div><p class="text-xs text-neutral-500">Email</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $email }}</p></div>
        <div><p class="text-xs text-neutral-500">House Number</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $houseNo }}</p></div>
        <div><p class="text-xs text-neutral-500">Road / Street</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $roadStreet }}</p></div>
        <div><p class="text-xs text-neutral-500">Area</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $area }}</p></div>
        <div><p class="text-xs text-neutral-500">City / Town / Village</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $cityTown }}</p></div>
        <div class="col-span-2"><p class="text-xs text-neutral-500">Landmarks & Direction</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $landmarks }}</p></div>
    </div>
</div>

{{-- ================================================================
     MARITAL STATUS / SPOUSE DETAILS
     ================================================================ --}}
<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Marital Status / Spouse Details</h3>
        <button class="text-xs text-neutral-600 dark:text-neutral-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-6">
        <div><p class="text-xs text-neutral-500">Marital Status</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ ucfirst((string)$marital) }}</p></div>
        <div><p class="text-xs text-neutral-500">Spouse First Name</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $spouseFirst }}</p></div>
        <div><p class="text-xs text-neutral-500">Spouse Surname</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $spouseSur }}</p></div>
    </div>
</div>

{{-- ================================================================
     PLACE OF BIRTH
     ================================================================ --}}
<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Place of Birth</h3>
        <button class="text-xs text-neutral-600 dark:text-neutral-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-4 gap-x-6">
        <div><p class="text-xs text-neutral-500">Home Language</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $homeLang }}</p></div>
        <div><p class="text-xs text-neutral-500">Born in Zambia</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ ucfirst((string)$bornZambia) }}</p></div>
        <div><p class="text-xs text-neutral-500">Province of Birth</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $provBirth }}</p></div>
        <div><p class="text-xs text-neutral-500">District of Birth</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $distBirth }}</p></div>
        <div><p class="text-xs text-neutral-500">Place of Birth</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $placeBirth }}</p></div>
    </div>
</div>

{{-- ================================================================
     EDUCATION & EMPLOYMENT
     ================================================================ --}}
<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Education & Employment</h3>
        <button class="text-xs text-neutral-600 dark:text-neutral-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-6">
        <div><p class="text-xs text-neutral-500">Occupation</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ ucfirst((string)$occupation) }}</p></div>
    </div>
</div>

{{-- ================================================================
     BIOMETRICS
     ================================================================ --}}
<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Biometrics</h3>
        <button class="text-xs text-neutral-600 dark:text-neutral-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-4 gap-x-6">
        <div><p class="text-xs text-neutral-500">ART Number</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5 font-mono">{{ $artNumber }}</p></div>
        <div><p class="text-xs text-neutral-500">NUPN</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5 font-mono">{{ $nupn }}</p></div>
        <div><p class="text-xs text-neutral-500">Blood Group</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $bloodGroup }}</p></div>
        <div class="col-span-2"><p class="text-xs text-neutral-500">Allergies</p><p class="text-sm font-medium text-neutral-900 dark:text-white mt-0.5">{{ $allergies }}</p></div>
    </div>
</div>

{{-- ================================================================
     BARCODE SECTION
     ================================================================ --}}
@if($barcode !== '—' && $barcode !== '')
<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Patient Barcode</h3>
        <button onclick="printBarcode()" class="inline-flex items-center gap-1.5 text-xs text-neutral-600 dark:text-neutral-400 hover:underline font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Barcode
        </button>
    </div>
    <div class="px-6 py-5 flex flex-col items-center">
        <div class="barcode-label" id="barcode-printable">
            <p class="barcode-label-phone">Phone&nbsp; :&nbsp; {{ $phone !== '—' ? $phone : '' }}</p>
            <svg class="js-barcode" data-barcode-value="{{ $barcode }}"></svg>
            <p class="barcode-label-value">{{ $barcode }}</p>
        </div>
    </div>
</div>
@endif

{{-- Finish / Back --}}
<div class="flex justify-end mb-5">
    <a href="{{ route('patients.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-900 text-sm font-medium rounded transition">
        Finish
    </a>
</div>

@endsection

@push('styles')
<style>
    /* ── Barcode label card ── */
    .barcode-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 14px 28px 16px;
        min-width: 280px;
        max-width: 340px;
        margin: 0 auto;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .barcode-label-phone {
        font-size: 12px;
        font-weight: 600;
        color: #111827;
        letter-spacing: 0.3px;
        margin: 0 0 10px 0;
        padding: 0;
        text-align: center;
        line-height: 1;
    }
    .barcode-label svg {
        display: block;
        margin: 0 auto;
        max-width: 100%;
    }
    .barcode-label-value {
        margin: 8px 0 0 0;
        padding: 0;
        font-size: 14px;
        font-weight: 700;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        letter-spacing: 3px;
        color: #111827;
        text-align: center;
        line-height: 1;
    }
    /* ── Compact variant for table cells ── */
    .barcode-label-sm {
        padding: 8px 14px 10px;
        min-width: auto;
        max-width: 220px;
    }
    .barcode-label-sm .barcode-label-phone {
        font-size: 9px;
        margin-bottom: 6px;
    }
    .barcode-label-sm .barcode-label-value {
        font-size: 10px;
        letter-spacing: 1.5px;
        margin-top: 4px;
    }

    /* ── Print: only the barcode label ── */
    @media print {
        body * { visibility: hidden !important; }
        #barcode-printable,
        #barcode-printable * { visibility: visible !important; }
        #barcode-printable {
            position: absolute;
            left: 50%; top: 50%;
            transform: translate(-50%, -50%);
            border: 1px solid #000;
            border-radius: 4px;
            padding: 12px 24px 14px;
            box-shadow: none;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof JsBarcode === 'undefined') return;

        document.querySelectorAll('.js-barcode[data-barcode-value]').forEach(function (node) {
            const value = node.getAttribute('data-barcode-value') || '';
            if (!value || value === '—') return;
            try {
                var isSmall = node.closest('.barcode-label-sm');
                JsBarcode(node, value, {
                    format: 'CODE128',
                    width: isSmall ? 1.5 : 2.2,
                    height: isSmall ? 32 : 55,
                    displayValue: false,
                    margin: 0,
                    background: '#ffffff',
                    lineColor: '#000000',
                });
            } catch (e) { /* silently ignore unrenderable values */ }
        });
    });

    function printBarcode() {
        window.print();
    }
</script>
@endpush
