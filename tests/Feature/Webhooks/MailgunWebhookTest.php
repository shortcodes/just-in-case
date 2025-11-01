<?php

namespace Tests\Feature\Webhooks;

use App\Models\Custodianship;
use App\Models\Delivery;
use App\Models\Recipient;
use App\Models\User;
use App\Notifications\CustodianshipOwnerAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MailgunWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_updates_delivery_to_delivered(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->withMailgunId('<test-message-id@example.com>')
            ->create(['status' => 'pending']);

        $payload = [
            'signature' => [
                'timestamp' => time(),
                'token' => 'test-token',
                'signature' => 'test-signature',
            ],
            'event-data' => [
                'event' => 'delivered',
                'message' => [
                    'headers' => [
                        'message-id' => '<test-message-id@example.com>',
                    ],
                ],
            ],
        ];

        config(['services.mailgun.webhook_signing_key' => null]);

        $response = $this->postJson(route('webhooks.mailgun'), $payload);

        $response->assertOk();

        $delivery->refresh();
        $this->assertEquals('delivered', $delivery->status);
        $this->assertNotNull($delivery->delivered_at);
    }

    public function test_webhook_updates_delivery_to_failed(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->withMailgunId('<test-message-id@example.com>')
            ->create(['status' => 'pending']);

        $payload = [
            'signature' => [
                'timestamp' => time(),
                'token' => 'test-token',
                'signature' => 'test-signature',
            ],
            'event-data' => [
                'event' => 'failed',
                'message' => [
                    'headers' => [
                        'message-id' => '<test-message-id@example.com>',
                    ],
                ],
                'delivery-status' => [
                    'message' => 'SMTP error',
                ],
            ],
        ];

        config(['services.mailgun.webhook_signing_key' => null]);

        $response = $this->postJson(route('webhooks.mailgun'), $payload);

        $response->assertOk();

        $delivery->refresh();
        $this->assertEquals('failed', $delivery->status);

        Notification::assertSentTo($user, CustodianshipOwnerAlert::class);
    }

    public function test_webhook_handles_bounced_event(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->withMailgunId('<test-message-id@example.com>')
            ->create(['status' => 'pending']);

        $payload = [
            'signature' => [
                'timestamp' => time(),
                'token' => 'test-token',
                'signature' => 'test-signature',
            ],
            'event-data' => [
                'event' => 'bounced',
                'message' => [
                    'headers' => [
                        'message-id' => '<test-message-id@example.com>',
                    ],
                ],
                'delivery-status' => [
                    'message' => 'Mailbox does not exist',
                ],
            ],
        ];

        config(['services.mailgun.webhook_signing_key' => null]);

        $response = $this->postJson(route('webhooks.mailgun'), $payload);

        $response->assertOk();

        $delivery->refresh();
        $this->assertEquals('failed', $delivery->status);

        Notification::assertSentTo($user, CustodianshipOwnerAlert::class);
    }

    public function test_webhook_returns_404_for_unknown_message_id(): void
    {
        $payload = [
            'signature' => [
                'timestamp' => time(),
                'token' => 'test-token',
                'signature' => 'test-signature',
            ],
            'event-data' => [
                'event' => 'delivered',
                'message' => [
                    'headers' => [
                        'message-id' => '<unknown-message-id@example.com>',
                    ],
                ],
            ],
        ];

        config(['services.mailgun.webhook_signing_key' => null]);

        $response = $this->postJson(route('webhooks.mailgun'), $payload);

        $response->assertOk();
        $response->assertJson(['error' => 'Delivery not found']);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $timestamp = time();
        $token = 'test-token';
        $signingKey = 'test-signing-key';

        config(['services.mailgun.webhook_signing_key' => $signingKey]);

        $payload = [
            'signature' => [
                'timestamp' => $timestamp,
                'token' => $token,
                'signature' => 'invalid-signature',
            ],
            'event-data' => [
                'event' => 'delivered',
                'message' => [
                    'headers' => [
                        'message-id' => '<test-message-id@example.com>',
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('webhooks.mailgun'), $payload);

        $response->assertStatus(403);
    }

    public function test_webhook_accepts_valid_signature(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->withMailgunId('<test-message-id@example.com>')
            ->create(['status' => 'pending']);

        $timestamp = time();
        $token = 'test-token';
        $signingKey = 'test-signing-key';

        $signature = hash_hmac('sha256', $timestamp.$token, $signingKey);

        config(['services.mailgun.webhook_signing_key' => $signingKey]);

        $payload = [
            'signature' => [
                'timestamp' => $timestamp,
                'token' => $token,
                'signature' => $signature,
            ],
            'event-data' => [
                'event' => 'delivered',
                'message' => [
                    'headers' => [
                        'message-id' => '<test-message-id@example.com>',
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('webhooks.mailgun'), $payload);

        $response->assertOk();

        $delivery->refresh();
        $this->assertEquals('delivered', $delivery->status);
    }
}
