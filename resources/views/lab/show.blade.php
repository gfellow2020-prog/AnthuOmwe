@extends('layouts.dashboard')

@section('title', 'Lab — ' . $encounter->encounter_number)

@push('styles')
<style>
    .field-label  { display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px; }
    .field-input  { width:100%;padding:9px 13px;font-size:14px;border:1px solid #d1d5db;border-radius:8px;
                    background:#fff;color:#111827;outline:none;transition:border-color .15s; }
    .field-input:focus  { border-color:#525252;box-shadow:0 0 0 3px rgba(82,82,82,.12); }
    textarea.field-input { resize:vertical;min-height:64px; }
    select.field-input  { appearance:none;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-repeat:no-repeat;background-position:right 10px center;background-size:20px; }
    .btn-primary  { display:inline-flex;align-items:center;gap:6px;padding:9px 22px;font-size:14px;font-weight:600;background:#171717;color:#fff;border-radius:8px;border:none;cursor:pointer; }
    .btn-primary:hover  { background:#262626; }
    .btn-blue     { display:inline-flex;align-items:center;gap:6px;padding:9px 18px;font-size:14px;font-weight:600;background:#2563eb;color:#fff;border-radius:8px;border:none;cursor:pointer; }
    .btn-blue:hover     { background:#1d4ed8; }
    .btn-green    { display:inline-flex;align-items:center;gap:6px;padding:9px 22px;font-size:14px;font-weight:600;background:#404040;color:#fff;border-radius:8px;border:none;cursor:pointer; }
    .btn-green:hover    { background:#525252; }
    .btn-secondary{ display:inline-flex;align-items:center;gap:6px;padding:9px 20px;font-size:14px;font-weight:600;background:#f5f5f5;color:#374151;border-radius:8px;border:1px solid #d4d4d4;cursor:pointer; }
    .section-title{ font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px; }
    .detail-row   { display:flex;gap:12px;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:13px; }
    .detail-row:last-child { border-bottom:none; }
    .detail-label { flex-shrink:0;width:150px;font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase; }
    .result-row   { background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px; }
    /* Modal */
    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:flex;align-items:flex-start;justify-content:center;overflow-y:auto;padding:40px 16px; }
    .modal-box      { background:#fff;border-radius:16px;width:100%;max-width:780px;box-shadow:0 20px 60px rgba(0,0,0,.18);position:relative; }
</style>
@endpush

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('lab.queue') }}" class="hover:text-neutral-700 transition">Lab</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">{{ $encounter->encounter_number }}</span>
@endsection

@section('content')

@php $lr = $encounter->labRequest; @endphp

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-neutral-100 border border-neutral-300 text-neutral-800 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-neutral-900 border border-neutral-700 text-white rounded-lg text-sm">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-neutral-900 border border-neutral-700 text-white rounded-lg text-sm">
    <ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════
     Page layout: investigation table (left/main) + sidebar (right)
══════════════════════════════════════════════════════════════════════════ --}}
<div x-data="labPage()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Main: Investigation table ──────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Header row --}}
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Investigation</h1>
            <button @click="openModal()" class="btn-blue">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Investigation
            </button>
        </div>

        {{-- Filter bar --}}
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 px-5 py-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="field-label">Order Date</label>
                    <input type="text" x-model="filters.date" class="field-input text-sm" placeholder="dd-mm-yyyy"/>
                </div>
                <div>
                    <label class="field-label">Priority</label>
                    <select x-model="filters.priority" class="field-input text-sm">
                        <option value="">All</option>
                        <option value="urgent">Urgent</option>
                        <option value="routine">Routine</option>
                        <option value="stat">Stat</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Order Number</label>
                    <input type="text" x-model="filters.orderNumber" class="field-input text-sm" placeholder="Order Number"/>
                </div>
                <div>
                    <label class="field-label">Test name</label>
                    <div class="relative">
                        <input type="text" x-model="filters.testName" class="field-input text-sm pr-9" placeholder="Test name"/>
                        <svg class="w-4 h-4 text-neutral-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-neutral-50 dark:bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wide">Order Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wide">Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wide">Facility</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wide">Clinician</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wide">Test Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wide">Order Number</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wide">Test Result</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wide">Test Unit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                        @if($lr && $lr->items->isNotEmpty())
                            @foreach($lr->items as $item)
                            @php
                                $result = $lr->results->firstWhere('lab_request_item_id', $item->id);
                            @endphp
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition cursor-pointer" @click="openModal()">
                                <td class="px-4 py-3 text-neutral-700 dark:text-neutral-300 whitespace-nowrap">
                                    {{ $lr->requested_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                        {{ ($lr->priority_level ?? 'routine') === 'urgent' ? 'bg-red-100 text-red-700' :
                                           (($lr->priority_level ?? 'routine') === 'stat'   ? 'bg-orange-100 text-orange-700' : 'bg-neutral-100 text-neutral-600') }}">
                                        {{ ucfirst($lr->priority_level ?? 'Routine') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-neutral-600 dark:text-neutral-400">—</td>
                                <td class="px-4 py-3 text-neutral-600 dark:text-neutral-400">—</td>
                                <td class="px-4 py-3 font-medium text-neutral-800 dark:text-neutral-200">{{ $item->test_name }}</td>
                                <td class="px-4 py-3 font-mono text-neutral-600 dark:text-neutral-400 text-xs">{{ $lr->request_number }}</td>
                                <td class="px-4 py-3 text-neutral-700 dark:text-neutral-300">
                                    {{ $result?->result_value ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400 text-xs">
                                    {{ $item->specimen_type ?? '—' }}
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-sm text-neutral-500">
                                    No investigation orders found for this encounter.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Pagination bar --}}
            <div class="px-5 py-3 border-t border-neutral-100 dark:border-neutral-700 flex items-center justify-end gap-2 text-sm text-neutral-600">
                <span>Show</span>
                <select class="border border-neutral-300 rounded px-2 py-1 text-sm">
                    <option>5</option><option>10</option><option>25</option>
                </select>
                <button class="px-2 py-1 rounded hover:bg-neutral-100 disabled:opacity-40">«</button>
                <button class="px-2 py-1 rounded hover:bg-neutral-100 disabled:opacity-40">‹</button>
                <span class="px-3 py-1 bg-blue-600 text-white rounded text-xs font-bold">1</span>
                <button class="px-2 py-1 rounded hover:bg-neutral-100">›</button>
                <button class="px-2 py-1 rounded hover:bg-neutral-100">»</button>
            </div>
        </div>
    </div>

    {{-- ── Right sidebar ───────────────────────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Patient card --}}
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200">
            <div class="px-6 py-4 border-b border-neutral-100"><h2 class="text-sm font-semibold text-neutral-700">Patient</h2></div>
            <div class="px-6 py-4 text-sm space-y-1">
                <p class="font-semibold text-neutral-900">{{ $encounter->patient->full_name }}</p>
                <p class="text-neutral-500">{{ $encounter->patient->patient_id }}</p>
                <p class="text-neutral-500">{{ ucfirst($encounter->patient->gender ?? '—') }}</p>
                @if($encounter->patient->allergies)
                <p class="text-neutral-600 font-medium mt-2">⚠ {{ $encounter->patient->allergies }}</p>
                @endif
            </div>
        </div>

        {{-- Screening summary --}}
        @if($encounter->screeningRecord)
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200">
            <div class="px-6 py-4 border-b border-neutral-100"><h2 class="text-sm font-semibold text-neutral-700">Screening Summary</h2></div>
            <div class="px-6 py-4 text-sm space-y-2">
                @if($encounter->screeningRecord->complaints)
                <div><span class="font-semibold text-neutral-600">Complaints: </span>{{ $encounter->screeningRecord->complaints }}</div>
                @endif
                @if($encounter->screeningRecord->provisional_diagnosis)
                <div><span class="font-semibold text-neutral-600">Provisional Dx: </span>{{ $encounter->screeningRecord->provisional_diagnosis }}</div>
                @endif
                @if($encounter->screeningRecord->plan)
                <div><span class="font-semibold text-neutral-600">Plan: </span>{{ $encounter->screeningRecord->plan }}</div>
                @endif
            </div>
        </div>
        @endif

        {{-- Lab request info --}}
        @if($encounter->labRequest)
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200">
            <div class="px-6 py-4 border-b border-neutral-100"><h2 class="text-sm font-semibold text-neutral-700">Lab Request</h2></div>
            <div class="px-6 py-4 text-sm space-y-2">
                <div class="detail-row"><span class="detail-label">Number</span><span class="font-mono font-semibold text-neutral-700">{{ $encounter->labRequest->request_number }}</span></div>
                <div class="detail-row"><span class="detail-label">Priority</span><span>{{ ucfirst($encounter->labRequest->priority_level ?? 'Normal') }}</span></div>
                <div class="detail-row"><span class="detail-label">Status</span><span>{{ ucfirst($encounter->labRequest->status) }}</span></div>
                <div class="detail-row"><span class="detail-label">Received</span><span>{{ $encounter->labRequest->requested_at->format('H:i') }}</span></div>
            </div>
        </div>
        @endif

        {{-- Activity --}}
        @if($encounter->audits->isNotEmpty())
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200">
            <div class="px-6 py-4 border-b border-neutral-100"><h2 class="text-sm font-semibold text-neutral-700">Activity</h2></div>
            <div class="divide-y divide-neutral-50">
                @foreach($encounter->audits->sortByDesc('action_at') as $audit)
                <div class="px-6 py-3">
                    <p class="text-xs font-semibold text-neutral-700">{{ str_replace('_', ' ', ucfirst($audit->action_name)) }}</p>
                    <p class="text-xs text-neutral-400">{{ $audit->action_at->format('d M H:i') }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         MODAL — Sample Collection + Record Results
    ══════════════════════════════════════════════════════════════════════ --}}
    <template x-teleport="body">
        <div x-show="modalOpen" x-cloak class="modal-backdrop"
             @keydown.escape.window="closeModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="modal-box" @click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4">

                {{-- Modal header --}}
                <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-neutral-900">Record Investigation Results</h2>
                        <p class="text-xs text-neutral-500 mt-0.5">Patient: <span class="font-semibold">{{ $encounter->patient->full_name }}</span></p>
                    </div>
                    <button @click="closeModal()" class="text-neutral-400 hover:text-neutral-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto" style="max-height:calc(90vh - 120px)">

                    {{-- ── Section 1: Sample Collection ──────────────────────── --}}
                    <div x-data="sampleForm()" class="px-6 pt-5 pb-4 border-b border-neutral-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 bg-neutral-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.155-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-neutral-900">Sample Collection</h3>
                            @if($lr && $lr->samples->isNotEmpty())
                            <span class="ml-auto text-xs bg-neutral-100 text-neutral-700 font-semibold px-2 py-0.5 rounded-full">{{ $lr->samples->count() }} collected</span>
                            @endif
                        </div>

                        {{-- Existing samples --}}
                        @if($lr && $lr->samples->isNotEmpty())
                        <div class="mb-3 space-y-2">
                            @foreach($lr->samples as $s)
                            <div class="flex items-center gap-3 px-3 py-2 bg-neutral-50 border border-neutral-100 rounded-lg text-sm">
                                <span class="font-semibold text-neutral-700">{{ $s->sample_type }}</span>
                                @if($s->sample_label)<span class="text-neutral-500">· {{ $s->sample_label }}</span>@endif
                                <span class="text-neutral-400 ml-auto text-xs">{{ $s->collected_at->format('H:i') }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <form method="POST" action="{{ route('lab.samples', $encounter) }}" class="space-y-3">
                            @csrf
                            <template x-for="(sample, index) in samples" :key="index">
                                <div class="result-row flex gap-3 items-start">
                                    <div class="flex-1">
                                        <label class="field-label">Sample Type</label>
                                        <input type="text" :name="`samples[${index}][sample_type]`" x-model="sample.type"
                                               class="field-input" placeholder="e.g. Blood, Urine, Stool"/>
                                    </div>
                                    <div class="flex-1">
                                        <label class="field-label">Label</label>
                                        <input type="text" :name="`samples[${index}][sample_label]`" x-model="sample.label"
                                               class="field-input" placeholder="optional"/>
                                    </div>
                                    <button type="button" @click="samples.splice(index,1)"
                                            class="mt-6 text-neutral-500 hover:text-neutral-600 text-lg leading-none">×</button>
                                </div>
                            </template>
                            <div class="flex gap-3">
                                <button type="button" @click="samples.push({type:'',label:''})"
                                        class="text-sm text-neutral-600 font-semibold hover:underline">+ Add Sample</button>
                                <button type="submit" class="btn-primary text-xs px-3 py-1.5 ml-auto">Save Samples</button>
                            </div>
                        </form>
                    </div>

                    {{-- ── Section 2: Record Results & Complete ──────────────── --}}
                    <div x-data="resultForm()" class="px-6 pt-5 pb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 bg-neutral-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-neutral-900">Record Results &amp; Complete</h3>
                        </div>

                        {{-- Existing results --}}
                        @if($lr && $lr->results->isNotEmpty())
                        <div class="mb-4 space-y-2">
                            @foreach($lr->results as $res)
                            <div class="result-row text-sm">
                                <div class="flex items-center gap-3 flex-wrap">
                                    @if($res->labRequestItem)<span class="font-semibold text-gray-800">{{ $res->labRequestItem->test_name }}</span>@endif
                                    @if($res->result_value)<span class="font-semibold text-neutral-700 text-base">{{ $res->result_value }}</span>@endif
                                    @if($res->reference_range)<span class="text-neutral-500">Ref: {{ $res->reference_range }}</span>@endif
                                    @if($res->interpretation)
                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                        {{ $res->interpretation === 'normal' ? 'bg-neutral-100 text-neutral-700' :
                                           ($res->interpretation === 'critical' ? 'bg-neutral-900 text-white' : 'bg-neutral-200 text-neutral-700') }}">
                                        {{ ucfirst($res->interpretation) }}
                                    </span>
                                    @endif
                                </div>
                                @if($res->result_text)<p class="text-neutral-600 mt-1">{{ $res->result_text }}</p>@endif
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <form method="POST" action="{{ route('lab.complete', $encounter) }}" class="space-y-3">
                            @csrf

                            <template x-for="(row, index) in results" :key="index">
                                <div class="result-row space-y-3">
                                    @if($lr && $lr->items->isNotEmpty())
                                    <div>
                                        <label class="field-label">Test</label>
                                        <select :name="`results[${index}][lab_request_item_id]`" x-model="row.item_id" class="field-input">
                                            <option value="">— General result —</option>
                                            @foreach($lr?->items ?? [] as $item)
                                            <option value="{{ $item->id }}">{{ $item->test_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @else
                                    <input type="hidden" :name="`results[${index}][lab_request_item_id]`" value="">
                                    @endif
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="field-label">Result Value</label>
                                            <input type="text" :name="`results[${index}][result_value]`" x-model="row.value"
                                                   class="field-input" placeholder="e.g. 12.5 g/dL"/>
                                        </div>
                                        <div>
                                            <label class="field-label">Reference Range</label>
                                            <input type="text" :name="`results[${index}][reference_range]`" x-model="row.range"
                                                   class="field-input" placeholder="e.g. 11.5–16.5"/>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="field-label">Interpretation</label>
                                            <select :name="`results[${index}][interpretation]`" x-model="row.interp" class="field-input">
                                                <option value="">— Select —</option>
                                                <option value="normal">Normal</option>
                                                <option value="abnormal">Abnormal</option>
                                                <option value="critical">Critical</option>
                                                <option value="inconclusive">Inconclusive</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Remarks</label>
                                            <input type="text" :name="`results[${index}][remarks]`" x-model="row.remarks"
                                                   class="field-input" placeholder="optional"/>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="field-label">Result Text / Notes</label>
                                        <textarea :name="`results[${index}][result_text]`" x-model="row.text"
                                                  rows="2" class="field-input" placeholder="Narrative or microscopy findings…"></textarea>
                                    </div>
                                    <button type="button" @click="results.splice(index,1)"
                                            class="text-xs text-neutral-500 hover:text-neutral-600">Remove this result</button>
                                </div>
                            </template>

                            <button type="button" @click="results.push({item_id:'',value:'',range:'',interp:'',remarks:'',text:''})"
                                    class="text-sm text-neutral-600 font-semibold hover:underline">+ Add Result Row</button>

                            <div class="pt-3 border-t border-neutral-100 space-y-3">
                                <div>
                                    <label class="field-label">Handover Note to Clinician <span class="font-normal text-neutral-400 text-xs">(optional)</span></label>
                                    <textarea name="notes" rows="2" class="field-input"
                                              placeholder="Anything the reviewing clinician should note…">{{ old('notes') }}</textarea>
                                </div>
                                <div class="flex items-center gap-3 flex-wrap">
                                    <button type="submit" class="btn-green">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                        </svg>
                                        Save Results &amp; Return to Screening Review
                                    </button>
                                    <button type="button" @click="closeModal()" class="btn-secondary">Back</button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>{{-- /scroll area --}}
            </div>{{-- /modal-box --}}
        </div>{{-- /modal-backdrop --}}
    </template>

</div>{{-- /grid --}}

@push('scripts')
<script>
function labPage() {
    return {
        modalOpen: {{ ($errors->any() || old('notes')) ? 'true' : 'false' }},
        filters: { date: '', priority: '', orderNumber: '', testName: '' },
        openModal()  { this.modalOpen = true;  document.body.style.overflow = 'hidden'; },
        closeModal() { this.modalOpen = false; document.body.style.overflow = ''; },
    };
}
function sampleForm() {
    return { samples: [{ type: '', label: '' }] };
}
function resultForm() {
    return { results: [{ item_id: '', value: '', range: '', interp: '', remarks: '', text: '' }] };
}
</script>
@endpush

@endsection
