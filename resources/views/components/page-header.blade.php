{{-- ============================================================
     PAGE HEADER COMPONENT
     Usage: @include('components.page-header', ['title' => '...', 'subtitle' => '...'])
     ============================================================ --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $title ?? 'Page Title' }}</h1>
        @if(isset($subtitle))
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex items-center gap-2">
            {!! $actions !!}
        </div>
    @endif
</div>
