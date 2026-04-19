{{-- ============================================================
     SECTION CARD COMPONENT
     Usage:
       @include('components.section-card', ['title' => 'Section Title', 'subtitle' => '...'])
         ... content ...
       @endinclude   — not applicable for blade includes; use @slot pattern via component
     Instead, use as a wrapper in your view with @component or just inline.
     This partial provides the wrapping shell.
     ============================================================ --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
    @if(isset($title))
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $title }}</h3>
            @if(isset($subtitle))
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($action))
            {!! $action !!}
        @endif
    </div>
    @endif
    <div class="{{ $bodyClass ?? 'p-5' }}">
        {{ $slot ?? '' }}
    </div>
</div>
