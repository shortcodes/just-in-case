<?php

namespace Tests\Feature;

use App\Models\Custodianship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowCustodianshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_custodianship_show(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $response = $this->get(route('custodianships.show', $custodianship));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_their_own_custodianship(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('custodianships.show', $custodianship));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Show')
            ->has('user')
            ->has('custodianship')
            ->has('resetHistory')
        );
    }

    public function test_user_cannot_view_another_users_custodianship(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user1)->create();

        $response = $this->actingAs($user2)->get(route('custodianships.show', $custodianship));

        $response->assertForbidden();
    }

    public function test_show_includes_custodianship_relationships(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $custodianship->message()->create([
            'content' => 'Test message',
        ]);

        $custodianship->recipients()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.show', $custodianship));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Show')
            ->where('custodianship.name', $custodianship->name)
            ->where('custodianship.messageContent', 'Test message')
            ->has('custodianship.recipients', 1)
        );
    }

    public function test_show_includes_reset_history(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create([
            'status' => 'active',
            'last_reset_at' => now(),
            'next_trigger_at' => now()->addDays(90),
        ]);

        $custodianship->resets()->create([
            'user_id' => $user->id,
            'reset_method' => 'manual_button',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.show', $custodianship));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Show')
            ->has('resetHistory', 1)
            ->where('resetHistory.0.resetMethod', 'manual_button')
            ->where('resetHistory.0.ipAddress', '127.0.0.1')
        );
    }
}
