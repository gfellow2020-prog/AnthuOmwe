@extends('layouts.dashboard')

@section('title', 'Dashboard — Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Dashboard</span>
@endsection

@section('content')

@php
$statIcons = [
    'patients' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    'households' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
    'shift' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
    'metrics' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 2 2 5-5m2 9a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2v10z"/></svg>',
];
$stageConfig = [
    'registration'    => ['label' => 'Registration'],
    'triage'          => ['label' => 'Triage'],
    'screening'       => ['label' => 'Screening'],
    'lab'             => ['label' => 'Lab'],
    'screening_review'=> ['label' => 'Screening Review'],
    'pharmacy'        => ['label' => 'Pharmacy'],
    'completed'       => ['label' => 'Completed'],
];
$stageBadge = 'bg-neutral-200 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200';
@endphp

{{-- Per-stage queue counts - TOP --}}
<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Encounters by Stage</h3>
        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-0.5">Current queue depth per clinic stage</p>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 divide-x divide-y sm:divide-y-0 divide-neutral-200 dark:divide-neutral-700">
        @foreach($stageConfig as $stageKey => $cfg)
        @php $count = $encounterStageCounts[$stageKey] ?? 0; @endphp
        <div class="px-5 py-4 text-center">
            <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $stageBadge }} mb-2">
                {{ $cfg['label'] }}
            </span>
            <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 counter-value" data-target="{{ $count }}">0</p>
        </div>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @include('components.stat-card', ['title'=>'Total Patients',       'value'=>number_format($totalPatients),      'meta'=>'Seeded from local DB', 'color'=>'blue',   'icon'=>$statIcons['patients']])
    @include('components.stat-card', ['title'=>'Total Households',     'value'=>number_format($totalHouseholds),    'meta'=>'Linked household records', 'color'=>'indigo', 'icon'=>$statIcons['households']])
    @include('components.stat-card', ['title'=>'Active Patients',      'value'=>number_format($activePatients),     'meta'=>'Current patient records', 'color'=>'green',  'icon'=>$statIcons['patients']])
    @include('components.stat-card', ['title'=>"Today's Shift Volume", 'value'=>number_format($todayShiftPatients), 'meta'=>'Patients seen today', 'color'=>'yellow', 'icon'=>$statIcons['shift']])
</div>

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

