@extends('layouts.dashboard')

@section('title', 'Startup Medications — Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('triage.queue') }}" class="hover:text-neutral-700 transition">Triage</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Startup Medications</span>
@endsection

@section('content')

{{-- Search --}}
<div class="mb-5">
    <form method="GET" action="{{ route('triage.startup-medications') }}" class="flex items-center gap-3">
        <input type="text" name="search" value="{{ $search }}"
               class="w-full max-w-md px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none"
               placeholder="Search by medication, patient name or encounter number…">
        <button type="submit" class="px-4 py-2 text-sm font-medium rounded bg-neutral-900 hover:bg-neutral-800 text-white transition">Search</button>
        @if($search)
            <a href="{{ route('triage.startup-medications') }}" class="text-sm text-neutral-500 hover:text-neutral-700">Clear</a>
        @endif
    </form>
</div>

@if($medications->isEmpty())
    <div class="rounded border border-dashed border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-8 text-center text-neutral-500 dark:text-neutral-400 text-sm">
        No startup medications found.
    </div>
@else
    <div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800">
                        <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Patient</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Encounter</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Medication</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Dosage</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Route</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Frequency</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Given At</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Recorded By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    @foreach($medications as $med)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 cursor-pointer"
                        onclick="window.location='{{ route('triage.show', $med->encounter_id) }}'">
                        <td class="px-5 py-3 font-medium text-neutral-900 dark:text-white">{{ $med->patient->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-neutral-700 dark:text-neutral-300">{{ $med->encounter->encounter_number ?? '—' }}</td>
                        <td class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">{{ $med->medication_name }}</td>
                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">{{ $med->dosage ?? '—' }}</td>
                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">{{ $med->route ?? '—' }}</td>
                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">{{ $med->frequency ?? '—' }}</td>
                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">{{ $med->administered_at ? $med->administered_at->format('d M Y H:i') : '—' }}</td>
                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">{{ $med->recordedBy->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mb-5">
        {{ $medications->links() }}
    </div>
@endif

@endsection
