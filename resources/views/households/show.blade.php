@extends('layouts.dashboard')

@section('title', 'Household Details — Anthu Omwe Health Center')

@section('breadcrumbs')
@php
    $householdName = $household['householdName'] ?? $household['household_name'] ?? $household['name'] ?? $household['headOfHouseName'] ?? 'Household Details';
@endphp
<span class="mx-2">/</span>
<a href="{{ route('households.index') }}" class="hover:text-neutral-700 transition">Households</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">{{ $householdName }}</span>
@endsection

@section('content')

@if(session('success'))
    <div class="mb-5 flex items-start gap-3 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm dark:bg-green-900/20 dark:border-green-700 dark:text-green-300">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->has('household'))
    <div class="mb-5 flex items-start gap-3 p-4 rounded-xl bg-neutral-900 dark:bg-neutral-800 border border-neutral-700 dark:border-neutral-600 text-white dark:text-neutral-200 text-sm">
        <span>{{ $errors->first('household') }}</span>
    </div>
@endif

@if($errors->any() && !$errors->has('household'))
    <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm dark:bg-red-900/20 dark:border-red-700 dark:text-red-300">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $validationError)
                <li>{{ $validationError }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($error)
    <div class="mb-5 flex items-start gap-3 p-4 rounded-xl bg-neutral-900 dark:bg-neutral-800 border border-neutral-700 dark:border-neutral-600 text-white dark:text-neutral-200 text-sm">
        <span>{{ $error }}</span>
    </div>
@endif

@php
    $householdDocId = $household['id'] ?? $household['householdDocId'] ?? '—';
    $householdId = $household['householdId'] ?? $household['household_id'] ?? $household['houseHoldId'] ?? $householdDocId;
    $head = $household['headName'] ?? $household['head_name'] ?? $household['primaryContactName'] ?? $household['contactName'] ?? $household['headOfHouseName'] ?? '—';
    $phone = $household['phone'] ?? $household['phoneNumber'] ?? $household['contactPhone'] ?? $household['phone_number'] ?? '—';
    $barcode = $household['barcode'] ?? $household['barcodeNumber'] ?? '—';
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
                    class="px-2.5 py-1.5 text-xs bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-neutral-500 transition">
                @foreach([10, 25, 50, 100] as $pp)
                    <option value="{{ $pp }}" {{ $membersLimit == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                @endforeach
            </select>
            <input type="hidden" name="members_page" value="1">
        </form>
    </div>

    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/20">
        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-3">Add Member</h4>
        <form method="POST" action="{{ route('households.members.store', ['ref' => $householdId]) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            @csrf
            <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="Full name"
                   class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none lg:col-span-2">

            <select name="gender"
                    class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                <option value="">Gender</option>
                <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
            </select>

            <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                   class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">

            <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="Phone"
                   class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">

            <select name="relationship_to_head"
                    class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                <option value="Member" {{ old('relationship_to_head', 'Member') === 'Member' ? 'selected' : '' }}>Member</option>
                <option value="Head" {{ old('relationship_to_head') === 'Head' ? 'selected' : '' }}>Head</option>
            </select>

            <input type="text" name="nrc_number" value="{{ old('nrc_number') }}" placeholder="NRC (optional)"
                   class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none sm:col-span-2 lg:col-span-3">

            <div class="sm:col-span-2 lg:col-span-3 flex items-center justify-end">
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium rounded bg-neutral-900 hover:bg-neutral-800 text-white dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200 transition">
                    Add Member
                </button>
            </div>
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
                    <th class="px-5 py-3.5 text-left font-semibold">Actions</th>
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
                        $nrcNumber = $patient['nrcNumber'] ?? $patient['nrc_number'] ?? '—';
                        $status = $patient['status'] ?? 'Active';
                        $relationship = $patient['relationshipToHead'] ?? $patient['relationship_to_head'] ?? 'Member';
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
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-700 dark:bg-neutral-900/30 dark:text-neutral-400">{{ ucfirst((string) $status) }}</span>
                        </td>
                        <td class="px-5 py-3.5 align-top min-w-[280px]">
                            <button type="button"
                                    data-modal-open="manage-member-modal-{{ $rowNum }}"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-neutral-300 dark:border-neutral-600 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700/40">
                                Manage
                            </button>

                            <div id="manage-member-modal-{{ $rowNum }}" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="manage-member-modal-title-{{ $rowNum }}">
                                <div class="absolute inset-0 bg-black/40" data-modal-close="manage-member-modal-{{ $rowNum }}"></div>

                                <div class="relative h-full w-full overflow-y-auto p-3 sm:p-6">
                                    <div class="mx-auto mt-4 sm:mt-10 w-full max-w-2xl rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-xl">
                                        <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
                                            <h3 id="manage-member-modal-title-{{ $rowNum }}" class="text-sm font-semibold text-neutral-900 dark:text-white">
                                                Manage Member
                                            </h3>
                                            <button type="button"
                                                    data-modal-close="manage-member-modal-{{ $rowNum }}"
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-md text-neutral-500 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-700/40"
                                                    aria-label="Close manage member modal">
                                                &times;
                                            </button>
                                        </div>

                                        <div class="p-4 space-y-3">
                                            <form method="POST" action="{{ route('households.members.update', ['ref' => $householdId, 'patientRef' => $patientId]) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 border border-neutral-200 dark:border-neutral-700 rounded-lg bg-white dark:bg-neutral-800">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="full_name" value="{{ $fullName !== '—' ? $fullName : '' }}" required
                                                       class="w-full px-2.5 py-1.5 text-xs border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none sm:col-span-2">
                                                <select name="gender"
                                                        class="w-full px-2.5 py-1.5 text-xs border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                                                    <option value="">Gender</option>
                                                    <option value="Male" {{ strtolower((string) $gender) === 'male' ? 'selected' : '' }}>Male</option>
                                                    <option value="Female" {{ strtolower((string) $gender) === 'female' ? 'selected' : '' }}>Female</option>
                                                </select>
                                                <input type="date" name="date_of_birth" value="{{ $dob && $dob !== '—' ? $dob : '' }}"
                                                       class="w-full px-2.5 py-1.5 text-xs border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                                                <input type="text" name="phone_number" value="{{ $phone !== '—' ? $phone : '' }}" placeholder="Phone"
                                                       class="w-full px-2.5 py-1.5 text-xs border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                                                     <input type="text" name="nrc_number" value="{{ $nrcNumber !== '—' ? $nrcNumber : '' }}" placeholder="NRC"
                                                         class="w-full px-2.5 py-1.5 text-xs border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                                                <select name="relationship_to_head"
                                                        class="w-full px-2.5 py-1.5 text-xs border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                                                    <option value="Member" {{ strtolower((string) $relationship) !== 'head' ? 'selected' : '' }}>Member</option>
                                                    <option value="Head" {{ strtolower((string) $relationship) === 'head' ? 'selected' : '' }}>Head</option>
                                                </select>
                                                <button type="submit"
                                                        class="inline-flex justify-center px-3 py-1.5 text-xs font-semibold rounded bg-neutral-900 hover:bg-neutral-800 text-white dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200 transition">
                                                    Save
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('households.members.transfer', ['ref' => $householdId, 'patientRef' => $patientId]) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 border border-neutral-200 dark:border-neutral-700 rounded-lg bg-white dark:bg-neutral-800">
                                                @csrf
                                                <select name="target_household_id"
                                                        data-current-household="{{ $householdId }}"
                                                        data-search-url-template="{{ route('households.transfer-households.search', ['ref' => '__REF__']) }}"
                                                        required
                                                        class="js-transfer-household-select w-full px-2.5 py-1.5 text-xs border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none sm:col-span-2">
                                                    <option value="">Transfer to household...</option>
                                                    @if(old('target_household_id'))
                                                        <option value="{{ old('target_household_id') }}" selected>{{ old('target_household_id') }}</option>
                                                    @endif
                                                </select>

                                                <label class="inline-flex items-center gap-2 text-xs text-neutral-700 dark:text-neutral-300 sm:col-span-2">
                                                    <input type="checkbox" name="transfer_as_head" value="1" class="rounded border-neutral-300 dark:border-neutral-600">
                                                    Transfer as head of target household
                                                </label>

                                                <button type="submit"
                                                        class="inline-flex justify-center px-3 py-1.5 text-xs font-semibold rounded border border-neutral-300 dark:border-neutral-600 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700/40 transition sm:col-span-2">
                                                    Transfer Member
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('households.members.remove', ['ref' => $householdId, 'patientRef' => $patientId]) }}" onsubmit="return confirm('Remove this member from the household?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="w-full inline-flex justify-center px-3 py-1.5 text-xs font-semibold rounded border border-red-300 text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/20 transition">
                                                    Remove Member
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-14 text-center text-sm text-gray-500 dark:text-gray-400">
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
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition {{ $membersPage <= 1 ? 'border-neutral-100 dark:border-neutral-700 text-neutral-300 dark:text-neutral-600 bg-neutral-50 dark:bg-neutral-800/50 cursor-not-allowed pointer-events-none' : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-900/20 hover:border-neutral-300 dark:hover:border-neutral-700 hover:text-neutral-700 dark:hover:text-neutral-400 shadow-sm' }}">
                Previous
            </a>

            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border bg-neutral-900 border-neutral-900 text-white text-sm font-semibold shadow-sm">
                {{ $membersPage }}
            </span>

            <a href="{{ $membersPage < $membersTotalPages ? request()->fullUrlWithQuery(['members_page' => $membersPage + 1]) : '#' }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition {{ $membersPage >= $membersTotalPages ? 'border-neutral-100 dark:border-neutral-700 text-neutral-300 dark:text-neutral-600 bg-neutral-50 dark:bg-neutral-800/50 cursor-not-allowed pointer-events-none' : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-900/20 hover:border-neutral-300 dark:hover:border-neutral-700 hover:text-neutral-700 dark:hover:text-neutral-400 shadow-sm' }}">
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 31px;
        border-color: #d4d4d8;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 29px;
        font-size: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var openModalId = null;

        function closeModal(modalId) {
            var modal = document.getElementById(modalId);

            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            openModalId = null;
        }

        function initializeTransferSelect($el, dropdownParent) {
            if (!$el || $el.length === 0 || $el.hasClass('select2-hidden-accessible')) {
                return;
            }

            var currentHousehold = $el.data('current-household');
            var template = $el.data('search-url-template') || '';

            if (!currentHousehold || !template) {
                return;
            }

            var searchUrl = template.replace('__REF__', encodeURIComponent(currentHousehold));

            $el.select2({
                width: '100%',
                dropdownParent: dropdownParent,
                placeholder: 'Transfer to household...',
                minimumInputLength: 1,
                ajax: {
                    url: searchUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term || '' };
                    },
                    processResults: function (data) {
                        return {
                            results: Array.isArray(data.results) ? data.results.slice(0, 4) : []
                        };
                    },
                    cache: true
                }
            });
        }

        document.querySelectorAll('[data-modal-open]').forEach(function (button) {
            button.addEventListener('click', function () {
                var modalId = button.getAttribute('data-modal-open');
                var modal = document.getElementById(modalId);

                if (!modal) {
                    return;
                }

                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                openModalId = modalId;

                if (window.jQuery && typeof window.jQuery.fn.select2 === 'function') {
                    var $modal = window.jQuery(modal);
                    $modal.find('.js-transfer-household-select').each(function () {
                        initializeTransferSelect(window.jQuery(this), $modal);
                    });
                }
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                var modalId = button.getAttribute('data-modal-close');
                closeModal(modalId);
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && openModalId) {
                closeModal(openModalId);
            }
        });

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

        if (window.jQuery && typeof window.jQuery.fn.select2 === 'function') {
            window.jQuery('.js-transfer-household-select').each(function () {
                initializeTransferSelect(window.jQuery(this), window.jQuery('body'));
            });
        }
    });
</script>
@endpush
