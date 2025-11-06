<?php

namespace Tests\Feature;

use App\Models\Custodianship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCustodianshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_delete_custodianship(): void
    {
        $custodianship = Custodianship::factory()->create();

        $response = $this->delete(route('custodianships.destroy', $custodianship));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('custodianships', ['id' => $custodianship->id]);
    }

    public function test_user_cannot_delete_others_custodianship(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete(route('custodianships.destroy', $custodianship));

        $response->assertForbidden();
        $this->assertDatabaseHas('custodianships', ['id' => $custodianship->id]);
    }

    public function test_user_can_delete_own_custodianship(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('custodianships.destroy', $custodianship));

        $response->assertRedirect(route('custodianships.index'));
        $response->assertSessionHas('success', 'Custodianship deleted successfully.');
        $this->assertDatabaseMissing('custodianships', ['id' => $custodianship->id]);
    }

    public function test_deleting_custodianship_deletes_associated_recipients(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);
        $custodianship->recipients()->create(['email' => 'test@example.com']);
        $recipientId = $custodianship->recipients->first()->id;

        $response = $this->actingAs($user)->delete(route('custodianships.destroy', $custodianship));

        $response->assertRedirect(route('custodianships.index'));
        $this->assertDatabaseMissing('recipients', ['id' => $recipientId]);
    }

    public function test_deleting_custodianship_deletes_associated_message(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);
        $custodianship->message()->create(['content' => 'Test message']);
        $messageId = $custodianship->message->id;

        $response = $this->actingAs($user)->delete(route('custodianships.destroy', $custodianship));

        $response->assertRedirect(route('custodianships.index'));
        $this->assertDatabaseMissing('custodianship_messages', ['id' => $messageId]);
    }

    public function test_deleting_custodianship_deletes_associated_resets(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->active()->create(['user_id' => $user->id]);
        $custodianship->resets()->create([
            'user_id' => $user->id,
            'reset_method' => 'manual_button',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'created_at' => now(),
        ]);
        $resetId = $custodianship->resets->first()->id;

        $response = $this->actingAs($user)->delete(route('custodianships.destroy', $custodianship));

        $response->assertRedirect(route('custodianships.index'));
        $this->assertDatabaseMissing('resets', ['id' => $resetId]);
    }

    public function test_deleting_custodianship_deletes_associated_deliveries(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->completed()->create(['user_id' => $user->id]);
        $recipient = $custodianship->recipients()->create(['email' => 'test@example.com']);
        $delivery = $custodianship->deliveries()->create([
            'recipient_id' => $recipient->id,
            'recipient_email' => $recipient->email,
            'status' => 'delivered',
        ]);

        $response = $this->actingAs($user)->delete(route('custodianships.destroy', $custodianship));

        $response->assertRedirect(route('custodianships.index'));
        $this->assertDatabaseMissing('deliveries', ['id' => $delivery->id]);
    }

    public function test_user_cannot_delete_completed_custodianship_with_pending_deliveries(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->completed()->create(['user_id' => $user->id]);
        $recipient = $custodianship->recipients()->create(['email' => 'test@example.com']);
        $custodianship->deliveries()->create([
            'recipient_id' => $recipient->id,
            'recipient_email' => $recipient->email,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->delete(route('custodianships.destroy', $custodianship));

        $response->assertForbidden();
        $this->assertDatabaseHas('custodianships', ['id' => $custodianship->id]);
    }
}
