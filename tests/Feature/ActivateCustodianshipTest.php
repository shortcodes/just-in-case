<?php

namespace Tests\Feature;

use App\Models\Custodianship;
use App\Models\CustodianshipMessage;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivateCustodianshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_activate_their_draft_custodianship(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'interval' => 'P30D',
            'last_reset_at' => null,
            'next_trigger_at' => null,
            'activated_at' => null,
        ]);

        CustodianshipMessage::factory()->create([
            'custodianship_id' => $custodianship->id,
            'content' => 'Test message content',
        ]);

        Recipient::factory()->create([
            'custodianship_id' => $custodianship->id,
            'email' => 'recipient@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('custodianships.activate', $custodianship));

        $response->assertRedirect(route('custodianships.index'));
        $response->assertSessionHas('success', 'Custodianship activated successfully.');

        $custodianship->refresh();

        $this->assertEquals('active', $custodianship->status);
        $this->assertNotNull($custodianship->last_reset_at);
        $this->assertNotNull($custodianship->next_trigger_at);
        $this->assertNotNull($custodianship->activated_at);
        $this->assertTrue($custodianship->last_reset_at->isToday());
        $this->assertTrue($custodianship->activated_at->isToday());
    }

    public function test_unauthorized_user_cannot_activate_others_custodianship(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);
        $custodianship = Custodianship::factory()->create([
            'user_id' => $owner->id,
            'status' => 'draft',
        ]);

        CustodianshipMessage::factory()->create([
            'custodianship_id' => $custodianship->id,
            'content' => 'Test message content',
        ]);

        Recipient::factory()->create([
            'custodianship_id' => $custodianship->id,
            'email' => 'recipient@example.com',
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->post(route('custodianships.activate', $custodianship));

        $response->assertForbidden();

        $custodianship->refresh();
        $this->assertEquals('draft', $custodianship->status);
    }

    public function test_cannot_activate_if_email_not_verified(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'interval' => 'P30D',
        ]);

        CustodianshipMessage::factory()->create([
            'custodianship_id' => $custodianship->id,
            'content' => 'Test message content',
        ]);

        Recipient::factory()->create([
            'custodianship_id' => $custodianship->id,
            'email' => 'recipient@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('custodianships.activate', $custodianship));

        $response->assertSessionHasErrors(['email_verification']);

        $custodianship->refresh();
        $this->assertEquals('draft', $custodianship->status);
    }

    public function test_cannot_activate_if_missing_message(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'interval' => 'P30D',
        ]);

        Recipient::factory()->create([
            'custodianship_id' => $custodianship->id,
            'email' => 'recipient@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('custodianships.activate', $custodianship));

        $response->assertSessionHasErrors(['message']);

        $custodianship->refresh();
        $this->assertEquals('draft', $custodianship->status);
    }

    public function test_cannot_activate_if_message_has_empty_content(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'interval' => 'P30D',
        ]);

        CustodianshipMessage::factory()->create([
            'custodianship_id' => $custodianship->id,
            'content' => '',
        ]);

        Recipient::factory()->create([
            'custodianship_id' => $custodianship->id,
            'email' => 'recipient@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('custodianships.activate', $custodianship));

        $response->assertSessionHasErrors(['message']);

        $custodianship->refresh();
        $this->assertEquals('draft', $custodianship->status);
    }

    public function test_cannot_activate_if_missing_recipients(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'interval' => 'P30D',
        ]);

        CustodianshipMessage::factory()->create([
            'custodianship_id' => $custodianship->id,
            'content' => 'Test message content',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('custodianships.activate', $custodianship));

        $response->assertSessionHasErrors(['recipients']);

        $custodianship->refresh();
        $this->assertEquals('draft', $custodianship->status);
    }

    public function test_cannot_activate_if_already_active(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'interval' => 'P30D',
            'last_reset_at' => now(),
            'next_trigger_at' => now()->addDays(30),
        ]);

        CustodianshipMessage::factory()->create([
            'custodianship_id' => $custodianship->id,
            'content' => 'Test message content',
        ]);

        Recipient::factory()->create([
            'custodianship_id' => $custodianship->id,
            'email' => 'recipient@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('custodianships.activate', $custodianship));

        $response->assertSessionHasErrors(['status']);

        $custodianship->refresh();
        $this->assertEquals('active', $custodianship->status);
    }

    public function test_timestamps_are_set_correctly_after_activation(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'interval' => 'P90D',
            'last_reset_at' => null,
            'next_trigger_at' => null,
            'activated_at' => null,
        ]);

        CustodianshipMessage::factory()->create([
            'custodianship_id' => $custodianship->id,
            'content' => 'Test message content',
        ]);

        Recipient::factory()->create([
            'custodianship_id' => $custodianship->id,
            'email' => 'recipient@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('custodianships.activate', $custodianship));

        $response->assertRedirect(route('custodianships.index'));

        $custodianship->refresh();

        $this->assertEquals('active', $custodianship->status);
        $this->assertNotNull($custodianship->last_reset_at);
        $this->assertNotNull($custodianship->next_trigger_at);
        $this->assertNotNull($custodianship->activated_at);
        $this->assertTrue($custodianship->last_reset_at->isToday());
        $this->assertTrue($custodianship->activated_at->isToday());
        $this->assertEqualsWithDelta(
            now()->addDays(90)->timestamp,
            $custodianship->next_trigger_at->timestamp,
            60
        );
    }

    public function test_guest_cannot_activate_custodianship(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $response = $this->post(route('custodianships.activate', $custodianship));

        $response->assertRedirect(route('login'));
    }
}
