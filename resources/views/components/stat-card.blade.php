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

<div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5 hover:border-neutral-400 dark:hover:border-neutral-600 transition">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:text-neutral-400">{{ $title ?? 'Stat' }}</p>
            <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white counter-value" data-target="{{ str_replace(',', '', $value ?? '0') }}">0</p>
            @if(isset($meta))
                <p class="mt-1 text-xs text-neutral-600 dark:text-neutral-400">{{ $meta }}</p>
            @endif
        </div>
        <div class="ml-4 flex-shrink-0 w-11 h-11 rounded bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
            <span class="text-neutral-700 dark:text-neutral-300">
                {!! $icon ?? '' !!}
            </span>
        </div>
    </div>
</div>
