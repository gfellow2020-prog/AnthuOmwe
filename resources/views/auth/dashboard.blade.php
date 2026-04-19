@extends('layouts.dashboard')

@section('title', 'Dashboard — Anthu Omwe Health Center')

@section('page-header')
    @include('components.page-header', [
        'title'    => 'Anthu Omwe Health Center Dashboard',
        'subtitle' => 'Live overview from your local seeded database',
    ])
@endsection

@section('content')

@php
$statIcons = [
    'patients' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    'households' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
    'shift' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
    'metrics' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 2 2 5-5m2 9a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2v10z"/></svg>',
];
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @include('components.stat-card', ['title'=>'Total Patients',       'value'=>number_format($totalPatients),      'meta'=>'Seeded from local DB', 'color'=>'blue',   'icon'=>$statIcons['patients']])
    @include('components.stat-card', ['title'=>'Total Households',     'value'=>number_format($totalHouseholds),    'meta'=>'Linked household records', 'color'=>'indigo', 'icon'=>$statIcons['households']])
    @include('components.stat-card', ['title'=>'Active Patients',      'value'=>number_format($activePatients),     'meta'=>'Current patient records', 'color'=>'green',  'icon'=>$statIcons['patients']])
    @include('components.stat-card', ['title'=>"Today's Shift Volume", 'value'=>number_format($todayShiftPatients), 'meta'=>'Patients seen today', 'color'=>'yellow', 'icon'=>$statIcons['shift']])
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    <div class="xl:col-span-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Recent Patients</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Latest registered patients from database</p>
            </div>
            <a href="{{ route('patients.index') }}" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3 text-left font-semibold">Patient ID</th>
                        <th class="px-5 py-3 text-left font-semibold">Name</th>
                        <th class="px-5 py-3 text-left font-semibold">Gender</th>
                        <th class="px-5 py-3 text-left font-semibold">DOB</th>
                        <th class="px-5 py-3 text-left font-semibold">Household</th>
                        <th class="px-5 py-3 text-left font-semibold">Barcode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentPatients as $p)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                        <td class="px-5 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $p->patient_id }}</td>
                        <td class="px-5 py-3 font-medium text-gray-800 dark:text-gray-100">{{ $p->full_name }}</td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ ucfirst((string) $p->gender) }}</td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $p->date_of_birth ? \Carbon\Carbon::parse($p->date_of_birth)->format('d M Y') : '—' }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $p->household_head_of_house ?: '—' }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300 font-mono text-xs">{{ $p->barcode ?: $p->patient_id }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No patient records yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Recent Shift Reports</h3>
            <span class="text-xs text-gray-400">Live</span>
        </div>
        <div class="p-4 space-y-1">
            @forelse($recentShiftReports as $report)
            <div class="flex gap-3 py-2.5 border-b border-gray-50 dark:border-gray-700/60 last:border-0">
                <div class="flex flex-col items-center pt-1.5 flex-shrink-0">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                    <div class="w-px flex-1 bg-gray-100 dark:bg-gray-700 mt-1"></div>
                </div>
                <div class="flex-1 min-w-0 pb-1">
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100 leading-tight">{{ ucfirst((string) $report->shift_type) }} shift</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ number_format((int) $report->total_patients_seen) }} patients seen{{ $report->reported_by ? ' • ' . $report->reported_by : '' }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $report->report_date ? \Carbon\Carbon::parse($report->report_date)->format('d M Y') : '—' }}</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">No shift reports found.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    <div class="xl:col-span-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Impact Numbers</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Seeded from Excel Impact Numbers sheet</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800">{{ number_format($impactNumbers->count()) }} Metrics</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3 text-left font-semibold">Metric</th>
                        <th class="px-5 py-3 text-left font-semibold">Value</th>
                        <th class="px-5 py-3 text-left font-semibold">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($impactNumbers as $metric)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                        <td class="px-5 py-3 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $metric->metric }}</td>
                        <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $metric->value }}</td>
                        <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $metric->description ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No metrics found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Quick Actions</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Frequently used shortcuts</p>
        </div>
        <div class="p-4 grid grid-cols-2 gap-3">
            @php
            $quickActions = [
                ['Register Patient', 'green',  route('registration.index'), '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>'],
                ['Households',       'blue',   route('households.index'), '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'],
                ['Patients',         'indigo', route('patients.index'),   '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
                ['All Encounters',   'purple', route('encounters.index'), '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'],
                ['Triage Queue',     'yellow', route('triage.queue'),     '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
                ['Pharmacy Queue',   'green',  route('pharmacy.queue'),   '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.155-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>'],
            ];
            @endphp
            @foreach($quickActions as $act)
                @include('components.quick-action-button', ['label'=>$act[0],'color'=>$act[1],'href'=>$act[2],'icon'=>$act[3]])
            @endforeach
        </div>
    </div>

</div>

<div class="h-4"></div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- ENCOUNTER CYCLE SECTION                                                 --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}

@php
$stageConfig = [
    'registration'    => ['label' => 'Registration',     'color' => 'blue'],
    'triage'          => ['label' => 'Triage',           'color' => 'yellow'],
    'screening'       => ['label' => 'Screening',        'color' => 'purple'],
    'lab'             => ['label' => 'Lab',              'color' => 'indigo'],
    'screening_review'=> ['label' => 'Screening Review', 'color' => 'pink'],
    'pharmacy'        => ['label' => 'Pharmacy',         'color' => 'orange'],
    'completed'       => ['label' => 'Completed',        'color' => 'green'],
];
$statusBadge = [
    'started'     => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    'queued'      => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
    'in_progress' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
    'completed'   => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    'cancelled'   => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
];
$stageBadge = [
    'registration'     => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    'triage'           => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
    'screening'        => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
    'lab'              => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    'screening_review' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300',
    'pharmacy'         => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
    'completed'        => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
];
@endphp

{{-- Encounter summary stats --}}
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
    @include('components.stat-card', [
        'title' => 'Total Encounters',
        'value' => number_format($totalEncounters),
        'meta'  => 'All time',
        'color' => 'blue',
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',
    ])
    @include('components.stat-card', [
        'title' => 'Active Encounters',
        'value' => number_format($activeEncounters),
        'meta'  => 'Currently in-progress',
        'color' => 'yellow',
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    ])
    @include('components.stat-card', [
        'title' => 'Completed Encounters',
        'value' => number_format($completedEncounters),
        'meta'  => 'Fully closed',
        'color' => 'green',
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    ])
</div>

{{-- Per-stage queue counts --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Encounters by Stage</h3>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Current queue depth per clinic stage</p>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 divide-x divide-y sm:divide-y-0 divide-gray-100 dark:divide-gray-700">
        @foreach($stageConfig as $stageKey => $cfg)
        @php $count = $encounterStageCounts[$stageKey] ?? 0; @endphp
        <div class="px-5 py-4 text-center">
            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $stageBadge[$stageKey] ?? 'bg-gray-100 text-gray-600' }} mb-2">
                {{ $cfg['label'] }}
            </span>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($count) }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- Recent encounters table --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden mb-6">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Recent Encounters</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Latest 10 encounters across all stages</p>
        </div>
        <a href="{{ route('registration.index') }}" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Go to Registration →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-5 py-3 text-left font-semibold">Encounter #</th>
                    <th class="px-5 py-3 text-left font-semibold">Patient</th>
                    <th class="px-5 py-3 text-left font-semibold">Patient ID</th>
                    <th class="px-5 py-3 text-left font-semibold">Stage</th>
                    <th class="px-5 py-3 text-left font-semibold">Status</th>
                    <th class="px-5 py-3 text-left font-semibold">Visit Type</th>
                    <th class="px-5 py-3 text-left font-semibold">Priority</th>
                    <th class="px-5 py-3 text-left font-semibold">Started</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($recentEncounters as $enc)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-5 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $enc->encounter_number }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800 dark:text-gray-100">{{ $enc->full_name }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $enc->patient_code }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $stageBadge[$enc->current_stage] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $stageConfig[$enc->current_stage]['label'] ?? ucfirst($enc->current_stage) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[$enc->current_status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucwords(str_replace('_', ' ', $enc->current_status)) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-500 dark:text-gray-400 capitalize">{{ $enc->visit_type ?: '—' }}</td>
                    <td class="px-5 py-3">
                        @if($enc->priority_level === 'urgent')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">Urgent</span>
                        @elseif($enc->priority_level === 'high')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300">High</span>
                        @else
                            <span class="text-gray-400 text-xs">{{ ucfirst($enc->priority_level ?: 'normal') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                        {{ $enc->started_at ? \Carbon\Carbon::parse($enc->started_at)->format('d M Y H:i') : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        No encounters yet. <a href="{{ route('registration.index') }}" class="text-blue-600 hover:underline">Start one at Registration →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="h-4"></div>
@endsection
