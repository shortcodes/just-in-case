<?php

namespace Tests\Feature;

use App\Models\Custodianship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCustodianshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_edit_page(): void
    {
        $custodianship = Custodianship::factory()->create();

        $response = $this->get(route('custodianships.edit', $custodianship));

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_edit_others_custodianship(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get(route('custodianships.edit', $custodianship));

        $response->assertForbidden();
    }

    public function test_user_can_access_edit_page_for_own_custodianship(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('custodianships.edit', $custodianship));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Form')
            ->has('user')
            ->has('custodianship')
            ->has('intervalUnits')
        );
    }

    public function test_guest_cannot_update_custodianship(): void
    {
        $custodianship = Custodianship::factory()->create(['name' => 'Original Name']);

        $data = ['name' => 'Updated Name', 'intervalValue' => 90, 'intervalUnit' => 'days'];

        $response = $this->patch(route('custodianships.update', $custodianship), $data);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('custodianships', ['id' => $custodianship->id, 'name' => 'Original Name']);
    }

    public function test_user_cannot_update_others_custodianship(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $custodianship = Custodianship::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Original Name',
        ]);

        $data = ['name' => 'Updated Name', 'intervalValue' => 90, 'intervalUnit' => 'days'];

        $response = $this->actingAs($otherUser)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertForbidden();
        $this->assertDatabaseHas('custodianships', ['id' => $custodianship->id, 'name' => 'Original Name']);
    }

    public function test_user_can_update_own_custodianship(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Original Name',
            'interval' => 'P90D',
        ]);

        $data = [
            'name' => 'Updated Name',
            'intervalValue' => 30,
            'intervalUnit' => 'days',
            'recipients' => [],
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertRedirect(route('custodianships.show', $custodianship));
        $this->assertDatabaseHas('custodianships', [
            'id' => $custodianship->id,
            'name' => 'Updated Name',
            'interval' => 'P30D',
        ]);
    }

    public function test_user_can_update_message_content(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);
        $custodianship->message()->create(['content' => 'Original message']);

        $data = [
            'name' => $custodianship->name,
            'messageContent' => 'Updated message content',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => [],
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertRedirect(route('custodianships.show', $custodianship));

        $custodianship->refresh();
        $this->assertEquals('Updated message content', $custodianship->message->content);
    }

    public function test_user_can_add_message_to_custodianship_without_message(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseMissing('custodianship_messages', ['custodianship_id' => $custodianship->id]);

        $data = [
            'name' => $custodianship->name,
            'messageContent' => 'New message content',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => [],
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertRedirect(route('custodianships.show', $custodianship));

        $custodianship->refresh();
        $this->assertNotNull($custodianship->message);
        $this->assertEquals('New message content', $custodianship->message->content);
    }

    public function test_user_can_update_recipients(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);
        $custodianship->recipients()->create(['email' => 'old@example.com']);

        $data = [
            'name' => $custodianship->name,
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => ['new@example.com'],
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertRedirect(route('custodianships.show', $custodianship));
        $this->assertDatabaseMissing('recipients', [
            'custodianship_id' => $custodianship->id,
            'email' => 'old@example.com',
        ]);
        $this->assertDatabaseHas('recipients', [
            'custodianship_id' => $custodianship->id,
            'email' => 'new@example.com',
        ]);
    }

    public function test_user_can_add_recipients(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);
        $custodianship->recipients()->create(['email' => 'existing@example.com']);

        $data = [
            'name' => $custodianship->name,
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => ['existing@example.com', 'new@example.com'],
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertRedirect(route('custodianships.show', $custodianship));
        $this->assertDatabaseHas('recipients', [
            'custodianship_id' => $custodianship->id,
            'email' => 'existing@example.com',
        ]);
        $this->assertDatabaseHas('recipients', [
            'custodianship_id' => $custodianship->id,
            'email' => 'new@example.com',
        ]);
        $this->assertCount(2, $custodianship->fresh()->recipients);
    }

    public function test_user_can_remove_all_recipients(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);
        $custodianship->recipients()->create(['email' => 'recipient@example.com']);

        $data = [
            'name' => $custodianship->name,
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => [],
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertRedirect(route('custodianships.show', $custodianship));
        $this->assertDatabaseMissing('recipients', [
            'custodianship_id' => $custodianship->id,
        ]);
        $this->assertCount(0, $custodianship->fresh()->recipients);
    }

    public function test_update_with_reset_timer_workflow(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()
            ->active()
            ->create([
                'user_id' => $user->id,
                'name' => 'Original Name',
                'interval' => 'P90D',
                'last_reset_at' => now()->subDays(30),
                'next_trigger_at' => now()->addDays(60),
            ]);

        $oldLastResetAt = $custodianship->last_reset_at;
        $oldNextTriggerAt = $custodianship->next_trigger_at;

        $data = [
            'name' => 'Updated Name',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => [],
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertRedirect(route('custodianships.show', $custodianship));
        $this->assertDatabaseHas('custodianships', [
            'id' => $custodianship->id,
            'name' => 'Updated Name',
        ]);

        $resetResponse = $this->actingAs($user)->post(route('custodianships.reset', $custodianship));

        $resetResponse->assertRedirect();

        $custodianship->refresh();
        $this->assertNotEquals($oldLastResetAt->timestamp, $custodianship->last_reset_at->timestamp);
        $this->assertNotEquals($oldNextTriggerAt->timestamp, $custodianship->next_trigger_at->timestamp);
        $this->assertTrue($custodianship->last_reset_at->isToday());
    }

    public function test_validation_requires_name(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $data = [
            'name' => '',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertSessionHasErrors('name');
    }

    public function test_validation_requires_interval_value(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $data = [
            'name' => 'Test',
            'intervalUnit' => 'days',
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertSessionHasErrors('intervalValue');
    }

    public function test_validation_requires_valid_interval_unit(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $data = [
            'name' => 'Test',
            'intervalValue' => 90,
            'intervalUnit' => 'INVALID',
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertSessionHasErrors('intervalUnit');
    }

    public function test_validation_limits_recipients_to_two(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $data = [
            'name' => 'Test',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => ['test1@example.com', 'test2@example.com', 'test3@example.com'],
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertSessionHasErrors('recipients');
    }

    public function test_validation_requires_valid_email_for_recipients(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $data = [
            'name' => 'Test',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => ['invalid-email'],
        ];

        $response = $this->actingAs($user)->patch(route('custodianships.update', $custodianship), $data);

        $response->assertSessionHasErrors('recipients.0');
    }
}
