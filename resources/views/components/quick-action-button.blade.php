{{-- ============================================================
     QUICK ACTION BUTTON COMPONENT
     Usage: @include('components.quick-action-button', [
       'label' => 'Register Patient',
       'icon'  => '<svg...>',
       'href'  => '#',
     ])
     ============================================================ --}}
<a href="{{ $href ?? '#' }}"
   class="flex flex-col items-center justify-center gap-2 p-4 rounded border border-neutral-200 dark:border-neutral-700 bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 hover:bg-neutral-700 dark:hover:bg-neutral-100 transition text-center">
    <span class="w-8 h-8 flex items-center justify-center">
        {!! $icon ?? '' !!}
    </span>
    <span class="text-xs font-semibold leading-tight">{{ $label ?? 'Action' }}</span>
</a>
