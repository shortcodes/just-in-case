<?php

namespace Tests\Feature\Listeners;

use App\Listeners\CreateDeliveryRecord;
use App\Models\Custodianship;
use App\Models\Delivery;
use App\Models\Recipient;
use App\Models\User;
use App\Notifications\ExpiredCustodianshipNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationSending;
use Tests\TestCase;

class CreateDeliveryRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_delivery_record_when_expired_custodianship_notification_is_sending(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient = Recipient::factory()->for($custodianship)->create(['email' => 'test@example.com']);

        $notification = new ExpiredCustodianshipNotification($custodianship, $recipient);

        $event = new NotificationSending($recipient, $notification, 'mail');

        $listener = new CreateDeliveryRecord;
        $listener->handle($event);

        $this->assertDatabaseHas('deliveries', [
            'custodianship_id' => $custodianship->id,
            'recipient_id' => $recipient->id,
            'recipient_email' => 'test@example.com',
            'status' => 'pending',
        ]);

        $this->assertNotNull($notification->delivery);
        $this->assertNotNull($notification->delivery->mailgun_message_id);
        $this->assertStringContainsString('@', $notification->delivery->mailgun_message_id);
    }

    public function test_creates_multiple_delivery_records_for_retries(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $notification = new ExpiredCustodianshipNotification($custodianship, $recipient);
        $listener = new CreateDeliveryRecord;

        $listener->handle(new NotificationSending($recipient, $notification, 'mail'));
        $listener->handle(new NotificationSending($recipient, $notification, 'mail'));
        $listener->handle(new NotificationSending($recipient, $notification, 'mail'));

        $this->assertEquals(3, Delivery::where('recipient_id', $recipient->id)->count());
    }

    public function test_does_not_create_delivery_for_non_mail_channel(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $notification = new ExpiredCustodianshipNotification($custodianship, $recipient);

        $event = new NotificationSending($recipient, $notification, 'database');

        $listener = new CreateDeliveryRecord;
        $listener->handle($event);

        $this->assertDatabaseCount('deliveries', 0);
    }

    public function test_does_not_create_delivery_for_other_notifications(): void
    {
        $user = User::factory()->create();
        $recipient = Recipient::factory()->for(Custodianship::factory()->for($user))->create();

        $notification = new class {};

        $event = new NotificationSending($recipient, $notification, 'mail');

        $listener = new CreateDeliveryRecord;
        $listener->handle($event);

        $this->assertDatabaseCount('deliveries', 0);
    }
}
