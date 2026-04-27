@extends('layouts.dashboard')

@section('title', 'All Vitals — Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('triage.queue') }}" class="hover:text-neutral-700 transition">Triage</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Vitals Records</span>
@endsection

@section('content')

{{-- Search --}}
<div class="mb-6">
    <form method="GET" action="{{ route('triage.vitals') }}" class="flex items-center gap-3">
        <div class="relative flex-1 max-w-md">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by patient name or encounter number…"
                   class="w-full pl-10 pr-4 py-2 text-sm border border-neutral-300 dark:border-neutral-700 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white focus:border-transparent">
        </div>
        <button type="submit" class="btn-primary text-xs px-4 py-2">Search</button>
        @if($search)
        <a href="{{ route('triage.vitals') }}" class="text-xs text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300">Clear</a>
        @endif
    </form>
</div>

{{-- Results --}}
<div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
    @if($records->isEmpty())
    <div class="px-6 py-10 text-center text-sm text-neutral-400">
        No vitals records found.
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-neutral-50 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 font-semibold">Patient</th>
                    <th class="px-6 py-3 font-semibold">Encounter</th>
                    <th class="px-6 py-3 font-semibold">Temp (°C)</th>
                    <th class="px-6 py-3 font-semibold">BP</th>
                    <th class="px-6 py-3 font-semibold">Pulse</th>
                    <th class="px-6 py-3 font-semibold">SpO₂</th>
                    <th class="px-6 py-3 font-semibold">Weight</th>
                    <th class="px-6 py-3 font-semibold">BMI</th>
                    <th class="px-6 py-3 font-semibold">Nurse</th>
                    <th class="px-6 py-3 font-semibold">Recorded</th>
                    <th class="px-6 py-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @foreach($records as $record)
                <tr onclick="window.location='{{ route('triage.show', $record->encounter_id) }}'" class="hover:bg-neutral-50 dark:hover:bg-neutral-800 transition cursor-pointer">
                    <td class="px-6 py-4 font-semibold text-neutral-900 dark:text-neutral-100">
                        {{ $record->patient->full_name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400">
                        {{ $record->encounter->encounter_number ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                        {{ $record->temperature ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                        @if($record->systolic_bp && $record->diastolic_bp)
                            {{ $record->systolic_bp }}/{{ $record->diastolic_bp }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                        {{ $record->pulse ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                        {{ $record->oxygen_saturation ? $record->oxygen_saturation . '%' : '—' }}
                    </td>
                    <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                        {{ $record->weight ? $record->weight . ' kg' : '—' }}
                    </td>
                    <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                        {{ $record->bmi ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400">
                        {{ $record->nurse->name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-neutral-500 text-xs">
                        {{ $record->triage_at ? $record->triage_at->format('d M Y H:i') : $record->created_at->format('d M Y H:i') }}
                    </td>
                    <td class="px-6 py-4" onclick="event.stopPropagation()">
                        <a href="{{ route('triage.show', $record->encounter_id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-700 hover:text-neutral-900 dark:hover:text-white transition" title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="px-6 py-3 border-t border-neutral-100 dark:border-neutral-700">
        {{ $records->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
