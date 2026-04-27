@extends('layouts.dashboard')

@section('title', 'Pharmacy Queue')

@section('breadcrumbs')
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Pharmacy Queue</span>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 rounded text-sm">{{ session('success') }}</div>
@endif

<div class="space-y-6">

    {{-- Queued --}}
    @if($encounters->isNotEmpty())
    <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center gap-3">
            <div class="w-8 h-8 bg-neutral-200 dark:bg-neutral-700 rounded flex items-center justify-center">
                <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Awaiting Dispensing</h2>
            <span class="ml-auto text-xs font-semibold bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 px-2 py-0.5 rounded">{{ $encounters->count() }}</span>
        </div>
        <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @foreach($encounters as $enc)
            <div class="flex items-center justify-between px-6 py-4 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                <div class="flex items-center gap-4">
                    <div class="w-9 h-9 bg-neutral-200 dark:bg-neutral-700 rounded-full flex items-center justify-center text-neutral-700 dark:text-neutral-300 font-bold text-sm">
                        {{ strtoupper(substr($enc->patient->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-neutral-900 dark:text-neutral-100 text-sm">{{ $enc->patient->full_name }}</p>
                        <p class="text-xs text-neutral-500">{{ $enc->encounter_number }}</p>
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
    <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700 px-6 py-10 text-center text-neutral-500 text-sm">
        No encounters currently in the pharmacy queue.
    </div>
    @endif

</div>

@endsection
