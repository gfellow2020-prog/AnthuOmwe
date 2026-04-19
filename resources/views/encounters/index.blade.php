@extends('layouts.dashboard')

@section('title', 'All Encounters')

@push('styles')
<style>
    .badge { display:inline-flex;align-items:center;padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600; }
    .badge-completed { background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0; }
    .badge-locked    { background:#fef2f2;color:#991b1b;border:1px solid #fecaca; }
    .badge-progress  { background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe; }
    .badge-queued    { background:#fefce8;color:#854d0e;border:1px solid #fef08a; }
    .badge-default   { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
</style>
@endpush

@section('page-header')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Encounters</h1>
        <p class="text-sm text-gray-500 mt-0.5">All patient visits</p>
    </div>
    <div class="text-sm text-gray-500">{{ now()->format('D, d M Y') }}</div>
</div>
@endsection

@section('content')

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="px-5 py-3 text-left font-semibold text-gray-600">Encounter #</th>
                <th class="px-5 py-3 text-left font-semibold text-gray-600">Patient</th>
                <th class="px-5 py-3 text-left font-semibold text-gray-600">Stage</th>
                <th class="px-5 py-3 text-left font-semibold text-gray-600">Status</th>
                <th class="px-5 py-3 text-left font-semibold text-gray-600">Started</th>
                <th class="px-5 py-3 text-left font-semibold text-gray-600">Started By</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($encounters as $enc)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3 font-mono text-xs text-gray-700">{{ $enc->encounter_number }}</td>
                <td class="px-5 py-3 font-medium text-gray-900">{{ $enc->patient->full_name }}</td>
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
                <td class="px-5 py-3 text-gray-500 text-xs">{{ $enc->started_at?->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-gray-600 text-xs">{{ $enc->startedBy?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('encounters.show', $enc) }}" class="text-xs text-blue-600 hover:underline font-medium">View Profile</a>
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
