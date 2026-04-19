{{-- ============================================================
     STAT CARD COMPONENT
     Usage: @include('components.stat-card', [
       'title'   => 'Total Patients',
       'value'   => '1,284',
       'meta'    => '+12 today',
       'color'   => 'blue',   // blue | green | yellow | red | purple | indigo
       'icon'    => '<svg...>',
     ])
     ============================================================ --}}
@php
    $colors = [
        'blue'   => ['bg' => 'bg-blue-50 dark:bg-blue-900/20',   'icon' => 'bg-blue-100 dark:bg-blue-800/40',   'iconText' => 'text-blue-600 dark:text-blue-400',   'border' => 'border-blue-100 dark:border-blue-800/40'],
        'green'  => ['bg' => 'bg-green-50 dark:bg-green-900/20', 'icon' => 'bg-green-100 dark:bg-green-800/40', 'iconText' => 'text-green-600 dark:text-green-400', 'border' => 'border-green-100 dark:border-green-800/40'],
        'yellow' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'icon' => 'bg-amber-100 dark:bg-amber-800/40', 'iconText' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-100 dark:border-amber-800/40'],
        'red'    => ['bg' => 'bg-red-50 dark:bg-red-900/20',     'icon' => 'bg-red-100 dark:bg-red-800/40',     'iconText' => 'text-red-600 dark:text-red-400',     'border' => 'border-red-100 dark:border-red-800/40'],
        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/20','icon'=>'bg-purple-100 dark:bg-purple-800/40','iconText' => 'text-purple-600 dark:text-purple-400','border'=> 'border-purple-100 dark:border-purple-800/40'],
        'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20','icon'=>'bg-indigo-100 dark:bg-indigo-800/40','iconText'=> 'text-indigo-600 dark:text-indigo-400','border'=> 'border-indigo-100 dark:border-indigo-800/40'],
    ];
    $c = $colors[$color ?? 'blue'];
@endphp

<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md transition">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $title ?? 'Stat' }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $value ?? '0' }}</p>
            @if(isset($meta))
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $meta }}</p>
            @endif
        </div>
        <div class="ml-4 flex-shrink-0 w-11 h-11 rounded-lg {{ $c['icon'] }} flex items-center justify-center">
            <span class="{{ $c['iconText'] }}">
                {!! $icon ?? '' !!}
            </span>
        </div>
    </div>
</div>
