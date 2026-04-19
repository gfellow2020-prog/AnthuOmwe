@extends('layouts.dashboard')

@section('title', 'Household Details — Anthu Omwe Health Center')

@section('page-header')
    @php
        $householdName = $household['householdName'] ?? $household['household_name'] ?? $household['name'] ?? $household['headOfHouseName'] ?? 'Household Details';
        $barcode = $household['barcode'] ?? $household['code'] ?? $household['barcodeId'] ?? '—';
    @endphp

    @include('components.page-header', [
        'title'    => $householdName,
        'subtitle' => 'Household profile and linked patients',
        'actions'  => '<a href="' . route('households.index') . '" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition">Back to Households</a>',
    ])
@endsection

@section('content')

@if($errors->has('household'))
    <div class="mb-5 flex items-start gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
        <span>{{ $errors->first('household') }}</span>
    </div>
@endif

@if($error)
    <div class="mb-5 flex items-start gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
        <span>{{ $error }}</span>
    </div>
@endif

@php
    $householdDocId = $household['id'] ?? $household['householdDocId'] ?? '—';
    $householdId = $household['householdId'] ?? $household['household_id'] ?? $household['houseHoldId'] ?? $householdDocId;
    $head = $household['headName'] ?? $household['head_name'] ?? $household['primaryContactName'] ?? $household['contactName'] ?? $household['headOfHouseName'] ?? '—';
    $phone = $household['phone'] ?? $household['phoneNumber'] ?? $household['contactPhone'] ?? $household['phone_number'] ?? '—';
    $location = $household['village'] ?? $household['ward'] ?? $household['district'] ?? $household['town'] ?? $household['address'] ?? '—';
    $status = $household['status'] ?? $household['paymentStatus'] ?? 'Active';
    $nrc = $household['nrcNumber'] ?? $household['nrc_number'] ?? '—';
    $town = $household['town'] ?? '—';
    $householdType = $household['householdType'] ?? $household['household_type'] ?? '—';
    $subscriptionPlan = $household['subscriptionPlan'] ?? $household['subscription_plan'] ?? '—';
    $subscriptionFee = $household['subscriptionFee'] ?? $household['subscription_fee'] ?? '—';
    $createdAt = $household['createdAt'] ?? $household['created_at'] ?? null;
    if (is_array($createdAt) || is_object($createdAt)) { $createdAt = null; }
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Barcode</p>
        @if($barcode !== '—' && $barcode !== '')
            <div class="barcode-label">
                <p class="barcode-label-phone">Phone&nbsp; :&nbsp; {{ $phone !== '—' ? $phone : '' }}</p>
                <svg class="js-barcode" data-barcode-value="{{ $barcode }}"></svg>
                <p class="barcode-label-value">{{ $barcode }}</p>
            </div>
        @else
            <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-100 font-mono">—</p>
        @endif
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Household ID</p>
        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-100 font-mono">{{ $householdId }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Doc ID: {{ $householdDocId }}</p>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Head / Contact</p>
        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $head }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $phone }}</p>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Location</p>
        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $location }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Status: {{ ucfirst((string) $status) }}</p>
    </div>
</div>

<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Household Full Details</h3>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-0">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400">NRC Number</p>
            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $nrc }}</p>
        </div>
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400">Town</p>
            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $town }}</p>
        </div>
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400">Household Type</p>
            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ ucfirst((string) $householdType) }}</p>
        </div>
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400">Subscription Plan</p>
            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ ucfirst((string) $subscriptionPlan) }}</p>
        </div>
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400">Subscription Fee</p>
            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ is_numeric($subscriptionFee) ? number_format((float) $subscriptionFee, 2) : $subscriptionFee }}</p>
        </div>
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400">Payment Status</p>
            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ ucfirst((string) ($household['paymentStatus'] ?? $status)) }}</p>
        </div>
    </div>
</div>

