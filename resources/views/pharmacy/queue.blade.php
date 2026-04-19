@extends('layouts.dashboard')

@section('title', 'Pharmacy Queue')

@push('styles')
<style>
    .btn-primary { display:inline-flex;align-items:center;gap:6px;padding:8px 18px;font-size:13px;font-weight:600;background:#2563eb;color:#fff;border-radius:8px;border:none;cursor:pointer; }
    .btn-primary:hover { background:#1d4ed8; }
    .btn-success { display:inline-flex;align-items:center;gap:6px;padding:8px 18px;font-size:13px;font-weight:600;background:#16a34a;color:#fff;border-radius:8px;border:none;cursor:pointer; }
    .btn-success:hover { background:#15803d; }
</style>
@endpush

@section('page-header')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pharmacy Queue</h1>
        <p class="text-sm text-gray-500 mt-0.5">Dispense medications &amp; close encounters</p>
    </div>
    <div class="text-sm text-gray-500">{{ now()->format('D, d M Y') }}</div>
</div>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="space-y-6">

    {{-- Queued --}}
    @if($encounters->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-blue-200">
        <div class="px-6 py-4 border-b border-blue-100 flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Awaiting Dispensing</h2>
            <span class="ml-auto text-xs font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ $encounters->count() }}</span>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($encounters as $enc)
            <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition">
                <div class="flex items-center gap-4">
                    <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold text-sm">
                        {{ strtoupper(substr($enc->patient->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">{{ $enc->patient->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $enc->encounter_number }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('pharmacy.receive', $enc) }}">
                    @csrf
                    <button type="submit" class="btn-primary text-xs px-3 py-1.5">Receive</button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 px-6 py-10 text-center text-gray-500 text-sm">
        No encounters currently in the pharmacy queue.
    </div>
    @endif

</div>

@endsection
