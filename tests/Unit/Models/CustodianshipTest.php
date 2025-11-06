<?php

namespace Tests\Unit\Models;

use App\Models\Custodianship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustodianshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_boot_generates_uuid_on_creation(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $this->assertNotNull($custodianship->uuid);
        $this->assertIsString($custodianship->uuid);
    }

    public function test_boot_sets_default_status_to_draft(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create([
            'status' => null,
        ]);

        $this->assertEquals('draft', $custodianship->status);
    }

    public function test_order_by_default_scope_orders_custodianships(): void
    {
        $user = User::factory()->create();

        $active = Custodianship::factory()->active()->for($user)->create();
        $draft = Custodianship::factory()->for($user)->create(['status' => 'draft']);
        $completed = Custodianship::factory()->completed()->for($user)->create();

        $ordered = $user->custodianships()->orderByDefault()->get();

        $this->assertEquals(3, $ordered->count());
        $this->assertContains($active->id, $ordered->pluck('id'));
        $this->assertContains($draft->id, $ordered->pluck('id'));
        $this->assertContains($completed->id, $ordered->pluck('id'));
    }

    public function test_update_delivery_status_marks_completed_when_all_delivered(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = $custodianship->recipients()->create(['email' => 'test@example.com']);

        $custodianship->deliveries()->create([
            'recipient_id' => $recipient->id,
            'recipient_email' => $recipient->email,
            'status' => 'delivered',
        ]);

        $custodianship->updateDeliveryStatus();

        $this->assertEquals('completed', $custodianship->fresh()->status);
    }

    public function test_get_route_key_name_returns_uuid(): void
    {
        $custodianship = new Custodianship;

        $this->assertEquals('uuid', $custodianship->getRouteKeyName());
    }
}
