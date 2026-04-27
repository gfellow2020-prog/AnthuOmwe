<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarEventRequest;
use App\Http\Requests\UpdateCalendarEventRequest;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * GET /calendar
     */
    public function index(Request $request): View
    {
        $year  = (int) $request->query('year',  now()->year);
        $month = (int) $request->query('month', now()->month);

        // Clamp values to valid ranges
        $year  = max(2000, min(2100, $year));
        $month = max(1,    min(12,   $month));

        $currentMonth = Carbon::create($year, $month, 1);
        $prevMonth    = $currentMonth->copy()->subMonth();
        $nextMonth    = $currentMonth->copy()->addMonth();

        // Fetch all events for this calendar month
        $events = CalendarEvent::forMonth($year, $month)
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($e) => $e->event_date->format('Y-m-d'));

        // Upcoming events (next 7 days + rest of month)
        $upcoming = CalendarEvent::upcoming(8)->get();

        return view('calendar.index', compact(
            'currentMonth', 'prevMonth', 'nextMonth',
            'events', 'upcoming'
        ));
    }

    /**
     * GET /calendar/events (JSON — used by AJAX for event details)
     */
    public function events(Request $request): JsonResponse
    {
        $request->validate([
            'year'  => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1',    'max:12'],
        ]);

        $year  = (int) $request->query('year',  now()->year);
        $month = (int) $request->query('month', now()->month);

        $events = CalendarEvent::forMonth($year, $month)
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get()
            ->map(fn ($e) => $this->formatEvent($e));

        return response()->json(['events' => $events]);
    }

    /**
     * GET /calendar/events/{event} (JSON)
     */
    public function show(CalendarEvent $event): JsonResponse
    {
        return response()->json($this->formatEvent($event));
    }

    /**
     * POST /calendar/events
     */
    public function store(StoreCalendarEventRequest $request): RedirectResponse
    {
        CalendarEvent::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('calendar.index', [
                'year'  => Carbon::parse($request->validated('event_date'))->year,
                'month' => Carbon::parse($request->validated('event_date'))->month,
            ])
            ->with('success', 'Event created successfully.');
    }

    /**
     * PUT /calendar/events/{event}
     */
    public function update(UpdateCalendarEventRequest $request, CalendarEvent $event): RedirectResponse
    {
        abort_unless($event->created_by === $request->user()->id, 403, 'You can only edit your own events.');

        $event->update($request->validated());

        return redirect()
            ->route('calendar.index', [
                'year'  => $event->event_date->year,
                'month' => $event->event_date->month,
            ])
            ->with('success', 'Event updated.');
    }

    /**
     * DELETE /calendar/events/{event}
     */
    public function destroy(Request $request, CalendarEvent $event): JsonResponse|RedirectResponse
    {
        abort_unless($event->created_by === $request->user()->id, 403, 'You can only delete your own events.');

        $event->delete();

        if ($request->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()
            ->route('calendar.index')
            ->with('success', 'Event deleted.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function formatEvent(CalendarEvent $event): array
    {
        return [
            'id'          => $event->id,
            'title'       => $event->title,
            'description' => $event->description,
            'event_date'  => $event->event_date->format('Y-m-d'),
            'start_time'  => $event->start_time,
            'end_time'    => $event->end_time,
            'event_type'  => $event->event_type,
            'location'    => $event->location,
            'created_by'  => $event->created_by,
        ];
    }
}
