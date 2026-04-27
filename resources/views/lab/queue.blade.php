@extends('layouts.dashboard')

@section('title', 'Lab Queue')

@section('breadcrumbs')
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Lab Queue</span>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 rounded text-sm">{{ session('success') }}</div>
@endif

<div x-data="{ tab: 'waiting' }" class="space-y-0">

    {{-- Tab Bar --}}
    <div class="bg-white dark:bg-neutral-900 rounded-t border border-b-0 border-neutral-300 dark:border-neutral-700">
        <div class="flex">
            <button @click="tab = 'waiting'" :class="tab === 'waiting' ? 'border-b-2 border-neutral-900 dark:border-white text-neutral-900 dark:text-white' : 'border-b-2 border-transparent text-neutral-400 hover:text-neutral-600'"
                    class="flex items-center gap-2 px-6 py-4 text-sm font-semibold transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Waiting in Queue
                <span :class="tab === 'waiting' ? 'bg-neutral-900 dark:bg-white text-white dark:text-neutral-900' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-400'"
                      class="text-xs font-bold px-2 py-0.5 rounded-full transition-colors duration-200">{{ $queued->total() }}</span>
            </button>
            <button @click="tab = 'progress'" :class="tab === 'progress' ? 'border-b-2 border-neutral-900 dark:border-white text-neutral-900 dark:text-white' : 'border-b-2 border-transparent text-neutral-400 hover:text-neutral-600'"
                    class="flex items-center gap-2 px-6 py-4 text-sm font-semibold transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                In Progress
                <span :class="tab === 'progress' ? 'bg-neutral-900 dark:bg-white text-white dark:text-neutral-900' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-400'"
                      class="text-xs font-bold px-2 py-0.5 rounded-full transition-colors duration-200">{{ $inProgress->total() }}</span>
            </button>
        </div>
    </div>

    {{-- Tab Content --}}
    <div class="bg-white dark:bg-neutral-900 rounded-b border border-neutral-300 dark:border-neutral-700">

        {{-- Waiting in Queue --}}
        <div x-show="tab === 'waiting'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @if($queued->isEmpty())
            <div class="px-6 py-10 text-center text-sm text-neutral-500">
                No patients waiting. Lab receives patients when Screening requests tests.
            </div>
            @else
            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach($queued as $enc)
                <div class="flex items-center justify-between px-6 py-4 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 bg-neutral-200 dark:bg-neutral-700 rounded-full flex items-center justify-center text-neutral-700 dark:text-neutral-300 font-bold text-sm">
                            {{ strtoupper(substr($enc->patient->full_name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-neutral-900 dark:text-neutral-100 text-sm">{{ $enc->patient->full_name }}</p>
                            <p class="text-xs text-neutral-500">{{ $enc->encounter_number }} · {{ $enc->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($enc->priority_level && $enc->priority_level !== 'normal')
                        <span class="badge badge-{{ $enc->priority_level }}">{{ ucfirst($enc->priority_level) }}</span>
                        @endif
                        <form method="POST" action="{{ route('lab.receive', $enc) }}">
                            @csrf
                            <button type="submit" class="btn-primary text-xs px-3 py-1.5">Receive Patient</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @if($queued->hasPages())
            <div class="px-6 py-3 border-t border-neutral-200 dark:border-neutral-700">{{ $queued->links() }}</div>
            @endif
            @endif
        </div>

        {{-- In Progress --}}
        <div x-show="tab === 'progress'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @if($inProgress->isEmpty())
            <div class="px-6 py-10 text-center text-sm text-neutral-400">
                No patients currently in progress. Receive a patient from the queue to begin.
            </div>
            @else
            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach($inProgress as $enc)
                <div class="flex items-center justify-between px-6 py-4 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 bg-neutral-200 dark:bg-neutral-700 rounded-full flex items-center justify-center text-neutral-700 dark:text-neutral-300 font-bold text-sm">
                            {{ strtoupper(substr($enc->patient->full_name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-neutral-900 dark:text-neutral-100 text-sm">{{ $enc->patient->full_name }}</p>
                            <p class="text-xs text-neutral-500">
                                {{ $enc->encounter_number }}
                                @if($enc->labRequest) · {{ $enc->labRequest->request_number }} @endif
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('lab.show', $enc) }}" class="btn-primary text-xs px-3 py-1.5">Record Results</a>
                </div>
                @endforeach
            </div>
            @if($inProgress->hasPages())
            <div class="px-6 py-3 border-t border-neutral-200 dark:border-neutral-700">{{ $inProgress->links() }}</div>
            @endif
            @endif
        </div>

    </div>

</div>
@endsection
