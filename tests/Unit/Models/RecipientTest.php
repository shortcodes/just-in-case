<?php

namespace Tests\Unit\Models;

use App\Models\Custodianship;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipientTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_notification_for_mail_returns_email(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient = Recipient::factory()->for($custodianship)->create([
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('test@example.com', $recipient->routeNotificationForMail());
    }

    public function test_custodianship_relationship(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $this->assertInstanceOf(Custodianship::class, $recipient->custodianship);
        $this->assertEquals($custodianship->id, $recipient->custodianship->id);
    }

    public function test_latest_delivery_relationship(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $oldDelivery = $custodianship->deliveries()->create([
            'recipient_id' => $recipient->id,
            'recipient_email' => $recipient->email,
            'status' => 'delivered',
            'created_at' => now()->subDay(),
        ]);

        $newDelivery = $custodianship->deliveries()->create([
            'recipient_id' => $recipient->id,
            'recipient_email' => $recipient->email,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $this->assertEquals($newDelivery->id, $recipient->latestDelivery->id);
    }
}
