{{-- ============================================================
     QUICK ACTION BUTTON COMPONENT
     Usage: @include('components.quick-action-button', [
       'label' => 'Register Patient',
       'color' => 'blue',
       'icon'  => '<svg...>',
       'href'  => '#',
     ])
     ============================================================ --}}
@php
    $colorMap = [
        'blue'   => 'bg-blue-600 hover:bg-blue-700 text-white border-blue-600',
        'green'  => 'bg-emerald-600 hover:bg-emerald-700 text-white border-emerald-600',
        'yellow' => 'bg-amber-500 hover:bg-amber-600 text-white border-amber-500',
        'red'    => 'bg-red-600 hover:bg-red-700 text-white border-red-600',
        'purple' => 'bg-purple-600 hover:bg-purple-700 text-white border-purple-600',
        'indigo' => 'bg-indigo-600 hover:bg-indigo-700 text-white border-indigo-600',
        'gray'   => 'bg-gray-600 hover:bg-gray-700 text-white border-gray-600',
    ];
    $cls = $colorMap[$color ?? 'blue'];
@endphp
<a href="{{ $href ?? '#' }}"
   class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl border {{ $cls }} transition shadow-sm hover:shadow-md text-center">
    <span class="w-8 h-8 flex items-center justify-center">
        {!! $icon ?? '' !!}
    </span>
    <span class="text-xs font-semibold leading-tight">{{ $label ?? 'Action' }}</span>
</a>
