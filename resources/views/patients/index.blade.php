@extends('layouts.dashboard')

@section('title', 'Patients — Anthu Omwe Health Center')

{{-- ================================================================
     PATIENTS INDEX PAGE
     ================================================================ --}}

@section('breadcrumbs')
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Patients</span>
@endsection

@section('content')

{{-- Error Banner --}}
@if($error)
    <div class="mb-5 flex items-start gap-3 p-4 rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900 text-neutral-700 dark:text-neutral-300 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.293 4.293a1 1 0 011.414 0l7 7a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7a1 1 0 010-1.414l7-7z"/>
        </svg>
        <span>{{ $error }}</span>
    </div>
@endif

{{-- ================================================================
     FILTERS & SEARCH BAR
     ================================================================ --}}
<form method="GET" action="{{ route('patients.index') }}"
      class="mb-5 flex flex-col sm:flex-row gap-3">

    {{-- Search --}}
    <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400 pointer-events-none"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Search by name, ID, phone..."
               class="w-full pl-9 pr-4 py-2.5 text-sm bg-white dark:bg-neutral-900
                      border border-neutral-300 dark:border-neutral-700 rounded
                      text-neutral-800 dark:text-neutral-200
                      placeholder-neutral-400 dark:placeholder-neutral-500
                      focus:outline-none focus:ring-2 focus:ring-neutral-900 focus:border-transparent transition">
    </div>

    {{-- Per page --}}
    <select name="limit"
            class="px-3 py-2.5 text-sm bg-white dark:bg-neutral-900
                   border border-neutral-300 dark:border-neutral-700 rounded
                   text-neutral-800 dark:text-neutral-200
                   focus:outline-none focus:ring-2 focus:ring-neutral-900 transition">
        @foreach([10, 25, 50, 100] as $perPage)
            <option value="{{ $perPage }}" {{ $limit == $perPage ? 'selected' : '' }}>
                {{ $perPage }} per page
            </option>
        @endforeach
    </select>

    {{-- Search button --}}
    <button type="submit"
            class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-700 text-white text-sm font-medium
                   rounded transition whitespace-nowrap">
        Search
    </button>

    @if($search)
        <a href="{{ route('patients.index') }}"
           class="px-4 py-2.5 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700
                  text-neutral-700 dark:text-neutral-300 text-sm font-medium rounded transition whitespace-nowrap">
            Clear
        </a>
    @endif
</form>

{{-- ================================================================
     PATIENTS TABLE CARD
     ================================================================ --}}
