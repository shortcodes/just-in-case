<?php

namespace Tests\Feature\Console;

use App\Jobs\SendCustodianshipNotificationJob;
use App\Models\Custodianship;
use App\Models\Delivery;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessPendingRetriesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_dispatches_jobs_for_ready_retries(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        $delivery = Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'status' => 'pending',
                'next_retry_at' => now()->subMinute(),
                'error_message' => 'Previous error',
                'attempt_number' => 1,
            ]);

        $this->artisan('custodianships:process-pending-retries')
            ->assertSuccessful();

        Queue::assertPushed(SendCustodianshipNotificationJob::class, function ($job) use ($custodianship, $recipient) {
            return $job->custodianship->id === $custodianship->id
                && $job->recipient->id === $recipient->id
                && $job->attemptNumber === 2;
        });
    }

    public function test_command_ignores_deliveries_not_ready_yet(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'status' => 'pending',
                'next_retry_at' => now()->addHour(),
                'error_message' => 'Previous error',
            ]);

        $this->artisan('custodianships:process-pending-retries')
            ->assertSuccessful();

        Queue::assertNothingPushed();
    }

    public function test_command_ignores_deliveries_without_error_message(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->completed()->create();
        $recipient = Recipient::factory()->for($custodianship)->create();

        Delivery::factory()
            ->for($custodianship)
            ->for($recipient)
            ->create([
                'status' => 'pending',
                'next_retry_at' => now()->subMinute(),
                'error_message' => null,
            ]);

        $this->artisan('custodianships:process-pending-retries')
            ->assertSuccessful();

        Queue::assertNothingPushed();
    }

    public function test_command_handles_empty_queue(): void
    {
        $this->artisan('custodianships:process-pending-retries')
            ->expectsOutput('No deliveries ready for retry.')
            ->assertSuccessful();
    }
}
