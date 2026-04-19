@extends('layouts.dashboard')

@section('title', 'Patient Details — Anthu Omwe Health Center')

@section('page-header')
    @php
        $firstName = $patient['firstName'] ?? $patient['first_name'] ?? $patient['firstname'] ?? '';
        $lastName  = $patient['lastName'] ?? $patient['last_name'] ?? $patient['lastname'] ?? '';
        $fullName  = trim($firstName . ' ' . $lastName) ?: ($patient['name'] ?? $patient['fullName'] ?? 'Patient Details');
    @endphp

    @include('components.page-header', [
        'title'    => 'Patient Details',
        'subtitle' => 'Profile, barcode and registration information',
        'actions'  => '<a href="' . route('patients.index') . '" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                          Back to Patients
                       </a>',
    ])
@endsection

@section('content')

@if($error)
    <div class="mb-5 flex items-start gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
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
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden mb-5">
    <div class="h-1 bg-blue-600"></div>
    <div class="px-5 py-4">
        <div class="flex flex-wrap items-center gap-x-8 gap-y-2 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Name:</span>
                <span class="font-semibold text-gray-900 dark:text-white ml-1">{{ $fullName }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">DOB:</span>
                <span class="font-semibold text-gray-900 dark:text-white ml-1">{{ $dobFormatted }}{{ $age ? ' (' . $age . ' yrs)' : '' }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Sex:</span>
                <span class="font-semibold text-gray-900 dark:text-white ml-1">{{ ucfirst((string)$gender) }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Cellphone:</span>
                <span class="font-semibold text-gray-900 dark:text-white ml-1">{{ $phone }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">NUPN:</span>
                <span class="font-semibold text-gray-900 dark:text-white ml-1">{{ $nupn }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">NRC:</span>
                <span class="font-semibold text-gray-900 dark:text-white ml-1 font-mono">{{ $nrc }}</span>
            </div>
            @if($householdName !== '—')
            <div>
                <span class="text-gray-500 dark:text-gray-400">Household:</span>
                @if($householdRef)
                    <a href="{{ route('households.show', ['ref' => $householdRef]) }}" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline ml-1">{{ $householdName }}</a>
                @else
                    <span class="font-semibold text-gray-900 dark:text-white ml-1">{{ $householdName }}</span>
                @endif
            </div>
            @endif
        </div>
        {{-- Action buttons --}}
        <div class="mt-3 flex flex-wrap gap-2">
            <button class="px-3 py-1.5 text-xs font-medium rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition">Edit Profile</button>
            <button class="px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition">Admission</button>
            <button class="px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition">Appointment</button>
            <button class="px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition">Assign Queue</button>
            <button class="px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition">Historical Visit</button>
            <button class="px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition">Binding</button>
            <button class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition">Attend to Patient</button>
        </div>
    </div>
</div>

{{-- ================================================================
     BASIC INFO
     ================================================================ --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Basic Info</h3>
        <button class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-4 gap-x-6">
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Full Name</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $fullName }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Sex</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ ucfirst((string)$gender) }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Date of Birth</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $dobFormatted }}{{ $age ? ' (' . $age . ' yrs)' : '' }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Country</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $country }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">NRC Number</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5 font-mono">{{ $nrc }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">NUPN</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5 font-mono">{{ $nupn }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Registered on</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $regFormatted }}</p></div>
    </div>
</div>

{{-- ================================================================
     CONTACT INFORMATION
     ================================================================ --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Contact Information</h3>
        <button class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-4 gap-x-6">
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Cellphone Number</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $phone }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Other Cellphone Number</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $otherCell }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Landline Number</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $landline }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Email</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $email }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">House Number</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $houseNo }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Road / Street</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $roadStreet }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Area</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $area }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">City / Town / Village</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $cityTown }}</p></div>
        <div class="col-span-2"><p class="text-xs text-gray-500 dark:text-gray-400">Landmarks & Direction</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $landmarks }}</p></div>
    </div>
</div>

{{-- ================================================================
     MARITAL STATUS / SPOUSE DETAILS
     ================================================================ --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Marital Status / Spouse Details</h3>
        <button class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-6">
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Marital Status</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ ucfirst((string)$marital) }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Spouse First Name</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $spouseFirst }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Spouse Surname</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $spouseSur }}</p></div>
    </div>
</div>

{{-- ================================================================
     PLACE OF BIRTH
     ================================================================ --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Place of Birth</h3>
        <button class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-4 gap-x-6">
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Home Language</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $homeLang }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Born in Zambia</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ ucfirst((string)$bornZambia) }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Province of Birth</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $provBirth }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">District of Birth</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $distBirth }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Place of Birth</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $placeBirth }}</p></div>
    </div>
</div>

{{-- ================================================================
     EDUCATION & EMPLOYMENT
     ================================================================ --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Education & Employment</h3>
        <button class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-6">
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Occupation</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ ucfirst((string)$occupation) }}</p></div>
    </div>
</div>

{{-- ================================================================
     BIOMETRICS
     ================================================================ --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Biometrics</h3>
        <button class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">Edit</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-4 gap-x-6">
        <div><p class="text-xs text-gray-500 dark:text-gray-400">ART Number</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5 font-mono">{{ $artNumber }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">NUPN</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5 font-mono">{{ $nupn }}</p></div>
        <div><p class="text-xs text-gray-500 dark:text-gray-400">Blood Group</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $bloodGroup }}</p></div>
        <div class="col-span-2"><p class="text-xs text-gray-500 dark:text-gray-400">Allergies</p><p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $allergies }}</p></div>
    </div>
</div>

{{-- ================================================================
     BARCODE SECTION
     ================================================================ --}}
@if($barcode !== '—' && $barcode !== '')
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Patient Barcode</h3>
        <button onclick="printBarcode()" class="inline-flex items-center gap-1.5 text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">
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
    <a href="{{ route('patients.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
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
