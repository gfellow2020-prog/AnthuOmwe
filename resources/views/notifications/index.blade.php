@extends('layouts.dashboard')

@section('title', 'Notifications — Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Notifications</span>
@endsection

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-neutral-900 dark:text-white">Notifications</h1>
            <p class="text-sm text-neutral-500 mt-0.5">
                {{ $notifications->total() }} total ·
                {{ auth()->user()->unreadNotifications()->count() }} unread
            </p>
        </div>
        @if(auth()->user()->unreadNotifications()->count() > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn-secondary text-xs px-3 py-2">
                Mark all as read
            </button>
        </form>
        @endif
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="px-4 py-3 bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 rounded text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Notification list --}}
    <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700 overflow-hidden">
        @forelse($notifications as $notification)
        @php
            $data    = $notification->data;
            $isUnread = is_null($notification->read_at);
            $typeIcon = match($data['type'] ?? 'info') {
                'success' => 'M5 13l4 4L19 7',
                'warning' => 'M12 9v2m0 4h.01M10.29 3.86l-8.08 14A1 1 0 003.08 20h17.84a1 1 0 00.87-1.5l-8.08-14a1 1 0 00-1.74 0z',
                'error'   => 'M6 18L18 6M6 6l12 12',
                default   => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            };
        @endphp
        <div class="flex gap-4 px-5 py-4 {{ $isUnread ? 'bg-neutral-50 dark:bg-neutral-800/60' : '' }} border-b border-neutral-100 dark:border-neutral-800 last:border-b-0 group">
            {{-- Icon --}}
            <div class="flex-shrink-0 mt-0.5">
                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $isUnread ? 'bg-neutral-900 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeIcon }}"/>
                    </svg>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-neutral-900 dark:text-white {{ $isUnread ? '' : 'font-medium text-neutral-700 dark:text-neutral-300' }}">
                    {{ $data['title'] ?? 'Notification' }}
                    @if($isUnread)
                    <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-neutral-900 text-white">NEW</span>
                    @endif
                </p>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-0.5">{{ $data['message'] ?? '' }}</p>
                @if(!empty($data['link']))
                <a href="{{ $data['link'] }}" class="text-xs text-neutral-700 dark:text-neutral-400 underline mt-1 inline-block">View details →</a>
                @endif
                <p class="text-[11px] text-neutral-400 mt-1.5">{{ $notification->created_at->diffForHumans() }}</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                @if($isUnread)
                <button type="button"
                        onclick="markNotificationRead('{{ $notification->id }}', this)"
                        class="text-[11px] text-neutral-500 hover:text-neutral-800 underline underline-offset-2">
                    Mark read
                </button>
                @endif
                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-[11px] text-neutral-400 hover:text-red-700 underline underline-offset-2"
                            onclick="return confirm('Dismiss this notification?')">
                        Dismiss
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-16 text-center">
            <svg class="w-10 h-10 text-neutral-300 dark:text-neutral-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-sm text-neutral-500">No notifications yet.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div class="flex justify-center">{{ $notifications->links() }}</div>
    @endif

</div>
@endsection

@push('scripts')
<script>
async function markNotificationRead(id, btn) {
    try {
        await fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': @js(csrf_token()),
                'Accept':       'application/json',
            },
        });
        // Remove the "NEW" badge and "Mark read" button, update row style
        const row = btn.closest('.flex');
        row.classList.remove('bg-neutral-50', 'dark:bg-neutral-800\/60');
        const badge = row.querySelector('.bg-neutral-900.text-white.text-\\[10px\\]');
        if (badge) badge.remove();
        btn.remove();
    } catch (e) {
        alert('Could not mark as read.');
    }
}
</script>
@endpush
