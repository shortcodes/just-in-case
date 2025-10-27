<?php

namespace Tests\Feature;

use App\Models\Custodianship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetCustodianshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_reset_their_custodianship(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'interval' => 'P30D',
            'last_reset_at' => now()->subDays(10),
            'next_trigger_at' => now()->addDays(20),
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('custodianships.reset', $custodianship));

        $response->assertRedirect();

        $custodianship->refresh();

        $this->assertNotNull($custodianship->last_reset_at);
        $this->assertNotNull($custodianship->next_trigger_at);
        $this->assertTrue($custodianship->last_reset_at->isToday());
        $this->assertEqualsWithDelta(
            now()->addDays(30)->timestamp,
            $custodianship->next_trigger_at->timestamp,
            60
        );
    }

    public function test_unauthorized_user_cannot_reset_others_custodianship(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $custodianship = Custodianship::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->post(route('custodianships.reset', $custodianship));

        $response->assertForbidden();
    }

    public function test_reset_creates_audit_log_record(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'interval' => 'P90D',
        ]);

        $this->assertCount(0, $custodianship->resets);

        $response = $this
            ->actingAs($user)
            ->post(route('custodianships.reset', $custodianship));

        $response->assertRedirect();

        $custodianship->refresh();
        $this->assertCount(1, $custodianship->resets);

        $reset = $custodianship->resets->first();
        $this->assertEquals($user->id, $reset->user_id);
        $this->assertEquals('manual_button', $reset->reset_method);
        $this->assertNotNull($reset->ip_address);
        $this->assertNotNull($reset->user_agent);
        $this->assertNotNull($reset->created_at);
    }

    public function test_timestamps_are_updated_correctly(): void
    {
        $user = User::factory()->create();
        $oldLastResetAt = now()->subDays(50);
        $oldNextTriggerAt = now()->addDays(40);

        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'interval' => 'P90D',
            'last_reset_at' => $oldLastResetAt,
            'next_trigger_at' => $oldNextTriggerAt,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('custodianships.reset', $custodianship));

        $response->assertRedirect();

        $custodianship->refresh();

        $this->assertNotEquals($oldLastResetAt->timestamp, $custodianship->last_reset_at->timestamp);
        $this->assertNotEquals($oldNextTriggerAt->timestamp, $custodianship->next_trigger_at->timestamp);
        $this->assertTrue($custodianship->last_reset_at->isToday());
    }

    public function test_guest_cannot_reset_custodianship(): void
    {
        $custodianship = Custodianship::factory()->create();

        $response = $this->post(route('custodianships.reset', $custodianship));

        $response->assertRedirect(route('login'));
    }
}