<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden">

    {{-- Card Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-5 py-4
                border-b border-neutral-200 dark:border-neutral-700">
        <div>
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">All Patients</h3>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                @if(count($patients) > 0)
                    Showing {{ number_format($from) }}–{{ number_format($to) }}
                    @if($totalIsKnown) of {{ number_format($total) }} patients @endif
                @else
                    No records found
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium
                         bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300
                         border border-neutral-300 dark:border-neutral-600">
                @if($totalIsKnown) {{ number_format($total) }} Total @else Page {{ $page }} @endif
            </span>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-neutral-50 dark:bg-neutral-800
                           text-xs uppercase tracking-wider
                           text-neutral-600 dark:text-neutral-400
                           border-b border-neutral-200 dark:border-neutral-700">
                    <th class="px-5 py-3.5 text-left font-semibold">#</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Patient</th>
                    <th class="px-5 py-3.5 text-left font-semibold">NRC</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Gender</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Age / DOB</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Phone</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Village</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Status</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Registered</th>
                    <th class="px-5 py-3.5 text-center font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">

                @forelse($patients as $index => $patient)
                @php
                    // Safely pluck common field names from the API response
                    $pid       = $patient['id']             ?? $patient['_id']          ?? '—';
                    $pidShort  = $patient['patientId']      ?? $patient['patient_id']   ?? (is_string($pid) ? strtoupper(substr($pid, 0, 8)) : '—');
                    $firstName = $patient['firstName']      ?? $patient['first_name']   ?? $patient['firstname']  ?? '';
                    $lastName  = $patient['lastName']       ?? $patient['last_name']    ?? $patient['lastname']   ?? '';
                    $fullName  = trim("$firstName $lastName") ?: ($patient['name'] ?? $patient['fullName'] ?? '—');
                    $email     = $patient['email']          ?? '—';
                    $gender    = $patient['gender']         ?? $patient['sex']          ?? '—';
                    $nrc       = $patient['nrcNumber']      ?? $patient['nrc_number']   ?? '—';
                    $barcode   = $patient['barcode']        ?? $patient['barcodeId']    ?? '—';
                    $dob       = $patient['dateOfBirth']    ?? $patient['dob']          ?? $patient['date_of_birth'] ?? null;
                    $age       = $patient['age']            ?? null;
                    $phone     = $patient['phone']          ?? $patient['phoneNumber']  ?? $patient['mobile']     ?? '—';
                    $address   = $patient['address']        ?? $patient['homeAddress']  ?? '—';
                    $status    = $patient['status']         ?? 'Active';
                    $createdAt = $patient['createdAt']      ?? $patient['created_at']   ?? null;

                    // Normalize date fields — the API may return them as arrays or objects
                    if (is_array($dob) || is_object($dob))   { $dob       = null; }
                    if (is_array($createdAt) || is_object($createdAt)) { $createdAt = null; }

                    // Compute age from DOB if not provided
                    if (!$age && $dob && is_string($dob)) {
                        try { $age = \Carbon\Carbon::parse($dob)->age; } catch(\Exception $e) { $age = null; }
                    }

                    // Status badge
                    $statusStyles = [
                        'active'     => 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800/30 dark:text-neutral-400',
                        'inactive'   => 'bg-neutral-200 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-400',
                        'admitted'   => 'bg-neutral-300 text-neutral-800 dark:bg-neutral-700/30 dark:text-neutral-300',
                        'discharged' => 'bg-neutral-400 text-neutral-900 dark:bg-neutral-600/30 dark:text-neutral-300',
                        'critical'   => 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900',
                    ];
                    $statusClass = $statusStyles[strtolower($status)] ?? 'bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-400';

                    $rowNum = (($page - 1) * $limit) + $index + 1;
                    $rowHref = null;
                    $patientDocId = $patient['id'] ?? $patient['_id'] ?? null;
                    if ($patientDocId) {
                        $rowHref = route('patients.show', ['ref' => $patientDocId]);
                    } elseif ($pidShort && $pidShort !== '—') {
                        $rowHref = route('patients.show', ['ref' => $pidShort]);
                    }
                @endphp

                <tr
                    @if($rowHref)
                        role="link"
                        tabindex="0"
                        data-href="{{ $rowHref }}"
                        title="Open patient"
                    @endif
                    class="transition group patient-row {{ $rowHref ? 'cursor-pointer hover:bg-neutral-100/70 dark:hover:bg-neutral-800/20 focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-inset' : 'hover:bg-gray-50 dark:hover:bg-gray-700/30' }}">

                    {{-- Row # --}}
                    <td class="px-5 py-3.5 text-xs text-gray-400 dark:text-gray-500 font-mono">
                        {{ $rowNum }}
                    </td>

                    {{-- Patient Name + Email --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-neutral-600 to-neutral-800 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($firstName ?: $fullName, 0, 1)) }}{{ strtoupper(substr($lastName, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-gray-800 dark:text-gray-100 truncate">{{ $fullName }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 truncate font-mono">{{ $pidShort }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- NRC --}}
                    <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300 font-mono whitespace-nowrap">
                        {{ $nrc ?: '—' }}
                    </td>

                    {{-- Gender --}}
                    <td class="px-5 py-3.5">
                        @if(strtolower($gender) === 'male')
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 9a6 6 0 1 0 4.09 10.328L16 22.414 17.414 21l-2.905-2.905A6 6 0 0 0 9 9zm0 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm7-7h2v3h3v2h-3v3h-2V9h-3V7h3V4z"/></svg>
                                Male
                            </span>
                        @elseif(strtolower($gender) === 'female')
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-neutral-600 dark:text-neutral-400">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11 2a6 6 0 1 1 2 11.944V16h2v2h-2v2H11v-2H9v-2h2v-2.056A6 6 0 0 1 11 2zm0 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/></svg>
                                Female
                            </span>
                        @else
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ ucfirst($gender) }}</span>
                        @endif
                    </td>

                    {{-- Age / DOB --}}
                    <td class="px-5 py-3.5">
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $age ? $age . ' yrs' : '—' }}
                        </span>
                        @if($dob && is_string($dob))
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                @php
                                    try { echo \Carbon\Carbon::parse($dob)->format('d M Y'); }
                                    catch(\Exception $e) { echo '—'; }
                                @endphp
                            </p>
                        @endif
                    </td>

                    {{-- Phone --}}
                    <td class="px-5 py-3.5 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                        {{ $phone }}
                    </td>

                    {{-- Address --}}
                    <td class="px-5 py-3.5 max-w-[160px]">
                        <p class="text-sm text-gray-600 dark:text-gray-300 truncate" title="{{ $address }}">
                            {{ $address }}
                        </p>
                    </td>

                    {{-- Status --}}
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>

                    {{-- Registered --}}
                    <td class="px-5 py-3.5 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        @if($createdAt && is_string($createdAt))
                            @php
                                try {
                                    $ca = \Carbon\Carbon::parse($createdAt);
                                    echo $ca->format('d M Y') . '<br><span class="text-gray-400 dark:text-gray-500">' . $ca->format('h:i A') . '</span>';
                                } catch(\Exception $e) {
                                    echo '—';
                                }
                            @endphp
                        @else
                            —
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-1 opacity-100 transition">
                            <button title="View"
                                    class="js-row-action p-1.5 rounded-lg text-neutral-500 hover:bg-neutral-50 hover:text-neutral-600
                                           dark:hover:bg-neutral-900/20 dark:hover:text-neutral-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            <button title="Edit"
                                    class="js-row-action p-1.5 rounded-lg text-neutral-500 hover:bg-neutral-50 hover:text-neutral-600
                                           dark:hover:bg-neutral-900/20 dark:hover:text-neutral-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="11" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No patients found</p>
                            @if($search)
                                <p class="text-xs text-gray-400 dark:text-gray-500">Try clearing the search filter</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    {{-- ================================================================
         PAGINATION  (server-side: only $limit records loaded per page)
         ================================================================ --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-5 py-4
                border-t border-gray-100 dark:border-gray-700">

        {{-- Left: record count info --}}
        <p class="text-sm text-gray-500 dark:text-gray-400 order-2 sm:order-1">
            @if(count($patients) > 0)
                Showing
                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ number_format($from) }}</span>
                &ndash;
                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ number_format($to) }}</span>
                @if($totalIsKnown)
                    of <span class="font-semibold text-gray-800 dark:text-gray-100">{{ number_format($total) }}</span> patients
                @else
                    &nbsp;&mdash;&nbsp;Page <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $page }}</span>
                @endif
            @else
                <span class="text-gray-400 dark:text-gray-500">No patients found</span>
            @endif
        </p>

        {{-- Right: navigation controls --}}
        <div class="flex items-center gap-2 order-1 sm:order-2">

            {{-- « First page --}}
            @if($totalIsKnown && $totalPages > 1)
            <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}"
               title="First page"
               class="inline-flex items-center justify-center w-9 h-9 rounded-lg border text-sm transition
                      {{ $page <= 1
                          ? 'border-gray-100 dark:border-gray-700 text-gray-300 dark:text-gray-600 bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed pointer-events-none'
                          : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/>
                </svg>
            </a>
            @endif

            {{-- ← Previous page --}}
            <a href="{{ $page > 1 ? request()->fullUrlWithQuery(['page' => $page - 1]) : '#' }}"
               title="Previous page"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition
                      {{ $page <= 1
                          ? 'border-gray-100 dark:border-gray-700 text-gray-300 dark:text-gray-600 bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed pointer-events-none'
                          : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-neutral-100 dark:hover:bg-neutral-800/20 hover:border-neutral-300 dark:hover:border-neutral-700 hover:text-neutral-700 dark:hover:text-neutral-400 shadow-sm' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Previous
            </a>

            {{-- Numbered page pills (only when total is known) --}}
            @if($totalIsKnown && $totalPages > 1)
                @php
                    $winStart = max(1, $page - 2);
                    $winEnd   = min($totalPages, $winStart + 4);
                    $winStart = max(1, $winEnd - 4);
                @endphp

                @if($winStart > 1)
                    <span class="hidden sm:inline-block px-1 text-sm text-neutral-300 dark:text-neutral-600">…</span>
                @endif

                @for($i = $winStart; $i <= $winEnd; $i++)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                       class="hidden sm:inline-flex items-center justify-center w-9 h-9 rounded-lg border text-sm font-medium transition
                              {{ $i === $page
                                  ? 'bg-neutral-900 border-neutral-900 text-white shadow-sm'
                                  : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 hover:border-neutral-300 hover:text-neutral-700 dark:hover:bg-neutral-900/20 dark:hover:text-neutral-400' }}">
                        {{ $i }}
                    </a>
                @endfor

                @if($winEnd < $totalPages)
                    <span class="hidden sm:inline-block px-1 text-sm text-neutral-300 dark:text-neutral-600">…</span>
                @endif
            @else
                {{-- No known total: just show the current page badge --}}
                <span class="hidden sm:inline-flex items-center justify-center px-3 h-9 rounded-lg border
                             border-neutral-900 bg-neutral-900 text-white text-sm font-semibold shadow-sm">
                    {{ $page }}
                </span>
            @endif

            {{-- Next page → --}}
            <a href="{{ $hasMore ? request()->fullUrlWithQuery(['page' => $page + 1]) : '#' }}"
               title="Next page"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition
                      {{ !$hasMore
                          ? 'border-neutral-100 dark:border-neutral-700 text-neutral-300 dark:text-neutral-600 bg-neutral-50 dark:bg-neutral-800/50 cursor-not-allowed pointer-events-none'
                          : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-900/20 hover:border-neutral-300 dark:hover:border-neutral-700 hover:text-neutral-700 dark:hover:text-neutral-400 shadow-sm' }}">>
                Next
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- » Last page --}}
            @if($totalIsKnown && $totalPages > 1)
            <a href="{{ request()->fullUrlWithQuery(['page' => $totalPages]) }}"
               title="Last page"
               class="inline-flex items-center justify-center w-9 h-9 rounded-lg border text-sm transition
                      {{ $page >= $totalPages
                          ? 'border-gray-100 dark:border-gray-700 text-gray-300 dark:text-gray-600 bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed pointer-events-none'
                          : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M6 5l7 7-7 7"/>
                </svg>
            </a>
            @endif

        </div>{{-- /nav --}}

    </div>

</div>

<div class="h-6"></div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rows = document.querySelectorAll('tr.patient-row[data-href]');

        rows.forEach(function (row) {
            row.addEventListener('click', function (event) {
                if (event.target.closest('.js-row-action')) {
                    return;
                }

                const href = row.getAttribute('data-href');
                if (href) {
                    window.location.href = href;
                }
            });

            row.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    const href = row.getAttribute('data-href');
                    if (href) {
                        window.location.href = href;
                    }
                }
            });
        });
    });
</script>
@endpush
