<?php

namespace Tests\Feature;

use App\Models\NotificationLog;
use App\Models\User;
use App\Services\HeaderNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HeaderNotificationReadTest extends TestCase
{
    use RefreshDatabase;

    private function logToday(string $subject): NotificationLog
    {
        return NotificationLog::create([
            'channel' => 'email',
            'event_type' => 'test',
            'recipient_name' => 'Test Customer',
            'message_body' => 'Hello',
            'subject' => $subject,
            'status' => 'sent',
        ]);
    }

    public function test_mark_all_read_is_saved_on_the_server(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->logToday('First alert');

        $this->assertSame(1, HeaderNotificationService::getNotificationsForUser($admin)['unread_count']);

        $this->actingAs($admin)
            ->postJson(route('notifications.read-all'))
            ->assertOk()
            ->assertJson(['unread_count' => 0]);

        $this->assertNotNull($admin->fresh()->notifications_read_at);
        // A fresh page load (new request, cache cleared) still shows nothing unread
        HeaderNotificationService::clearCacheForUser($admin->id);
        $this->assertSame(0, HeaderNotificationService::getNotificationsForUser($admin->fresh())['unread_count']);
    }

    public function test_new_notifications_after_marking_read_count_as_unread(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->logToday('Old alert');
        $this->actingAs($admin)->postJson(route('notifications.read-all'))->assertOk();

        Carbon::setTestNow(now()->addMinute());
        $this->logToday('New alert');
        HeaderNotificationService::clearCacheForUser($admin->id);

        $this->assertSame(1, HeaderNotificationService::getNotificationsForUser($admin->fresh())['unread_count']);
        Carbon::setTestNow();
    }

    public function test_guests_cannot_mark_notifications_read(): void
    {
        $this->postJson(route('notifications.read-all'))->assertUnauthorized();
    }
}
