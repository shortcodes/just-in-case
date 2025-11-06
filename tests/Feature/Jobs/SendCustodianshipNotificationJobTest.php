<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendCustodianshipNotificationJob;
use App\Models\Custodianship;
use App\Models\Delivery;
use App\Models\Recipient;
use App\Models\User;
use App\Notifications\ExpiredCustodianshipNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendCustodianshipNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creates_delivery_record_when_not_exists(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->completed()->create(['user_id' => $user->id]);
        $recipient = Recipient::factory()->for($custodianship)->create();

        Notification::fake();

        $job = new SendCustodianshipNotificationJob($custodianship, $recipient);
        $job->handle();

        $this->assertDatabaseHas('deliveries', [
            'custodianship_id' => $custodianship->id,
            'recipient_id' => $recipient->id,
            'recipient_email' => $recipient->email,
            'status' => 'pending',
            'attempt_number' => 1,
        ]);
    }

    public function test_job_sets_max_attempts_from_config(): void
    {
        config(['custodianship.delivery.max_attempts' => 5]);

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->completed()->create(['user_id' => $user->id]);
        $recipient = Recipient::factory()->for($custodianship)->create();

        Notification::fake();

        $job = new SendCustodianshipNotificationJob($custodianship, $recipient);
        $job->handle();

        $this->assertDatabaseHas('deliveries', [
            'custodianship_id' => $custodianship->id,
            'recipient_id' => $recipient->id,
            'max_attempts' => 5,
        ]);
    }

    public function test_job_updates_delivery_for_retry(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->completed()->create(['user_id' => $user->id]);
        $recipient = Recipient::factory()->for($custodianship)->create();

        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'status' => 'pending',
                'attempt_number' => 1,
                'error_message' => 'Previous error',
                'next_retry_at' => now()->addHour(),
            ]);

        Notification::fake();

        $job = new SendCustodianshipNotificationJob($custodianship, $recipient, 2);
        $job->handle();

        $delivery->refresh();

        $this->assertEquals(2, $delivery->attempt_number);
        $this->assertNotNull($delivery->last_retry_at);
        $this->assertNull($delivery->next_retry_at);
        $this->assertNull($delivery->error_message);
    }

    public function test_job_sends_notification_to_recipient(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->completed()->create(['user_id' => $user->id]);
        $recipient = Recipient::factory()->for($custodianship)->create();

        Notification::fake();

        $job = new SendCustodianshipNotificationJob($custodianship, $recipient);
        $job->handle();

        Notification::assertSentTo(
            $recipient,
            ExpiredCustodianshipNotification::class,
            function ($notification) use ($custodianship, $recipient) {
                return $notification->custodianship->id === $custodianship->id
                    && $notification->recipient->id === $recipient->id;
            }
        );
    }

    public function test_job_attaches_delivery_to_notification(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->completed()->create(['user_id' => $user->id]);
        $recipient = Recipient::factory()->for($custodianship)->create();

        Notification::fake();

        $job = new SendCustodianshipNotificationJob($custodianship, $recipient);
        $job->handle();

        Notification::assertSentTo(
            $recipient,
            ExpiredCustodianshipNotification::class,
            function ($notification) {
                return $notification->delivery instanceof Delivery;
            }
        );
    }

    public function test_job_uses_notifications_queue(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->completed()->create(['user_id' => $user->id]);
        $recipient = Recipient::factory()->for($custodianship)->create();

        $job = new SendCustodianshipNotificationJob($custodianship, $recipient);

        $this->assertEquals('notifications', $job->queue);
    }

    public function test_job_generates_valid_mailgun_message_id(): void
    {
        config(['mail.mailers.mailgun.domain' => 'mg.example.com']);

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->completed()->create(['user_id' => $user->id]);
        $recipient = Recipient::factory()->for($custodianship)->create();

        Notification::fake();

        $job = new SendCustodianshipNotificationJob($custodianship, $recipient);
        $job->handle();

        $delivery = Delivery::where('custodianship_id', $custodianship->id)
            ->where('recipient_id', $recipient->id)
            ->first();

        $this->assertNotNull($delivery->mailgun_message_id);
        $this->assertStringContainsString('@', $delivery->mailgun_message_id);
    }

    public function test_job_handles_custom_attempt_number(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->completed()->create(['user_id' => $user->id]);
        $recipient = Recipient::factory()->for($custodianship)->create();

        Notification::fake();

        $job = new SendCustodianshipNotificationJob($custodianship, $recipient, 3);
        $job->handle();

        $this->assertDatabaseHas('deliveries', [
            'custodianship_id' => $custodianship->id,
            'recipient_id' => $recipient->id,
            'attempt_number' => 3,
        ]);
    }
}