<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Household Patients</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                @if($membersTotal > 0)
                    Showing {{ number_format($membersFrom) }}-{{ number_format($membersTo) }} of {{ number_format($membersTotal) }} patients
                @else
                    No patients linked to this household
                @endif
            </p>
        </div>

        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
            <label for="members_limit" class="text-xs text-gray-500 dark:text-gray-400">Per page</label>
            <select id="members_limit" name="members_limit" onchange="this.form.submit()"
                    class="px-2.5 py-1.5 text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                @foreach([10, 25, 50, 100] as $pp)
                    <option value="{{ $pp }}" {{ $membersLimit == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                @endforeach
            </select>
            <input type="hidden" name="members_page" value="1">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <th class="px-5 py-3.5 text-left font-semibold">#</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Patient</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Barcode</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Gender</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Age / DOB</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Phone</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($pagedMembers as $index => $patient)
                    @php
                        $rowNum = (($membersPage - 1) * $membersLimit) + $index + 1;
                        $firstName = $patient['firstName'] ?? $patient['first_name'] ?? $patient['firstname'] ?? '';
                        $lastName = $patient['lastName'] ?? $patient['last_name'] ?? $patient['lastname'] ?? '';
                        $fullName = trim($firstName . ' ' . $lastName) ?: ($patient['name'] ?? $patient['fullName'] ?? '—');
                        $patientId = $patient['patientId'] ?? $patient['patient_id'] ?? $patient['id'] ?? $patient['_id'] ?? '—';
                        $patientBarcode = $patient['barcode'] ?? $patient['barcodeId'] ?? $patient['householdBarcode'] ?? $patientId;
                        $gender = $patient['gender'] ?? $patient['sex'] ?? '—';
                        $dob = $patient['dateOfBirth'] ?? $patient['dob'] ?? $patient['date_of_birth'] ?? null;
                        $age = $patient['age'] ?? null;
                        if ((is_array($dob) || is_object($dob)) || !is_string($dob)) { $dob = null; }
                        if (!$age && $dob) {
                            try { $age = \Carbon\Carbon::parse($dob)->age; } catch (\Exception $e) { $age = null; }
                        }
                        $phone = $patient['phone'] ?? $patient['phoneNumber'] ?? $patient['mobile'] ?? '—';
                        $status = $patient['status'] ?? 'Active';
                    @endphp

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                        <td class="px-5 py-3.5 text-xs text-gray-400 dark:text-gray-500 font-mono">{{ $rowNum }}</td>
                        <td class="px-5 py-3.5">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $fullName }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 font-mono">{{ $patientId }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300 font-mono">
                            @if($patientBarcode !== '—' && $patientBarcode !== '')
                                <div class="barcode-label barcode-label-sm">
                                    <p class="barcode-label-phone">Phone&nbsp;:&nbsp;{{ $phone !== '—' ? $phone : '' }}</p>
                                    <svg class="js-barcode" data-barcode-value="{{ $patientBarcode }}"></svg>
                                    <p class="barcode-label-value">{{ $patientBarcode }}</p>
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst((string) $gender) }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                            {{ $age ? $age . ' yrs' : '—' }}
                            @if($dob)
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    @php
                                        try { echo \Carbon\Carbon::parse($dob)->format('d M Y'); }
                                        catch(\Exception $e) { echo '—'; }
                                    @endphp
                                </p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300">{{ $phone }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">{{ ucfirst((string) $status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-14 text-center text-sm text-gray-500 dark:text-gray-400">
                            No patients found under this household.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between gap-3 px-5 py-4 border-t border-gray-100 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400">Page {{ $membersPage }} of {{ $membersTotalPages }}</p>

        <div class="flex items-center gap-2">
            <a href="{{ $membersPage > 1 ? request()->fullUrlWithQuery(['members_page' => $membersPage - 1]) : '#' }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition {{ $membersPage <= 1 ? 'border-gray-100 dark:border-gray-700 text-gray-300 dark:text-gray-600 bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed pointer-events-none' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-700 hover:text-blue-700 dark:hover:text-blue-400 shadow-sm' }}">
                Previous
            </a>

            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border bg-blue-600 border-blue-600 text-white text-sm font-semibold shadow-sm">
                {{ $membersPage }}
            </span>

            <a href="{{ $membersPage < $membersTotalPages ? request()->fullUrlWithQuery(['members_page' => $membersPage + 1]) : '#' }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition {{ $membersPage >= $membersTotalPages ? 'border-gray-100 dark:border-gray-700 text-gray-300 dark:text-gray-600 bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed pointer-events-none' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-700 hover:text-blue-700 dark:hover:text-blue-400 shadow-sm' }}">
                Next
            </a>
        </div>
    </div>
</div>

@if($createdAt && is_string($createdAt))
    <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">Household created: 
        @php
            try { echo \Carbon\Carbon::parse($createdAt)->format('d M Y h:i A'); }
            catch(\Exception $e) { echo '—'; }
        @endphp
    </p>
@endif

<div class="h-6"></div>
@endsection

@push('styles')
<style>
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof JsBarcode === 'undefined') {
            return;
        }

        document.querySelectorAll('.js-barcode[data-barcode-value]').forEach(function (node) {
            const value = node.getAttribute('data-barcode-value') || '';

            if (!value || value === '—') {
                return;
            }

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
            } catch (e) {
                // Keep page stable even when an unexpected value is not encodable.
            }
        });
    });
</script>
@endpush
