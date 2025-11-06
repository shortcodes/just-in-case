<?php

namespace Tests\Feature\Console;

use App\Models\Custodianship;
use App\Models\Delivery;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckStaleDeliveriesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_marks_stale_deliveries_as_failed(): void
    {
        config(['custodianship.delivery.pending_timeout' => 3600]);
        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $staleDelivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'status' => 'pending',
                'next_retry_at' => null,
                'created_at' => now()->subHours(2),
                'last_retry_at' => null,
                'attempt_number' => 3,
                'max_attempts' => 3,
            ]);

        $this->artisan('custodianships:check-stale-deliveries')
            ->assertSuccessful();

        $staleDelivery->refresh();

        $this->assertEquals('failed', $staleDelivery->status);
        $this->assertStringContainsString('stale', $staleDelivery->error_message);
    }

    public function test_command_ignores_recent_deliveries(): void
    {
        config(['custodianship.delivery.pending_timeout' => 3600]);

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $recentDelivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'status' => 'pending',
                'next_retry_at' => null,
                'created_at' => now()->subMinutes(30),
            ]);

        $this->artisan('custodianships:check-stale-deliveries')
            ->expectsOutput('No stale deliveries found.')
            ->assertSuccessful();

        $recentDelivery->refresh();
        $this->assertEquals('pending', $recentDelivery->status);
    }

    public function test_command_handles_no_stale_deliveries(): void
    {
        $this->artisan('custodianships:check-stale-deliveries')
            ->expectsOutput('No stale deliveries found.')
            ->assertSuccessful();
    }
}
