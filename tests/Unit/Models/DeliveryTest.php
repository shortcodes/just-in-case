<?php

namespace Tests\Unit\Models;

use App\Models\Custodianship;
use App\Models\Delivery;
use App\Models\Recipient;
use App\Models\User;
use App\Notifications\CustodianshipOwnerAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retry_returns_true_when_attempts_available(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'attempt_number' => 1,
                'max_attempts' => 3,
                'status' => 'pending',
            ]);

        $this->assertTrue($delivery->canRetry());
    }

    public function test_can_retry_returns_false_when_max_attempts_reached(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'attempt_number' => 3,
                'max_attempts' => 3,
                'status' => 'pending',
            ]);

        $this->assertFalse($delivery->canRetry());
    }

    public function test_can_retry_returns_false_when_already_delivered(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->delivered()
            ->create([
                'attempt_number' => 1,
                'max_attempts' => 3,
            ]);

        $this->assertFalse($delivery->canRetry());
    }

    public function test_fail_marks_as_failed_when_permanent(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();
        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'attempt_number' => 1,
                'max_attempts' => 3,
                'status' => 'pending',
            ]);

        $delivery->fail('Permanent error', true);

        $delivery->refresh();

        $this->assertEquals('failed', $delivery->status);
        $this->assertEquals('Permanent error', $delivery->error_message);
        $this->assertNull($delivery->next_retry_at);
    }

    public function test_fail_sends_owner_alert_when_permanent(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();
        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'attempt_number' => 1,
                'max_attempts' => 3,
                'status' => 'pending',
            ]);

        $delivery->fail('Permanent error', true);

        Notification::assertSentTo(
            $user,
            CustodianshipOwnerAlert::class,
            function ($notification) use ($delivery) {
                return $notification->delivery->id === $delivery->id
                    && $notification->type === 'failed'
                    && $notification->errorMessage === 'Permanent error';
            }
        );
    }

    public function test_fail_marks_as_failed_when_max_attempts_reached(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();
        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'attempt_number' => 3,
                'max_attempts' => 3,
                'status' => 'pending',
            ]);

        $delivery->fail('Max attempts reached');

        $delivery->refresh();

        $this->assertEquals('failed', $delivery->status);
        $this->assertNull($delivery->next_retry_at);
    }

    public function test_fail_sets_next_retry_when_attempts_available(): void
    {
        config(['custodianship.delivery.retry_intervals' => [60, 120, 240]]);

        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();
        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'attempt_number' => 1,
                'max_attempts' => 3,
                'status' => 'pending',
            ]);

        $beforeFail = now();
        $delivery->fail('Temporary error');

        $delivery->refresh();

        $this->assertNotNull($delivery->next_retry_at);
        $this->assertGreaterThanOrEqual($beforeFail->addSeconds(59)->timestamp, $delivery->next_retry_at->timestamp);
        $this->assertLessThanOrEqual($beforeFail->addSeconds(61)->timestamp, $delivery->next_retry_at->timestamp);
    }

    public function test_fail_uses_last_interval_when_exceeding_config_array(): void
    {
        config(['custodianship.delivery.retry_intervals' => [60, 120]]);

        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();
        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'attempt_number' => 3,
                'max_attempts' => 5,
                'status' => 'pending',
            ]);

        $beforeFail = now();
        $delivery->fail('Temporary error');

        $delivery->refresh();

        $this->assertNotNull($delivery->next_retry_at);
        $this->assertGreaterThanOrEqual($beforeFail->addSeconds(119)->timestamp, $delivery->next_retry_at->timestamp);
        $this->assertLessThanOrEqual($beforeFail->addSeconds(121)->timestamp, $delivery->next_retry_at->timestamp);
    }

    public function test_stale_scope_finds_stale_pending_deliveries(): void
    {
        config(['custodianship.delivery.pending_timeout' => 3600]);

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient1 = Recipient::factory()->for($custodianship)->create();
        $recipient2 = Recipient::factory()->for($custodianship)->create();

        $stalePending = Delivery::factory()
            ->for($custodianship)
            ->for($recipient1)
            ->create([
                'status' => 'pending',
                'next_retry_at' => null,
                'created_at' => now()->subHours(2),
                'last_retry_at' => null,
            ]);

        $recentPending = Delivery::factory()
            ->for($custodianship)
            ->for($recipient2)
            ->create([
                'status' => 'pending',
                'next_retry_at' => null,
                'created_at' => now()->subMinutes(30),
                'last_retry_at' => null,
            ]);

        $staleDeliveries = Delivery::stale()->get();

        $this->assertTrue($staleDeliveries->contains($stalePending));
        $this->assertFalse($staleDeliveries->contains($recentPending));
    }

    public function test_stale_scope_ignores_deliveries_with_retry_scheduled(): void
    {
        config(['custodianship.delivery.pending_timeout' => 3600]);

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $withRetry = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'status' => 'pending',
                'next_retry_at' => now()->addHour(),
                'created_at' => now()->subHours(2),
            ]);

        $staleDeliveries = Delivery::stale()->get();

        $this->assertFalse($staleDeliveries->contains($withRetry));
    }

    public function test_stale_scope_ignores_non_pending_deliveries(): void
    {
        config(['custodianship.delivery.pending_timeout' => 3600]);

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient1 = Recipient::factory()->for($custodianship)->create();
        $recipient2 = Recipient::factory()->for($custodianship)->create();

        $delivered = Delivery::factory()
            ->for($custodianship)
            ->for($recipient1)
            ->delivered()
            ->create([
                'created_at' => now()->subHours(2),
            ]);

        $failed = Delivery::factory()
            ->for($custodianship)
            ->for($recipient2)
            ->failed()
            ->create([
                'created_at' => now()->subHours(2),
            ]);

        $staleDeliveries = Delivery::stale()->get();

        $this->assertFalse($staleDeliveries->contains($delivered));
        $this->assertFalse($staleDeliveries->contains($failed));
    }

    public function test_get_last_attempted_at_returns_last_retry_at_when_present(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $lastRetryAt = now()->subHour();
        $createdAt = now()->subDays(2);

        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'last_retry_at' => $lastRetryAt,
                'created_at' => $createdAt,
            ]);

        $this->assertEquals($lastRetryAt->timestamp, $delivery->last_attempted_at->timestamp);
    }

    public function test_get_last_attempted_at_returns_created_at_when_no_retry(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $createdAt = now()->subDays(2);

        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'last_retry_at' => null,
                'created_at' => $createdAt,
            ]);

        $this->assertEquals($createdAt->timestamp, $delivery->last_attempted_at->timestamp);
    }

    public function test_fail_updates_custodianship_delivery_status(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();
        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'attempt_number' => 3,
                'max_attempts' => 3,
                'status' => 'pending',
            ]);

        $delivery->fail('Error', true);

        $custodianship->refresh();
        $this->assertEquals('completed', $custodianship->status);
    }
}
