@extends('layouts.dashboard')

@section('title', 'Patient Visit History — Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('patients.index') }}" class="hover:text-neutral-700 transition">Patients</a>
<span class="mx-2">/</span>
<a href="{{ route('patients.show', ['ref' => $patient['patient_id']]) }}" class="hover:text-neutral-700 transition">{{ $patient['full_name'] ?? 'Patient' }}</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Visit History</span>
@endsection

@section('content')

@if($encounters->isEmpty())
    <div class="rounded border border-dashed border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-8 text-center text-neutral-500 dark:text-neutral-400 text-sm">
        No past encounters found for this patient.
    </div>
@else
    <div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800">
                    <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Encounter #</th>
                    <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Stage</th>
                    <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Status</th>
                    <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Visit Type</th>
                    <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Started</th>
                    <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($encounters as $enc)
                    <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                        <td class="px-5 py-3 font-mono text-neutral-900 dark:text-white">{{ $enc->encounter_number ?? '—' }}</td>
                        <td class="px-5 py-3 text-neutral-700 dark:text-neutral-300">{{ ucfirst($enc->current_stage ?? '—') }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded
                                {{ ($enc->current_status ?? '') === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300' }}">
                                {{ ucfirst($enc->current_status ?? '—') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-neutral-700 dark:text-neutral-300">{{ ucfirst($enc->visit_type ?? '—') }}</td>
                        <td class="px-5 py-3 text-neutral-700 dark:text-neutral-300">
                            {{ $enc->started_at ? \Carbon\Carbon::parse($enc->started_at)->format('d M Y H:i') : '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('encounters.show', ['encounter' => $enc->id]) }}" class="text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mb-5">
        {{ $encounters->links() }}
    </div>
@endif

@endsection
