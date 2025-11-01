<?php

namespace Tests\Unit\Models;

use App\Models\Custodianship;
use App\Models\Delivery;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustodianshipDeliveryStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_status_is_null_for_draft_custodianship(): void
    {
        $custodianship = Custodianship::factory()->create(['status' => 'draft']);

        $this->assertNull($custodianship->delivery_status);
    }

    public function test_delivery_status_is_null_for_active_custodianship(): void
    {
        $custodianship = Custodianship::factory()->active()->create();

        $this->assertNull($custodianship->delivery_status);
    }

    public function test_delivery_status_is_null_for_completed_with_no_deliveries(): void
    {
        $custodianship = Custodianship::factory()->completed()->create();

        $this->assertNull($custodianship->delivery_status);
    }

    public function test_delivery_status_is_dispatched_when_all_deliveries_pending(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient1 = Recipient::factory()->for($custodianship)->create();
        $recipient2 = Recipient::factory()->for($custodianship)->create();

        Delivery::factory()->for($custodianship)->for($recipient1)->create(['status' => 'pending']);
        Delivery::factory()->for($custodianship)->for($recipient2)->create(['status' => 'pending']);

        $custodianship->refresh();

        $this->assertEquals('dispatched', $custodianship->delivery_status);
    }

    public function test_delivery_status_is_delivered_when_all_deliveries_delivered(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient1 = Recipient::factory()->for($custodianship)->create();
        $recipient2 = Recipient::factory()->for($custodianship)->create();

        Delivery::factory()->for($custodianship)->for($recipient1)->delivered()->create();
        Delivery::factory()->for($custodianship)->for($recipient2)->delivered()->create();

        $custodianship->refresh();

        $this->assertEquals('delivered', $custodianship->delivery_status);
    }

    public function test_delivery_status_is_failed_when_all_deliveries_failed(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient1 = Recipient::factory()->for($custodianship)->create();
        $recipient2 = Recipient::factory()->for($custodianship)->create();

        Delivery::factory()->for($custodianship)->for($recipient1)->failed()->create();
        Delivery::factory()->for($custodianship)->for($recipient2)->failed()->create();

        $custodianship->refresh();

        $this->assertEquals('failed', $custodianship->delivery_status);
    }

    public function test_delivery_status_is_partially_failed_when_some_delivered_some_failed(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient1 = Recipient::factory()->for($custodianship)->create();
        $recipient2 = Recipient::factory()->for($custodianship)->create();

        Delivery::factory()->for($custodianship)->for($recipient1)->delivered()->create();
        Delivery::factory()->for($custodianship)->for($recipient2)->failed()->create();

        $custodianship->refresh();

        $this->assertEquals('partially_failed', $custodianship->delivery_status);
    }

    public function test_delivery_status_is_partially_delivered_when_some_delivered_some_pending(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient1 = Recipient::factory()->for($custodianship)->create();
        $recipient2 = Recipient::factory()->for($custodianship)->create();

        Delivery::factory()->for($custodianship)->for($recipient1)->delivered()->create();
        Delivery::factory()->for($custodianship)->for($recipient2)->create(['status' => 'pending']);

        $custodianship->refresh();

        $this->assertEquals('partially_delivered', $custodianship->delivery_status);
    }

    public function test_delivery_stats_returns_correct_counts(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient1 = Recipient::factory()->for($custodianship)->create();
        $recipient2 = Recipient::factory()->for($custodianship)->create();
        $recipient3 = Recipient::factory()->for($custodianship)->create();

        Delivery::factory()->for($custodianship)->for($recipient1)->delivered()->create();
        Delivery::factory()->for($custodianship)->for($recipient2)->failed()->create();
        Delivery::factory()->for($custodianship)->for($recipient3)->create(['status' => 'pending']);

        $custodianship->refresh();

        $stats = $custodianship->delivery_stats;

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(1, $stats['delivered']);
        $this->assertEquals(1, $stats['failed']);
        $this->assertEquals(1, $stats['pending']);
        $this->assertEquals(33.33, $stats['success_percentage']);
    }

    public function test_delivery_stats_returns_zero_values_when_no_deliveries(): void
    {
        $custodianship = Custodianship::factory()->create();

        $stats = $custodianship->delivery_stats;

        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['delivered']);
        $this->assertEquals(0, $stats['failed']);
        $this->assertEquals(0, $stats['pending']);
        $this->assertEquals(0, $stats['success_percentage']);
    }
}
