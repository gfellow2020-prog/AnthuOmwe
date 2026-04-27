<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    // ─── helpers ─────────────────────────────────────────────────────────────

    private function actingUser(): User
    {
        return User::factory()->create();
    }

    private function sendNotif(User $user, array $overrides = []): DatabaseNotification
    {
        $user->notify(new SystemNotification(
            title:   $overrides['title']   ?? 'Test title',
            message: $overrides['message'] ?? 'Test message',
            type:    $overrides['type']    ?? 'info',
        ));

        return $user->notifications()->latest()->first();
    }

    // ─── index ────────────────────────────────────────────────────────────────

    public function test_notifications_index_requires_auth(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_notifications_page(): void
    {
        $user = $this->actingUser();

        $this->actingAs($user)
             ->get(route('notifications.index'))
             ->assertOk()
             ->assertViewIs('notifications.index');
    }

    public function test_notifications_page_shows_own_notifications(): void
    {
        $user  = $this->actingUser();
        $other = $this->actingUser();

        $this->sendNotif($user,  ['title' => 'Own Notif']);
        $this->sendNotif($other, ['title' => 'Other Notif']);

        $this->actingAs($user)
             ->get(route('notifications.index'))
             ->assertOk()
             ->assertSee('Own Notif')
             ->assertDontSee('Other Notif');
    }

    // ─── mark read ────────────────────────────────────────────────────────────

    public function test_user_can_mark_notification_as_read(): void
    {
        $user  = $this->actingUser();
        $notif = $this->sendNotif($user);

        $this->assertNull($notif->read_at);

        $this->actingAs($user)
             ->postJson(route('notifications.read', $notif->id))
             ->assertOk()
             ->assertJson(['read' => true]);

        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $owner   = $this->actingUser();
        $other   = $this->actingUser();
        $notif   = $this->sendNotif($owner);

        $this->actingAs($other)
             ->postJson(route('notifications.read', $notif->id))
             ->assertForbidden();
    }

    // ─── mark all read ────────────────────────────────────────────────────────

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = $this->actingUser();
        $this->sendNotif($user);
        $this->sendNotif($user);
        $this->sendNotif($user);

        $this->assertEquals(3, $user->unreadNotifications()->count());

        $this->actingAs($user)
             ->post(route('notifications.read-all'))
             ->assertRedirect();

        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }

    // ─── dismiss ─────────────────────────────────────────────────────────────

    public function test_user_can_dismiss_own_notification(): void
    {
        $user  = $this->actingUser();
        $notif = $this->sendNotif($user);

        $this->assertEquals(1, $user->notifications()->count());

        $this->actingAs($user)
             ->delete(route('notifications.destroy', $notif->id))
             ->assertRedirect();

        $this->assertEquals(0, $user->fresh()->notifications()->count());
    }

    public function test_user_cannot_dismiss_other_users_notification(): void
    {
        $owner = $this->actingUser();
        $other = $this->actingUser();
        $notif = $this->sendNotif($owner);

        $this->actingAs($other)
             ->delete(route('notifications.destroy', $notif->id))
             ->assertForbidden();

        $this->assertEquals(1, $owner->fresh()->notifications()->count());
    }
}
