@extends('layouts.dashboard')

@section('title', 'Calendar — Anthu Omwe Health Center')

@push('styles')
<style>
    .cal-day { min-height: 84px; }
    .cal-day-other { opacity: .35; }
    .event-pill { font-size: 11px; line-height: 1.3; padding: 2px 6px; border-radius: 3px;
                  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; }
    .event-appointment { background:#171717; color:#fff; }
    .event-meeting      { background:#404040; color:#fff; }
    .event-reminder     { background:#e5e5e5; color:#171717; }
    .event-other        { background:#d4d4d4; color:#171717; }
</style>
@endpush

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('calendar.index') }}" class="hover:text-neutral-700">Calendar</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">{{ $currentMonth->format('F Y') }}</span>
@endsection

@section('content')

@php
    $today      = \Carbon\Carbon::today();
    $startOfCal = $currentMonth->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $endOfCal   = $currentMonth->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SATURDAY);
    $eventTypes = \App\Models\CalendarEvent::$types;
@endphp

<div x-data="calendarPage()" class="space-y-4">

    {{-- Flash --}}
    @if(session('success'))
    <div class="px-4 py-3 bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 rounded text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-4">

        {{-- ═══ CALENDAR GRID ═══ --}}
        <div class="flex-1 min-w-0">
            <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700">

                {{-- Calendar header --}}
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('calendar.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
                           class="btn-secondary text-xs px-2.5 py-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <h2 class="text-base font-bold text-neutral-900 dark:text-white min-w-[140px] text-center">
                            {{ $currentMonth->format('F Y') }}
                        </h2>
                        <a href="{{ route('calendar.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
                           class="btn-secondary text-xs px-2.5 py-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        @if(!($currentMonth->isSameMonth($today)))
                        <a href="{{ route('calendar.index') }}" class="text-xs text-neutral-500 hover:text-neutral-800 underline underline-offset-2 ml-1">
                            Today
                        </a>
                        @endif
                    </div>
                    <button type="button" @click="openCreate()" class="btn-primary text-xs px-3 py-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Event
                    </button>
                </div>

                {{-- Day labels --}}
                <div class="grid grid-cols-7 border-b border-neutral-100 dark:border-neutral-800">
                    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                    <div class="py-2 text-center text-[11px] font-bold text-neutral-500 uppercase tracking-widest">{{ $d }}</div>
                    @endforeach
                </div>

                {{-- Grid cells --}}
                <div class="grid grid-cols-7">
                    @php $cursor = $startOfCal->copy(); @endphp
                    @while($cursor->lte($endOfCal))
                    @php
                        $key         = $cursor->format('Y-m-d');
                        $dayEvents   = $events->get($key, collect());
                        $isToday     = $cursor->isSameDay($today);
                        $isThisMonth = $cursor->month === $currentMonth->month;
                        $isWeekend   = $cursor->isWeekend();
                    @endphp
                    <div class="cal-day border-b border-r border-neutral-100 dark:border-neutral-800 p-1.5 {{ !$isThisMonth ? 'cal-day-other' : '' }} {{ $isWeekend ? 'bg-neutral-50 dark:bg-neutral-800/40' : '' }}"
                         @click="openCreate('{{ $key }}')"
                         style="cursor:pointer;">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold {{ $isToday ? 'w-6 h-6 flex items-center justify-center rounded-full bg-neutral-900 text-white' : 'text-neutral-600 dark:text-neutral-400' }}">
                                {{ $cursor->day }}
                            </span>
                        </div>
                        @foreach($dayEvents->take(3) as $ev)
                        <div class="event-pill event-{{ $ev->event_type }} mb-0.5"
                             @click.stop="openDetail(@js(['id'=>$ev->id,'title'=>$ev->title,'description'=>$ev->description,'event_date'=>$ev->event_date->format('Y-m-d'),'start_time'=>$ev->start_time,'end_time'=>$ev->end_time,'event_type'=>$ev->event_type,'location'=>$ev->location,'created_by'=>$ev->created_by]))"
                             title="{{ $ev->title }}">
                            {{ $ev->start_time ? \Carbon\Carbon::parse($ev->start_time)->format('H:i').' ' : '' }}{{ $ev->title }}
                        </div>
                        @endforeach
                        @if($dayEvents->count() > 3)
                        <span class="text-[10px] text-neutral-400">+{{ $dayEvents->count() - 3 }} more</span>
                        @endif
                    </div>
                    @php $cursor->addDay(); @endphp
                    @endwhile
                </div>
            </div>

            {{-- Legend --}}
            <div class="flex flex-wrap items-center gap-3 mt-2 px-1">
                @foreach($eventTypes as $key => $label)
                <span class="event-pill event-{{ $key }}">{{ $label }}</span>
                @endforeach
            </div>
        </div>

        {{-- ═══ UPCOMING SIDEBAR ═══ --}}
        <div class="w-full lg:w-72 flex-shrink-0 space-y-3">
            <div class="bg-white dark:bg-neutral-900 rounded border border-neutral-300 dark:border-neutral-700">
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Upcoming Events</h3>
                </div>
                <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    @forelse($upcoming as $ev)
                    <div class="px-4 py-3 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
                         @click="openDetail(@js(['id'=>$ev->id,'title'=>$ev->title,'description'=>$ev->description,'event_date'=>$ev->event_date->format('Y-m-d'),'start_time'=>$ev->start_time,'end_time'=>$ev->end_time,'event_type'=>$ev->event_type,'location'=>$ev->location,'created_by'=>$ev->created_by]))">
                        <div class="flex items-start gap-2">
                            <span class="event-pill event-{{ $ev->event_type }} mt-0.5 flex-shrink-0">{{ ucfirst($ev->event_type) }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white truncate">{{ $ev->title }}</p>
                                <p class="text-xs text-neutral-500 mt-0.5">
                                    {{ $ev->event_date->format('D, j M') }}
                                    @if($ev->start_time) · {{ \Carbon\Carbon::parse($ev->start_time)->format('H:i') }} @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-8 text-center text-sm text-neutral-500">No upcoming events.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ CREATE / EDIT MODAL ═══ --}}
    <div x-show="showForm"
         x-transition.opacity
         class="fixed inset-0 z-[9997] bg-black/50 flex items-center justify-center p-4"
         style="display:none;"
         @click.self="showForm = false"
         @keydown.escape.window="showForm = false">
        <div class="w-full max-w-lg rounded border border-neutral-200 bg-white shadow-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-neutral-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-neutral-900" x-text="editingId ? 'Edit Event' : 'New Event'"></h3>
                <button type="button" @click="showForm = false" class="text-neutral-500 hover:text-neutral-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form :action="editingId ? `/calendar/events/${editingId}` : `{{ route('calendar.events.store') }}`"
                  method="POST" class="p-5 space-y-3">
                @csrf
                <input type="hidden" name="_method" x-bind:value="editingId ? 'PUT' : 'POST'">

                <div>
                    <label class="field-label">Title <span class="req">*</span></label>
                    <input type="text" name="title" x-model="form.title" required maxlength="200"
                           class="field-input" placeholder="Event title" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Date <span class="req">*</span></label>
                        <input type="date" name="event_date" x-model="form.event_date" required class="field-input" />
                    </div>
                    <div>
                        <label class="field-label">Type <span class="req">*</span></label>
                        <select name="event_type" x-model="form.event_type" class="field-input">
                            @foreach($eventTypes as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Start Time</label>
                        <input type="time" name="start_time" x-model="form.start_time" class="field-input" />
                    </div>
                    <div>
                        <label class="field-label">End Time</label>
                        <input type="time" name="end_time" x-model="form.end_time" class="field-input" />
                    </div>
                </div>

                <div>
                    <label class="field-label">Location</label>
                    <input type="text" name="location" x-model="form.location" maxlength="200"
                           class="field-input" placeholder="Room, building, or URL" />
                </div>

                <div>
                    <label class="field-label">Description</label>
                    <textarea name="description" x-model="form.description" rows="2"
                              class="field-input" placeholder="Optional notes…"></textarea>
                </div>

                @if($errors->any())
                <div class="text-xs text-red-700 space-y-0.5">
                    @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="showForm = false" class="btn-secondary text-xs px-3 py-2">Cancel</button>
                    <button type="submit" class="btn-primary text-xs px-3 py-2" x-text="editingId ? 'Save Changes' : 'Create Event'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ DETAIL MODAL ═══ --}}
    <div x-show="showDetail"
         x-transition.opacity
         class="fixed inset-0 z-[9997] bg-black/50 flex items-center justify-center p-4"
         style="display:none;"
         @click.self="showDetail = false"
         @keydown.escape.window="showDetail = false">
        <div class="w-full max-w-md rounded border border-neutral-200 bg-white shadow-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-neutral-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-neutral-900 truncate" x-text="detail.title"></h3>
                <button type="button" @click="showDetail = false" class="text-neutral-500 hover:text-neutral-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="event-pill" :class="'event-' + detail.event_type" x-text="typeLabel(detail.event_type)"></span>
                    <span class="text-sm text-neutral-700" x-text="formatDate(detail.event_date)"></span>
                    <span x-show="detail.start_time" class="text-xs text-neutral-500"
                          x-text="detail.start_time ? '· ' + detail.start_time + (detail.end_time ? ' – ' + detail.end_time : '') : ''"></span>
                </div>
                <p x-show="detail.location" class="text-sm text-neutral-600">
                    <span class="font-medium">Location:</span> <span x-text="detail.location"></span>
                </p>
                <p x-show="detail.description" class="text-sm text-neutral-600 whitespace-pre-wrap" x-text="detail.description"></p>
            </div>
            <div class="px-5 py-3 border-t border-neutral-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <template x-if="detail.created_by === {{ auth()->id() }}">
                        <div class="flex gap-2">
                            <button type="button" @click="openEdit(detail)" class="btn-secondary text-xs px-3 py-1.5">Edit</button>
                            <button type="button" @click="deleteEvent(detail.id)" class="text-xs text-red-700 hover:underline">Delete</button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="showDetail = false" class="btn-secondary text-xs px-3 py-1.5">Close</button>
            </div>
        </div>
    </div>

    {{-- Hidden delete form --}}
    <form id="delete-event-form" method="POST" style="display:none;">
        @csrf @method('DELETE')
    </form>

</div>
@endsection

@push('scripts')
<script>
function calendarPage() {
    return {
        showForm:   @js($errors->any()),
        showDetail: false,
        editingId:  @js(old('_editing_id')),
        form: {
            title:       @js(old('title', '')),
            event_date:  @js(old('event_date', '')),
            start_time:  @js(old('start_time', '')),
            end_time:    @js(old('end_time', '')),
            event_type:  @js(old('event_type', 'reminder')),
            location:    @js(old('location', '')),
            description: @js(old('description', '')),
        },
        detail: {},

        openCreate(date = '') {
            this.editingId       = null;
            this.form.title      = '';
            this.form.event_date = date || '';
            this.form.start_time = '';
            this.form.end_time   = '';
            this.form.event_type = 'reminder';
            this.form.location   = '';
            this.form.description= '';
            this.showDetail      = false;
            this.showForm        = true;
        },

        openDetail(ev) {
            this.detail     = ev;
            this.showForm   = false;
            this.showDetail = true;
        },

        openEdit(ev) {
            this.editingId        = ev.id;
            this.form.title       = ev.title;
            this.form.event_date  = ev.event_date;
            this.form.start_time  = ev.start_time ?? '';
            this.form.end_time    = ev.end_time   ?? '';
            this.form.event_type  = ev.event_type;
            this.form.location    = ev.location   ?? '';
            this.form.description = ev.description ?? '';
            this.showDetail       = false;
            this.showForm         = true;
        },

        deleteEvent(id) {
            if (!confirm('Delete this event?')) return;
            const form = document.getElementById('delete-event-form');
            form.action = `/calendar/events/${id}`;
            form.submit();
        },

        typeLabel(type) {
            const labels = {
                appointment: 'Appointment',
                meeting:     'Meeting',
                reminder:    'Reminder',
                other:       'Other',
            };
            return labels[type] ?? type;
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('en-GB', { weekday:'short', day:'numeric', month:'long', year:'numeric' });
        },
    };
}
</script>
@endpush
