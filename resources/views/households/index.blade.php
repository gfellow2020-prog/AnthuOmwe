@extends('layouts.dashboard')

@section('title', 'Households — Anthu Omwe Health Center')

@section('page-header')
    @include('components.page-header', [
        'title'    => 'Households',
        'subtitle' => 'Browse household barcode records',
    ])
@endsection

@section('content')

@if($error)
    <div class="mb-5 flex items-start gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.293 4.293a1 1 0 011.414 0l7 7a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7a1 1 0 010-1.414l7-7z"/>
        </svg>
        <span>{{ $error }}</span>
    </div>
@endif

<form method="GET" action="{{ route('households.index') }}" class="mb-5 flex flex-col sm:flex-row gap-3">
    <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ $search }}" placeholder="Search barcode, household ID, name, village..."
               class="w-full pl-9 pr-4 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
    </div>

    <select name="limit" class="px-3 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        @foreach([10, 25, 50, 100] as $perPage)
            <option value="{{ $perPage }}" {{ $limit == $perPage ? 'selected' : '' }}>{{ $perPage }} per page</option>
        @endforeach
    </select>

    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition shadow-sm whitespace-nowrap">
        Search
    </button>

    @if($search)
        <a href="{{ route('households.index') }}" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-medium rounded-lg transition whitespace-nowrap">
            Clear
        </a>
    @endif
</form>

<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">All Households</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                @if($total > 0)
                    Showing {{ number_format($from) }}-{{ number_format($to) }} of {{ number_format($total) }} records
                @else
                    No records found
                @endif
            </p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800">
            {{ number_format($total) }} Total
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <th class="px-5 py-3.5 text-left font-semibold">#</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Barcode</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Household ID</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Household Name</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Head / Contact</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Location</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Status</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($households as $index => $row)
                    @php
                        $rowNum = (($page - 1) * $limit) + $index + 1;

                        $barcode = $row['barcode'] ?? $row['code'] ?? $row['barcodeId'] ?? '—';
                        $householdId = $row['householdId'] ?? $row['household_id'] ?? $row['houseHoldId'] ?? $row['id'] ?? '—';
                        $householdName = $row['householdName'] ?? $row['household_name'] ?? $row['name'] ?? $row['headOfHouseName'] ?? '—';

                        $head = $row['headName'] ?? $row['head_name'] ?? $row['primaryContactName'] ?? $row['contactName'] ?? $row['headOfHouseName'] ?? '—';
                        $phone = $row['phone'] ?? $row['phoneNumber'] ?? $row['contactPhone'] ?? $row['phone_number'] ?? '—';

                        $location = $row['village'] ?? $row['ward'] ?? $row['district'] ?? $row['address'] ?? '—';

                        $status = $row['status'] ?? 'Active';
                        $statusKey = strtolower((string) $status);
                        $statusStyles = [
                            'active' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                            'inactive' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                            'archived' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                        ];
                        $statusClass = $statusStyles[$statusKey] ?? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';

                        $createdAt = $row['createdAt'] ?? $row['created_at'] ?? null;
                        if (is_array($createdAt) || is_object($createdAt)) { $createdAt = null; }

                        $rowRef = null;
                        if ($barcode !== '—' && $barcode !== '') {
                            $rowRef = $barcode;
                        } elseif ($householdId !== '—' && $householdId !== '') {
                            $rowRef = $householdId;
                        }

                        $rowHref = $rowRef ? route('households.show', ['ref' => $rowRef]) : null;
                    @endphp

                    <tr
                        @if($rowHref)
                            role="link"
                            tabindex="0"
                            data-href="{{ $rowHref }}"
                            title="Open household details"
                        @endif
                        class="transition household-row {{ $rowHref ? 'cursor-pointer hover:bg-blue-50/70 dark:hover:bg-blue-900/20 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset' : 'hover:bg-gray-50 dark:hover:bg-gray-700/30' }}">
                        <td class="px-5 py-3.5 text-xs text-gray-400 dark:text-gray-500 font-mono">{{ $rowNum }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300 font-mono">{{ $barcode }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300 font-mono">{{ $householdId }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-800 dark:text-gray-200 font-medium">{{ $householdName }}</td>
                        <td class="px-5 py-3.5">
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $head }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $phone }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-600 dark:text-gray-300">{{ $location }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ ucfirst((string) $status) }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            @if($createdAt && is_string($createdAt))
                                @php
                                    try { echo \Carbon\Carbon::parse($createdAt)->format('d M Y'); }
                                    catch(\Exception $e) { echo '—'; }
                                @endphp
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-14 text-center text-sm text-gray-500 dark:text-gray-400">
                            No household records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between gap-3 px-5 py-4 border-t border-gray-100 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400">Page {{ $page }} of {{ $totalPages }}</p>

        <div class="flex items-center gap-2">
            <a href="{{ $page > 1 ? request()->fullUrlWithQuery(['page' => $page - 1]) : '#' }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition {{ $page <= 1 ? 'border-gray-100 dark:border-gray-700 text-gray-300 dark:text-gray-600 bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed pointer-events-none' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-700 hover:text-blue-700 dark:hover:text-blue-400 shadow-sm' }}">
                Previous
            </a>

            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border bg-blue-600 border-blue-600 text-white text-sm font-semibold shadow-sm">
                {{ $page }}
            </span>

            <a href="{{ $hasMore ? request()->fullUrlWithQuery(['page' => $page + 1]) : '#' }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition {{ !$hasMore ? 'border-gray-100 dark:border-gray-700 text-gray-300 dark:text-gray-600 bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed pointer-events-none' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-700 hover:text-blue-700 dark:hover:text-blue-400 shadow-sm' }}">
                Next
            </a>
        </div>
    </div>
</div>

<div class="h-6"></div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rows = document.querySelectorAll('tr.household-row[data-href]');

        rows.forEach(function (row) {
            row.addEventListener('click', function () {
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
