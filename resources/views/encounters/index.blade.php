@extends('layouts.dashboard')

@section('title', 'All Encounters')

@section('breadcrumbs')
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">All Encounters</span>
@endsection

@section('content')

<div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-neutral-50 dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700">
                <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-400">Encounter #</th>
                <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-400">Patient</th>
                <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-400">Stage</th>
                <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-400">Status</th>
                <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-400">Started</th>
                <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-400">Started By</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @forelse($encounters as $enc)
            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                <td class="px-5 py-3 font-mono text-xs text-neutral-700 dark:text-neutral-300">{{ $enc->encounter_number }}</td>
                <td class="px-5 py-3 font-medium text-neutral-900 dark:text-neutral-100">{{ $enc->patient->full_name }}</td>
                <td class="px-5 py-3">
                    @php
                        $stageVal = $enc->current_stage?->value ?? 'unknown';
                        $stageBadge = match($stageVal) {
                            'completed'        => 'badge-completed',
                            'pharmacy'         => 'badge-progress',
                            'screening_review' => 'badge-progress',
                            'lab'              => 'badge-progress',
                            'screening'        => 'badge-progress',
                            'triage'           => 'badge-queued',
                            default            => 'badge-default',
                        };
                    @endphp
                    <span class="badge {{ $stageBadge }}">{{ ucfirst(str_replace('_', ' ', $stageVal)) }}</span>
                </td>
                <td class="px-5 py-3">
                    @php
                        $statusVal = $enc->current_status?->value ?? 'unknown';
                        $statusBadge = match($statusVal) {
                            'completed'   => 'badge-completed',
                            'in_progress' => 'badge-progress',
                            'queued'      => 'badge-queued',
                            default       => 'badge-default',
                        };
                    @endphp
                    <span class="badge {{ $statusBadge }}">{{ ucfirst(str_replace('_', ' ', $statusVal)) }}</span>
                </td>
                <td class="px-5 py-3 text-neutral-500 text-xs">{{ $enc->started_at?->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-neutral-600 dark:text-neutral-400 text-xs">{{ $enc->startedBy?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('encounters.show', $enc) }}" class="text-xs text-neutral-900 dark:text-neutral-100 hover:underline font-medium">View Profile</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">No encounters found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($encounters->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $encounters->links() }}
    </div>
    @endif
</div>

@endsection