{{-- Charts Section --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Encounters Over Time Chart --}}
    <div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden">
        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Encounters Over Time</h3>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-0.5">Daily encounter volume (last 7 days)</p>
        </div>
        <div class="p-5">
            <canvas id="encountersChart" height="200"></canvas>
        </div>
    </div>

    {{-- Encounters by Stage Bar Chart --}}
    <div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden">
        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Encounters by Stage</h3>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-0.5">Current distribution across stages</p>
        </div>
        <div class="p-5">
            <canvas id="stagesChart" height="200"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    <div class="xl:col-span-2 rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div>
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Recent Patients</h3>
                <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-0.5">Latest registered patients from database</p>
            </div>
            <a href="{{ route('patients.index') }}" class="text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-neutral-50 dark:bg-neutral-800 text-xs uppercase tracking-wide text-neutral-700 dark:text-neutral-300">
                        <th class="px-5 py-3 text-left font-semibold">Patient ID</th>
                        <th class="px-5 py-3 text-left font-semibold">Name</th>
                        <th class="px-5 py-3 text-left font-semibold">Gender</th>
                        <th class="px-5 py-3 text-left font-semibold">DOB</th>
                        <th class="px-5 py-3 text-left font-semibold">Household</th>
                        <th class="px-5 py-3 text-left font-semibold">Barcode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($recentPatients as $p)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition">
                        <td class="px-5 py-3 font-mono text-xs text-neutral-700 dark:text-neutral-300">{{ $p->patient_id }}</td>
                        <td class="px-5 py-3 font-medium text-neutral-900 dark:text-neutral-100">{{ $p->full_name }}</td>
                        <td class="px-5 py-3 text-neutral-700 dark:text-neutral-300">{{ ucfirst((string) $p->gender) }}</td>
                        <td class="px-5 py-3 text-neutral-700 dark:text-neutral-300">{{ $p->date_of_birth ? \Carbon\Carbon::parse($p->date_of_birth)->format('d M Y') : '—' }}</td>
                        <td class="px-5 py-3 text-neutral-700 dark:text-neutral-300">{{ $p->household_head_of_house ?: '—' }}</td>
                        <td class="px-5 py-3 text-neutral-700 dark:text-neutral-300 font-mono text-xs">{{ $p->barcode ?: $p->patient_id }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-neutral-600 dark:text-neutral-400">No patient records yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Recent Shift Reports</h3>
            <span class="text-xs text-neutral-600 dark:text-neutral-400">Live</span>
        </div>
        <div class="p-4 space-y-1">
            @forelse($recentShiftReports as $report)
            <div class="flex gap-3 py-2.5 border-b border-neutral-100 dark:border-neutral-800 last:border-0">
                <div class="flex flex-col items-center pt-1.5 flex-shrink-0">
                    <div class="w-2.5 h-2.5 rounded-full bg-neutral-900 dark:bg-white"></div>
                    <div class="w-px flex-1 bg-neutral-200 dark:bg-neutral-700 mt-1"></div>
                </div>
                <div class="flex-1 min-w-0 pb-1">
                    <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 leading-tight">{{ ucfirst((string) $report->shift_type) }} shift</p>
                    <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-0.5 truncate">{{ number_format((int) $report->total_patients_seen) }} patients seen{{ $report->reported_by ? ' • ' . $report->reported_by : '' }}</p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-500 mt-0.5">{{ $report->report_date ? \Carbon\Carbon::parse($report->report_date)->format('d M Y') : '—' }}</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-neutral-600 dark:text-neutral-400">No shift reports found.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="h-4"></div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- ENCOUNTER CYCLE SECTION                                                 --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}

@php
$statusBadge = [
    'started'     => 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300',
    'queued'      => 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300',
    'in_progress' => 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900',
    'completed'   => 'bg-neutral-200 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-300',
    'cancelled'   => 'bg-neutral-300 text-neutral-500 dark:bg-neutral-600 dark:text-neutral-400',
];
@endphp

{{-- Recent encounters table --}}
<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-6">
    <div class="flex items-center justify-between px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
        <div>
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Recent Encounters</h3>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-0.5">Latest 10 encounters across all stages</p>
        </div>
        <a href="{{ route('registration.index') }}" class="text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:underline">Go to Registration →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-neutral-50 dark:bg-neutral-800 text-xs uppercase tracking-wide text-neutral-700 dark:text-neutral-300">
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
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse($recentEncounters as $enc)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition">
                    <td class="px-5 py-3 font-mono text-xs text-neutral-700 dark:text-neutral-300">{{ $enc->encounter_number }}</td>
                    <td class="px-5 py-3 font-medium text-neutral-900 dark:text-neutral-100">{{ $enc->full_name }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-neutral-700 dark:text-neutral-300">{{ $enc->patient_code }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $stageBadge }}">
                            {{ $stageConfig[$enc->current_stage]['label'] ?? ucfirst($enc->current_stage) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusBadge[$enc->current_status] ?? 'bg-neutral-100 text-neutral-700' }}">
                            {{ ucwords(str_replace('_', ' ', $enc->current_status)) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-neutral-700 dark:text-neutral-300 capitalize">{{ $enc->visit_type ?: '—' }}</td>
                    <td class="px-5 py-3">
                        @if($enc->priority_level === 'urgent')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-900 text-white dark:bg-white dark:text-neutral-900">Urgent</span>
                        @elseif($enc->priority_level === 'high')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-700 text-white dark:bg-neutral-300 dark:text-neutral-900">High</span>
                        @else
                            <span class="text-neutral-600 dark:text-neutral-400 text-xs">{{ ucfirst($enc->priority_level ?: 'normal') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-neutral-700 dark:text-neutral-300">
                        {{ $enc->started_at ? \Carbon\Carbon::parse($enc->started_at)->format('d M Y H:i') : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center text-sm text-neutral-600 dark:text-neutral-400">
                        No encounters yet. <a href="{{ route('registration.index') }}" class="text-neutral-800 dark:text-neutral-200 hover:underline">Start one at Registration →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="h-4"></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== COUNTER ANIMATION =====
    const counters = document.querySelectorAll('.counter-value');
    const duration = 1500;

    const animateCounter = (el) => {
        const target = parseInt(el.getAttribute('data-target')) || 0;
        const start = 0;
        const startTime = performance.now();

        const easeOutQuart = (t) => 1 - Math.pow(1 - t, 4);

        const update = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easedProgress = easeOutQuart(progress);
            const current = Math.floor(start + (target - start) * easedProgress);
            el.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                el.textContent = target.toLocaleString();
            }
        };

        requestAnimationFrame(update);
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    counters.forEach(counter => observer.observe(counter));

    // ===== CHART.JS CONFIGURATION =====
    const isDark = document.documentElement.classList.contains('dark');
    const chartTheme = isDark
        ? {
            textColor: '#e5e7eb',
            gridColor: 'rgba(148, 163, 184, 0.28)',
            lineStroke: '#22d3ee',
            lineFillStart: 'rgba(34, 211, 238, 0.30)',
            lineFillEnd: 'rgba(34, 211, 238, 0.02)',
            pointColor: '#67e8f9',
            pointBorder: '#082f49',
            barColors: ['#fb7185', '#f59e0b', '#22d3ee', '#60a5fa', '#a78bfa', '#34d399', '#facc15'],
            barHoverColors: ['#f43f5e', '#d97706', '#06b6d4', '#3b82f6', '#8b5cf6', '#10b981', '#eab308'],
            tooltipBg: '#0b1220',
            tooltipBorder: 'rgba(34, 211, 238, 0.45)',
            tooltipTitle: '#f8fafc',
            tooltipBody: '#e2e8f0',
        }
        : {
            textColor: '#3f3f46',
            gridColor: 'rgba(113, 113, 122, 0.20)',
            lineStroke: '#0f766e',
            lineFillStart: 'rgba(15, 118, 110, 0.22)',
            lineFillEnd: 'rgba(15, 118, 110, 0.03)',
            pointColor: '#14b8a6',
            pointBorder: '#ffffff',
            barColors: ['#ef4444', '#f59e0b', '#14b8a6', '#2563eb', '#8b5cf6', '#10b981', '#ca8a04'],
            barHoverColors: ['#dc2626', '#d97706', '#0d9488', '#1d4ed8', '#7c3aed', '#059669', '#a16207'],
            tooltipBg: '#111827',
            tooltipBorder: 'rgba(17, 24, 39, 0.3)',
            tooltipTitle: '#ffffff',
            tooltipBody: '#f3f4f6',
        };

    Chart.defaults.color = chartTheme.textColor;
    Chart.defaults.borderColor = chartTheme.gridColor;
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';

    const last7Days = @json($encounterTrendLabels ?? []);
    const encounterData = @json($encounterTrendValues ?? []);

    // ===== ENCOUNTERS LINE CHART =====
    const encountersCtx = document.getElementById('encountersChart');
    if (encountersCtx) {
        new Chart(encountersCtx, {
            type: 'line',
            data: {
                labels: last7Days,
                datasets: [{
                    label: 'Encounters',
                    data: encounterData,
                    borderColor: chartTheme.lineStroke,
                    backgroundColor: (context) => {
                        const chart = context.chart;
                        const { ctx, chartArea } = chart;
                        if (!chartArea) {
                            return chartTheme.lineFillStart;
                        }

                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, chartTheme.lineFillStart);
                        gradient.addColorStop(1, chartTheme.lineFillEnd);
                        return gradient;
                    },
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: chartTheme.pointColor,
                    pointBorderColor: chartTheme.pointBorder,
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: chartTheme.tooltipBg,
                        borderColor: chartTheme.tooltipBorder,
                        borderWidth: 1,
                        titleColor: chartTheme.tooltipTitle,
                        bodyColor: chartTheme.tooltipBody,
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            color: chartTheme.textColor
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: chartTheme.gridColor },
                        ticks: {
                            font: { size: 11 },
                            color: chartTheme.textColor,
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // ===== STAGES BAR CHART =====
    const stagesCtx = document.getElementById('stagesChart');
    if (stagesCtx) {
        const stageLabels = ['Registration', 'Triage', 'Screening', 'Lab', 'Review', 'Pharmacy', 'Completed'];
        const stageValues = [
            {{ $encounterStageCounts['registration'] ?? 0 }},
            {{ $encounterStageCounts['triage'] ?? 0 }},
            {{ $encounterStageCounts['screening'] ?? 0 }},
            {{ $encounterStageCounts['lab'] ?? 0 }},
            {{ $encounterStageCounts['screening_review'] ?? 0 }},
            {{ $encounterStageCounts['pharmacy'] ?? 0 }},
            {{ $encounterStageCounts['completed'] ?? 0 }}
        ];

        new Chart(stagesCtx, {
            type: 'bar',
            data: {
                labels: stageLabels,
                datasets: [{
                    label: 'Encounters',
                    data: stageValues,
                    backgroundColor: chartTheme.barColors,
                    borderRadius: 4,
                    borderSkipped: false,
                    hoverBackgroundColor: chartTheme.barHoverColors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart',
                    delay: (context) => context.dataIndex * 100
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: chartTheme.tooltipBg,
                        borderColor: chartTheme.tooltipBorder,
                        borderWidth: 1,
                        titleColor: chartTheme.tooltipTitle,
                        bodyColor: chartTheme.tooltipBody,
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10 },
                            color: chartTheme.textColor,
                            maxRotation: 0,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: chartTheme.gridColor },
                        ticks: {
                            font: { size: 11 },
                            color: chartTheme.textColor,
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
