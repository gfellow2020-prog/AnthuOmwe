@extends('layouts.dashboard')

@section('title', 'Screening Review Queue')

@push('styles')
<style>
    .btn-primary  { display:inline-flex;align-items:center;gap:6px;padding:8px 18px;font-size:13px;font-weight:600;background:#2563eb;color:#fff;border-radius:8px;border:none;cursor:pointer; }
    .btn-primary:hover  { background:#1d4ed8; }
    .btn-success  { display:inline-flex;align-items:center;gap:6px;padding:8px 18px;font-size:13px;font-weight:600;background:#16a34a;color:#fff;border-radius:8px;border:none;cursor:pointer; }
    .btn-success:hover  { background:#15803d; }
</style>
@endpush

@section('page-header')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Screening Review Queue</h1>
        <p class="text-sm text-gray-500 mt-0.5">Post-lab clinical review &amp; prescriptions</p>
    </div>
    <div class="text-sm text-gray-500">{{ now()->format('D, d M Y') }}</div>
</div>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="space-y-6">

    {{-- In Progress --}}
    @if($inProgress->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-green-200">
        <div class="px-6 py-4 border-b border-green-100 flex items-center gap-3">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-base font-semibold text-gray-900">In Progress</h2>
            <span class="ml-auto text-xs font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">{{ $inProgress->total() }}</span>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($inProgress as $enc)
            <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition">
                <div class="flex items-center gap-4">
                    <div class="w-9 h-9 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-sm">
                        {{ strtoupper(substr($enc->patient->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">{{ $enc->patient->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $enc->encounter_number }}</p>
                    </div>
                </div>
                <a href="{{ route('screening-review.show', $enc) }}" class="btn-success text-xs px-3 py-1.5">Review &amp; Prescribe</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Queued --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Awaiting Review</h2>
            <span class="ml-auto text-xs font-semibold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">{{ $queued->total() }}</span>
        </div>

        @if($queued->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400">
            No patients awaiting screening review. Patients arrive here after Lab returns results.
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($queued as $enc)
            <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition">
                <div class="flex items-center gap-4">
                    <div class="w-9 h-9 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-sm">
                        {{ strtoupper(substr($enc->patient->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">{{ $enc->patient->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $enc->encounter_number }} · {{ $enc->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('screening-review.receive', $enc) }}">
                    @csrf
                    <button type="submit" class="btn-primary text-xs px-3 py-1.5">Receive Patient</button>
                </form>
            </div>
            @endforeach
        </div>
        @if($queued->hasPages())
        <div class="px-6 py-3 border-t border-gray-100">{{ $queued->links() }}</div>
        @endif
        @endif
    </div>

</div>
@endsection
