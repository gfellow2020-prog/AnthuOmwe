<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarEventTest extends TestCase
{
    use RefreshDatabase;

    // ─── helpers ─────────────────────────────────────────────────────────────

    private function actingUser(): User
    {
        return User::factory()->create();
    }

    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'title'       => 'Team Meeting',
            'description' => 'Weekly sync',
            'event_date'  => '2026-05-10',
            'start_time'  => '09:00',
            'end_time'    => '10:00',
            'event_type'  => 'meeting',
            'location'    => 'Conference Room A',
        ], $overrides);
    }

    // ─── calendar index ───────────────────────────────────────────────────────

    public function test_calendar_index_requires_auth(): void
    {
        $this->get(route('calendar.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_calendar(): void
    {
        $this->actingAs($this->actingUser())
             ->get(route('calendar.index'))
             ->assertOk()
             ->assertViewIs('calendar.index');
    }

    public function test_calendar_accepts_year_and_month_params(): void
    {
        $this->actingAs($this->actingUser())
             ->get(route('calendar.index', ['year' => 2026, 'month' => 3]))
             ->assertOk()
             ->assertSee('March 2026');
    }

    // ─── create event ─────────────────────────────────────────────────────────

    public function test_user_can_create_an_event(): void
    {
        $user = $this->actingUser();

        $this->actingAs($user)
             ->post(route('calendar.events.store'), $this->eventPayload())
             ->assertRedirect();

        $this->assertDatabaseHas('calendar_events', [
            'title'      => 'Team Meeting',
            'event_type' => 'meeting',
            'created_by' => $user->id,
        ]);
    }

    public function test_event_creation_requires_title(): void
    {
        $this->actingAs($this->actingUser())
             ->post(route('calendar.events.store'), $this->eventPayload(['title' => '']))
             ->assertSessionHasErrors('title');
    }

    public function test_event_creation_requires_date(): void
    {
        $this->actingAs($this->actingUser())
             ->post(route('calendar.events.store'), $this->eventPayload(['event_date' => '']))
             ->assertSessionHasErrors('event_date');
    }

    public function test_event_creation_rejects_invalid_type(): void
    {
        $this->actingAs($this->actingUser())
             ->post(route('calendar.events.store'), $this->eventPayload(['event_type' => 'invalid']))
             ->assertSessionHasErrors('event_type');
    }

    public function test_event_creation_validates_end_after_start(): void
    {
        $this->actingAs($this->actingUser())
             ->post(route('calendar.events.store'), $this->eventPayload([
                 'start_time' => '10:00',
                 'end_time'   => '09:00',
             ]))
             ->assertSessionHasErrors('end_time');
    }

    // ─── JSON events endpoint ─────────────────────────────────────────────────

    public function test_events_json_endpoint_returns_events_for_month(): void
    {
        $user = $this->actingUser();
        CalendarEvent::factory()->create([
            'event_date'  => '2026-05-15',
            'event_type'  => 'meeting',
            'created_by'  => $user->id,
        ]);

        $this->actingAs($user)
             ->getJson(route('calendar.events', ['year' => 2026, 'month' => 5]))
             ->assertOk()
             ->assertJsonCount(1, 'events')
             ->assertJsonPath('events.0.event_type', 'meeting');
    }

    public function test_events_json_endpoint_excludes_other_months(): void
    {
        $user = $this->actingUser();
        CalendarEvent::factory()->create(['event_date' => '2026-04-15', 'created_by' => $user->id]);

        $this->actingAs($user)
             ->getJson(route('calendar.events', ['year' => 2026, 'month' => 5]))
             ->assertOk()
             ->assertJsonCount(0, 'events');
    }

    // ─── show event ──────────────────────────────────────────────────────────

    public function test_user_can_fetch_event_detail_as_json(): void
    {
        $user  = $this->actingUser();
        $event = CalendarEvent::factory()->create([
            'title'      => 'Doctor Appointment',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
             ->getJson(route('calendar.events.show', $event))
             ->assertOk()
             ->assertJsonPath('title', 'Doctor Appointment');
    }

    // ─── update event ─────────────────────────────────────────────────────────

    public function test_owner_can_update_event(): void
    {
        $user  = $this->actingUser();
        $event = CalendarEvent::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)
             ->put(route('calendar.events.update', $event), $this->eventPayload([
                 'title' => 'Updated Title',
             ]))
             ->assertRedirect();

        $this->assertDatabaseHas('calendar_events', ['id' => $event->id, 'title' => 'Updated Title']);
    }

    public function test_non_owner_cannot_update_event(): void
    {
        $owner  = $this->actingUser();
        $other  = $this->actingUser();
        $event  = CalendarEvent::factory()->create(['created_by' => $owner->id]);

        $this->actingAs($other)
             ->put(route('calendar.events.update', $event), $this->eventPayload())
             ->assertForbidden();
    }

    // ─── delete event ─────────────────────────────────────────────────────────

    public function test_owner_can_delete_event(): void
    {
        $user  = $this->actingUser();
        $event = CalendarEvent::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)
             ->delete(route('calendar.events.destroy', $event))
             ->assertRedirect();

        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    public function test_non_owner_cannot_delete_event(): void
    {
        $owner = $this->actingUser();
        $other = $this->actingUser();
        $event = CalendarEvent::factory()->create(['created_by' => $owner->id]);

        $this->actingAs($other)
             ->delete(route('calendar.events.destroy', $event))
             ->assertForbidden();

        $this->assertDatabaseHas('calendar_events', ['id' => $event->id]);
    }

    // ─── JSON delete ─────────────────────────────────────────────────────────

    public function test_owner_can_delete_event_via_json(): void
    {
        $user  = $this->actingUser();
        $event = CalendarEvent::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)
             ->deleteJson(route('calendar.events.destroy', $event))
             ->assertOk()
             ->assertJson(['deleted' => true]);
    }
}
