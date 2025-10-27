<?php

namespace Tests\Feature;

use App\Models\Custodianship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCustodianshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_create_page(): void
    {
        $response = $this->get(route('custodianships.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('custodianships.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Create')
            ->has('user')
            ->has('intervalUnits')
        );
    }

    public function test_create_page_includes_correct_interval_units(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('custodianships.create'));

        $response->assertInertia(fn ($page) => $page
            ->where('intervalUnits', [
                ['value' => 'minutes', 'label' => 'Minutes'],
                ['value' => 'hours', 'label' => 'Hours'],
                ['value' => 'days', 'label' => 'Days'],
            ])
        );
    }

    public function test_guest_cannot_create_custodianship(): void
    {
        $data = [
            'name' => 'Test Custodianship',
            'messageContent' => 'Test message',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->post(route('custodianships.store'), $data);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('custodianships', 0);
    }

    public function test_user_can_create_custodianship(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test Custodianship',
            'messageContent' => 'Test message',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $custodianship = Custodianship::first();

        $response->assertRedirect(route('custodianships.show', $custodianship));
        $this->assertDatabaseCount('custodianships', 1);
        $this->assertDatabaseHas('custodianships', [
            'name' => 'Test Custodianship',
            'status' => 'draft',
            'interval' => 'P90D',
        ]);
        $this->assertNotNull($custodianship);
        $this->assertNull($custodianship->activated_at);
        $this->assertNull($custodianship->last_reset_at);
        $this->assertNull($custodianship->next_trigger_at);
        $this->assertDatabaseHas('custodianship_messages', [
            'custodianship_id' => $custodianship->id,
        ]);
        $this->assertDatabaseHas('recipients', [
            'custodianship_id' => $custodianship->id,
            'email' => 'test@example.com',
        ]);
    }

    public function test_custodianship_creation_with_multiple_recipients(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test Custodianship',
            'messageContent' => 'Test message',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => ['test1@example.com', 'test2@example.com'],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $custodianship = Custodianship::first();

        $this->assertDatabaseCount('recipients', 2);
        $this->assertDatabaseHas('recipients', [
            'custodianship_id' => $custodianship->id,
            'email' => 'test1@example.com',
        ]);
        $this->assertDatabaseHas('recipients', [
            'custodianship_id' => $custodianship->id,
            'email' => 'test2@example.com',
        ]);
    }

    public function test_custodianship_creation_without_message_content(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test Custodianship',
            'messageContent' => null,
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $this->assertDatabaseCount('custodianship_messages', 0);
    }

    public function test_validation_requires_name(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => '',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $response->assertSessionHasErrors('name');
    }

    public function test_validation_requires_interval_value(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test',
            'intervalUnit' => 'days',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $response->assertSessionHasErrors('intervalValue');
    }

    public function test_validation_requires_valid_interval_unit(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test',
            'intervalValue' => 90,
            'intervalUnit' => 'INVALID',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $response->assertSessionHasErrors('intervalUnit');
    }

    public function test_custodianship_can_be_created_without_recipients(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => [],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $custodianship = Custodianship::first();

        $response->assertRedirect(route('custodianships.show', $custodianship));
        $this->assertDatabaseCount('recipients', 0);
        $this->assertDatabaseHas('custodianships', [
            'name' => 'Test',
            'status' => 'draft',
        ]);
    }

    public function test_validation_limits_recipients_to_two(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => ['test1@example.com', 'test2@example.com', 'test3@example.com'],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $response->assertSessionHasErrors('recipients');
    }

    public function test_validation_requires_valid_email_for_recipients(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test',
            'intervalValue' => 90,
            'intervalUnit' => 'days',
            'recipients' => ['invalid-email'],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $response->assertSessionHasErrors('recipients.0');
    }

    public function test_interval_conversion_for_minutes(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test',
            'intervalValue' => 30,
            'intervalUnit' => 'minutes',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $this->assertDatabaseHas('custodianships', [
            'name' => 'Test',
            'interval' => 'PT30M',
        ]);
    }

    public function test_interval_conversion_for_hours(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test',
            'intervalValue' => 2,
            'intervalUnit' => 'hours',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $this->assertDatabaseHas('custodianships', [
            'name' => 'Test',
            'interval' => 'PT2H',
        ]);
    }

    public function test_interval_conversion_for_days(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test',
            'intervalValue' => 7,
            'intervalUnit' => 'days',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->actingAs($user)->post(route('custodianships.store'), $data);

        $this->assertDatabaseHas('custodianships', [
            'name' => 'Test',
            'interval' => 'P7D',
        ]);
    }
}
